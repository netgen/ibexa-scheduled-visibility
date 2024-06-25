<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Configuration\ScheduledVisibilityConfiguration;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\VisibilityUpdateResult;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Exception\InvalidStateException;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Service\ScheduledVisibilityService;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function sprintf;

final class UpdateContentVisibilityCommand extends Command
{
    private SymfonyStyle $style;

    public function __construct(
        private readonly Repository $repository,
        private readonly ScheduledVisibilityService $scheduledVisibilityService,
        private readonly ScheduledVisibilityConfiguration $configurationService,
        private readonly Connection $connection,
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Updates content visibility based on publish_from and publish_to attributes and configuration.',
        );
        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Number of content object to process in a single iteration',
            50,
        );
        $this->addOption(
            'ttl',
            't',
            InputOption::VALUE_OPTIONAL,
            'Expiration time for language caching',
            3600,
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->style->info(
            'This command fetches content and updates visibility based on its schedule from publish_from and publish_to fields.',
        );

        if (!$this->configurationService->isEnabled()) {
            $this->style->warning('Scheduled visibility mechanism is disabled.');

            return Command::FAILURE;
        }

        $allContentTypes = $this->configurationService->isAllContentTypes();
        $allowedContentTypes = $this->configurationService->getAllowedContentTypes();

        if (!$allContentTypes && count($allowedContentTypes) === 0) {
            $this->style->warning('No content types configured for scheduled visibility mechanism.');

            return Command::FAILURE;
        }

        $query = $this->getQueryBuilder();

        $contentTypeIds = [];
        if (!$allContentTypes && count($allowedContentTypes) > 0) {
            foreach ($allowedContentTypes as $allowedContentType) {
                try {
                    $contentTypeIds[] = $this->repository->getContentTypeService()->loadContentTypeByIdentifier($allowedContentType)->id;
                } catch (NotFoundException $exception) {
                    $this->logger->error(
                        sprintf(
                            "Content type with identifier '%s' does not exist: %s",
                            $allowedContentType,
                            $exception->getMessage(),
                        ),
                    );

                    continue;
                }
            }
        }

        $this->applyContentTypeLimit($query, $contentTypeIds);

        $pager = $this->getPager($query, $contentTypeIds);

        if ($pager->getNbResults() === 0) {
            $output->writeln('No content found.');

            return Command::FAILURE;
        }

        $limit = $input->getOption('limit');
        $offset = 0;

        $this->style->createProgressBar($pager->getNbResults());
        $this->style->progressStart();

        $results = $pager->getAdapter()->getSlice($offset, $limit);
        while (count($results) > 0) {
            $this->processResults($results, $input);
            $offset += $limit;
            $results = $pager->getAdapter()->getSlice($offset, $limit);
        }

        $this->style->progressFinish();

        $this->style->info('Done.');

        return Command::SUCCESS;
    }

    private function processResults(array $results, InputInterface $input): void
    {
        foreach ($results as $result) {
            try {
                $languageId = $result['initial_language_id'];
                $language = $this->loadLanguage($languageId, $input);
            } catch (NotFoundException $exception) {
                $this->logger->error(
                    sprintf(
                        'Language with id #%d does not exist: %s',
                        $languageId,
                        $exception->getMessage(),
                    ),
                );

                continue;
            }

            try {
                $contentId = $result['id'];

                /** @var Content $content */
                $content = $this->repository->sudo(
                    fn () => $this->repository->getContentService()->loadContent(
                        $contentId,
                        [$language->getLanguageCode()],
                    ),
                );
            } catch (NotFoundException $exception) {
                $this->logger->error(
                    sprintf(
                        'Content with id #%d does not exist: %s',
                        $contentId,
                        $exception->getMessage(),
                    ),
                );

                continue;
            }

            try {
                $action = $this->scheduledVisibilityService->updateVisibilityIfNeeded($content);
            } catch (InvalidStateException $exception) {
                $this->logger->error($exception->getMessage());

                continue;
            }

            if ($action !== VisibilityUpdateResult::NoChange) {
                $this->logger->info(
                    sprintf(
                        "Content '%s' with id #%d has been %s.",
                        $content->getName(),
                        $content->getId(),
                        $action->value,
                    ),
                );
            }

            $this->style->progressAdvance();
        }
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id', 'initial_language_id')
            ->from('ezcontentobject')
            ->where('published != :unpublished')
            ->orderBy('id', 'ASC')
            ->setParameter('unpublished', 0);

        return $query;
    }

    private function applyContentTypeLimit(QueryBuilder $query, array $contentTypeIds): void
    {
        $query->where(
            $query->expr()->in('contentclass_id', ':content_type_ids'),
        )->setParameter('content_type_ids', $contentTypeIds, Connection::PARAM_INT_ARRAY);
    }

    private function getPager(QueryBuilder $query, array $contentTypeIds): Pagerfanta
    {
        $countQueryBuilderModifier = static function (QueryBuilder $queryBuilder) use ($contentTypeIds): void {
            $queryBuilder->select('COUNT(id) AS total_results')
                ->from('ezcontentobject')
                ->where('published != :unpublished')
                ->setParameter('unpublished', 0)
                ->setMaxResults(1);

            if (count($contentTypeIds) > 0) {
                $queryBuilder->where(
                    $queryBuilder->expr()->in('contentclass_id', ':content_type_ids'),
                )->setParameter('content_type_ids', $contentTypeIds, Connection::PARAM_INT_ARRAY);
            }
        };

        return new Pagerfanta(new QueryAdapter($query, $countQueryBuilderModifier));
    }

    /**
     * @throws NotFoundException
     */
    private function loadLanguage(int $id, InputInterface $input): Language
    {
        $cacheItem = $this->cache->getItem("netgen-ibexa-scheduled-visibility-language-{$id}");

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $language = $this->repository->getContentLanguageService()->loadLanguageById($id);

        $cacheItem->set($language);
        $cacheItem->expiresAfter($input->getOption('ttl'));
        $this->cache->save($cacheItem);

        return $language;
    }
}
