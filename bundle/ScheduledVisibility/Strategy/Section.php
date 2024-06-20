<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Strategy;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\StrategyType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function sprintf;

final class Section implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly SectionService $sectionService,
        private readonly ContainerInterface $container,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    public function hide(Content $content): void
    {
        if ($this->isHidden($content)) {
            return;
        }

        $hiddenSectionId = $this->container->getParameter('netgen_ibexa_scheduled_visibility.sections.hidden.section_id');

        try {
            $hiddenSection = $this->repository->sudo(
                fn (): \Ibexa\Contracts\Core\Repository\Values\Content\Section => $this->sectionService->loadSection($hiddenSectionId),
            );
            $this->repository->sudo(
                fn () => $this->sectionService->assignSection($content->getContentInfo(), $hiddenSection),
            );
        } catch (NotFoundException $e) {
            $this->logger->error(
                sprintf(
                    'Section with id #%d was not found: %s',
                    $hiddenSectionId,
                    $e->getMessage(),
                ),
            );

            return;
        }
    }

    public function reveal(Content $content): void
    {
        if (!$this->isHidden($content)) {
            return;
        }

        $visibleSectionId = $this->container->getParameter('netgen_ibexa_scheduled_visibility.sections.visible.section_id');

        try {
            $visibleSection = $this->repository->sudo(
                fn (): \Ibexa\Contracts\Core\Repository\Values\Content\Section => $this->sectionService->loadSection($visibleSectionId),
            );
            $this->repository->sudo(
                fn () => $this->sectionService->assignSection($content->getContentInfo(), $visibleSection),
            );
        } catch (NotFoundException $e) {
            $this->logger->error(
                sprintf(
                    'Section with id #%d was not found: %s',
                    $visibleSectionId,
                    $e->getMessage(),
                ),
            );

            return;
        }
    }

    public function getType(): StrategyType
    {
        return StrategyType::Section;
    }

    public function isHidden(Content $content): bool
    {
        $hiddenSectionId = $this->container->getParameter('netgen_ibexa_scheduled_visibility.sections.hidden.section_id');

        return $content->getContentInfo()->getSectionId() === $hiddenSectionId;
    }
}
