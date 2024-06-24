<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\HandlerType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

final class Section implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly SectionService $sectionService,
        private readonly int $hiddenSectionId,
        private readonly int $visibleSectionId,
    ) {}

    public function hide(Content $content): void
    {
        $hiddenSectionId = $this->hiddenSectionId;

        $this->assignSection($content, $hiddenSectionId);
    }

    public function reveal(Content $content): void
    {
        $visibleSectionId = $this->visibleSectionId;

        $this->assignSection($content, $visibleSectionId);
    }

    public function getType(): HandlerType
    {
        return HandlerType::Section;
    }

    public function isHidden(Content $content): bool
    {
        return $content->getContentInfo()->getSectionId() === $this->hiddenSectionId;
    }

    public function isVisible(Content $content): bool
    {
        return $content->getContentInfo()->getSectionId() === $this->visibleSectionId;
    }

    /**
     * @throws NotFoundException
     */
    private function assignSection(Content $content, int $sectionId): void
    {
        $section = $this->repository->sudo(
            fn (): \Ibexa\Contracts\Core\Repository\Values\Content\Section => $this->sectionService->loadSection($sectionId),
        );
        $this->repository->sudo(
            fn () => $this->sectionService->assignSection($content->getContentInfo(), $section),
        );
    }
}
