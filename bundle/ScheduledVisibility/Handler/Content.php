<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as ContentValue;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

final class Content implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly ContentService $contentService,
    ) {}

    public function hide(ContentValue $content): void
    {
        $this->repository->sudo(
            fn () => $this->contentService->hideContent($content->getContentInfo()),
        );
    }

    public function reveal(ContentValue $content): void
    {
        $this->repository->sudo(
            fn () => $this->contentService->revealContent($content->getContentInfo()),
        );
    }

    public function isHidden(ContentValue $content): bool
    {
        return $content->contentInfo->isHidden();
    }

    public function isVisible(ContentValue $content): bool
    {
        return !$content->contentInfo->isHidden();
    }
}
