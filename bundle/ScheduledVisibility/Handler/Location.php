<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\HandlerType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

use function method_exists;

final class Location implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly ContentService $contentService,
        private readonly LocationService $locationService,
    ) {}

    public function hide(Content $content): void
    {
        if (method_exists($this->contentService, 'hideContent')) {
            $this->repository->sudo(
                fn () => $this->contentService->hideContent($content->getContentInfo()),
            );

            return;
        }

        $locations = $this->repository->sudo(
            fn () => $this->locationService->loadLocations($content->getContentInfo()),
        );

        foreach ($locations as $location) {
            $this->repository->sudo(
                fn () => $this->locationService->hideLocation($location),
            );
        }
    }

    public function reveal(Content $content): void
    {
        if (method_exists($this->contentService, 'revealContent')) {
            $this->repository->sudo(
                fn () => $this->contentService->revealContent($content->getContentInfo()),
            );

            return;
        }

        $locations = $this->repository->sudo(
            fn () => $this->locationService->loadLocations($content->getContentInfo()),
        );

        foreach ($locations as $location) {
            $this->repository->sudo(
                fn () => $this->locationService->unhideLocation($location),
            );
        }
    }

    public function isHidden(Content $content): bool
    {
        $contentInfo = $content->getContentInfo();
        if ($contentInfo->isHidden()) {
            return true;
        }
        $locations = $this->locationService->loadLocations($contentInfo);
        foreach ($locations as $location) {
            if (!$location->isHidden()) {
                return false;
            }
        }

        return true;
    }

    public function getType(): HandlerType
    {
        return HandlerType::Location;
    }
}
