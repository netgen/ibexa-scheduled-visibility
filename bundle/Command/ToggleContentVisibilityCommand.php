<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Command;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Service\ScheduledVisibilityService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function count;

final class ToggleContentVisibilityCommand extends Command
{
    private SymfonyStyle $style;

    public function __construct(
        private readonly Repository $repository,
        private readonly ScheduledVisibilityService $scheduledVisibilityService,
        private readonly ContainerInterface $container,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Toggles content visibility based on publish_from and publish_to attributes and configuration.',
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->style->info(
            'This command fetches content and toggles visibility based on its schedule from publish_from and publish_to fields.',
        );

        $enabled = $this->container->getParameter('netgen_ibexa_scheduled_visibility.enabled');
        if (!$enabled) {
            $this->style->warning('Scheduled visibility mechanism is disabled.');

            return Command::FAILURE;
        }

        $query = new Query();
        $query->limit = 50;
        $criteria = new Criterion\LogicalOr(
            [
                new Criterion\IsFieldEmpty('publish_from', false),
                new Criterion\IsFieldEmpty('publish_to', false),
            ],
        );

        $allowedAll = $this->container->getParameter('netgen_ibexa_scheduled_visibility.content_types.all');
        if (!$allowedAll) {
            $allowedContentTypes = $this->container->getParameter('netgen_ibexa_scheduled_visibility.content_types.allowed');
            if (count($allowedContentTypes) === 0) {
                $this->style->warning('No content types configured for scheduled visibility mechanism.');

                return Command::FAILURE;
            }
            $criteria = new Criterion\LogicalAnd(
                [
                    $criteria,
                    new Criterion\ContentTypeIdentifier($allowedContentTypes),
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
                if ($this->scheduledVisibilityService->accept($content)) {
                    $this->scheduledVisibilityService->toggleVisibility($content);
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
