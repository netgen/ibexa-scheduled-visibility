<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

final class Location implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly LocationService $locationService,
    ) {}

    public function hide(Content $content): void
    {
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
        return !$this->isVisible($content);
    }

    public function isVisible(Content $content): bool
    {
        $locations = $this->locationService->loadLocations($content->contentInfo);

        foreach ($locations as $location) {
            if ($location->isHidden()) {
                return false;
            }
        }

        return true;
    }
}
