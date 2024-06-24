<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Command;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\VisibilityUpdateResult;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Service\ScheduledVisibilityService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function is_numeric;
use function sprintf;

final class UpdateContentVisibilityCommand extends Command
{
    private SymfonyStyle $style;

    public function __construct(
        private readonly Repository $repository,
        private readonly ScheduledVisibilityService $scheduledVisibilityService,
        private readonly bool $enabled,
        private readonly bool $allContentTypes,
        private readonly array $allowedContentTypes,
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

        if (!$this->enabled) {
            $this->style->warning('Scheduled visibility mechanism is disabled.');

            return Command::FAILURE;
        }

        $query = new Query();
        $limit = $input->getOption('limit');
        $query->limit = is_numeric($limit) ? (int) $limit : 50;
        $criteria = new Criterion\LogicalOr(
            [
                new Criterion\IsFieldEmpty('publish_from', false),
                new Criterion\IsFieldEmpty('publish_to', false),
            ],
        );

        if (!$this->allContentTypes && count($this->allowedContentTypes) === 0) {
            $this->style->warning('No content types configured for scheduled visibility mechanism.');

            return Command::FAILURE;
        }

        if (!$this->allContentTypes && count($this->allowedContentTypes) > 0) {
            $criteria = new Criterion\LogicalAnd(
                [
                    $criteria,
                    new Criterion\ContentTypeIdentifier($this->allowedContentTypes),
                ],
            );
        }
        $query->filter = $criteria;

        $searchService = $this->repository->getSearchService();
        $searchResult = $searchService->findContent($query, [], false);
        $searchHitCount = count($searchResult->searchHits);

        while ($searchHitCount > 0) {
            $this->style->createProgressBar();
            $this->style->progressStart();
            foreach ($searchResult->searchHits as $hit) {
                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
                $content = $hit->valueObject;
                $action = $this->scheduledVisibilityService->updateVisibilityIfNeeded($content);
                if ($action !== VisibilityUpdateResult::NoChange) {
                    $this->logger->info(
                        sprintf(
                            'Content with id #%d has been %s.',
                            $content->getId(),
                            $action->value,
                        ),
                    );
                }
                $this->style->progressAdvance();
            }
            $this->style->progressFinish();

            $query->offset += $query->limit;
            $searchResult = $searchService->findContent($query, [], false);
            $searchHitCount = count($searchResult->searchHits);
        }

        $this->style->info('Done.');

        return Command::SUCCESS;
    }
}
