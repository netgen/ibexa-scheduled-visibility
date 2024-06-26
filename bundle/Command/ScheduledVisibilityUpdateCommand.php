<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Core\Configuration;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Core\Exception\InvalidStateException;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Core\ScheduledVisibilityService;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function sprintf;

final class ScheduledVisibilityUpdateCommand extends Command
{
    private SymfonyStyle $style;
    private array $languageCache = [];

    public function __construct(
        private readonly Repository $repository,
        private readonly ScheduledVisibilityService $scheduledVisibilityService,
        private readonly Configuration $configurationService,
        private readonly Connection $connection,
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
            1024,
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

        $question = new ConfirmationQuestion(
            'Continue with this action?',
            true,
            '/^(y)/i',
        );

        if (!$this->style->askQuestion($question)) {
            $this->style->success('Aborted');

            return Command::SUCCESS;
        }

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

        $pager = $this->getPager($allContentTypes, $allowedContentTypes);

        if ($pager->getNbResults() === 0) {
            $this->style->info('No content found');

            return Command::FAILURE;
        }

        $limit = $input->getOption('limit');
        $offset = 0;

        $this->style->progressStart($pager->getNbResults());

        $results = $pager->getAdapter()->getSlice($offset, $limit);
        while (count($results) > 0) {
            $this->processResults($results);
            $offset += $limit;
            $results = $pager->getAdapter()->getSlice($offset, $limit);
        }

        $this->style->progressFinish();

        $this->style->info('Done.');

        return Command::SUCCESS;
    }

    private function processResults(array $results): void
    {
        foreach ($results as $result) {
            try {
                $languageId = $result['initial_language_id'];
                $language = $this->loadLanguage($languageId);
            } catch (NotFoundException $exception) {
                $this->logger->error(
                    sprintf(
                        'Language with id #%d does not exist: %s',
                        $languageId,
                        $exception->getMessage(),
                    ),
                );

                $this->style->progressAdvance();

                continue;
            }

            try {
                $contentId = $result['id'];

                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
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

                $this->style->progressAdvance();

                continue;
            }

            try {
                $action = $this->scheduledVisibilityService->updateVisibilityIfNeeded($content);
            } catch (InvalidStateException $exception) {
                $this->logger->error($exception->getMessage());
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

    private function getPager(bool $allContentTypes, array $allowedContentTypes): Pagerfanta
    {
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

        if (count($contentTypeIds) > 0) {
            $this->applyContentTypeLimit($query, $contentTypeIds);
        }

        $countQueryBuilderModifier = function (QueryBuilder $queryBuilder) use ($contentTypeIds): void {
            $queryBuilder->select('COUNT(id) AS total_results')
                ->from('ezcontentobject')
                ->where('published != :unpublished')
                ->setParameter('unpublished', 0)
                ->setMaxResults(1);

            if (count($contentTypeIds) > 0) {
                $this->applyContentTypeLimit($queryBuilder, $contentTypeIds);
            }
        };

        return new Pagerfanta(new QueryAdapter($query, $countQueryBuilderModifier));
    }

    private function loadLanguage(int $id): Language
    {
        if (!isset($this->languageCache[$id])) {
            $language = $this->repository->getContentLanguageService()->loadLanguageById($id);
            $this->languageCache[$id] = $language;
        }

        return $this->languageCache[$id];
    }
}
