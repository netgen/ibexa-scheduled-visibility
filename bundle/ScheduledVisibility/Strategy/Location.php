<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Strategy;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\StrategyType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function method_exists;
use function sprintf;

final class Location implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly ContentService $contentService,
        private readonly LocationService $locationService,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    public function hide(Content $content): void
    {
        try {
            if ($this->isHidden($content)) {
                return;
            }
        } catch (BadStateException $e) {
            $this->logger->error(
                sprintf(
                    'Content with id #%d is in the wrong state: %s',
                    $content->getId(),
                    $e->getMessage(),
                ),
            );

            return;
        }

        if (method_exists($this->contentService, 'hideContent')) {
            $this->repository->sudo(
                fn () => $this->contentService->hideContent($content->getContentInfo()),
            );

            return;
        }

        try {
            $locations = $this->repository->sudo(
                fn () => $this->locationService->loadLocations($content->getContentInfo()),
            );
        } catch (BadStateException $e) {
            $this->logger->error(
                sprintf(
                    'Content with id #%d is in the wrong state: %s',
                    $content->getId(),
                    $e->getMessage(),
                ),
            );

            return;
        }

        foreach ($locations as $location) {
            $this->repository->sudo(
                fn () => $this->locationService->hideLocation($location),
            );
        }
    }

    public function reveal(Content $content): void
    {
        try {
            if (!$this->isHidden($content)) {
                return;
            }
        } catch (BadStateException $e) {
            $this->logger->error(
                sprintf(
                    'Content with id #%d is in the wrong state: %s',
                    $content->getId(),
                    $e->getMessage(),
                ),
            );

            return;
        }

        if (method_exists($this->contentService, 'revealContent')) {
            $this->repository->sudo(
                fn () => $this->contentService->revealContent($content->getContentInfo()),
            );

            return;
        }

        try {
            $locations = $this->repository->sudo(
                fn () => $this->locationService->loadLocations($content->getContentInfo()),
            );
        } catch (BadStateException $e) {
            $this->logger->error(
                sprintf(
                    'Content with id #%d is in the wrong state: %s',
                    $content->getId(),
                    $e->getMessage(),
                ),
            );

            return;
        }

        foreach ($locations as $location) {
            $this->repository->sudo(
                fn () => $this->locationService->unhideLocation($location),
            );
        }
    }

    /**
     * @throws BadStateException
     */
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

    public function getType(): StrategyType
    {
        return StrategyType::Location;
    }
}
