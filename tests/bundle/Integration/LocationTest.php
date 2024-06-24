<?php

declare(strict_types=1);

namespace Netgen\IbexaScheduledVisibility\Tests\Integration;

use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Location;

final class LocationTest extends BaseTest
{
    /**
     * @dataProvider provideCases
     */
    public function testToggleVisibility(array $configuration, bool $expectedHidden)
    {
        $scheduledVisibilityService = $this->getScheduledVisibilityService();
        $content = $this->createContent($configuration['publish_from'], $configuration['publish_to']);
        $handler = $this->getLocationHandler();
        if ($scheduledVisibilityService->shouldBeHidden($content) && !$handler->isHidden($content)) {
            $handler->hide($content);
        }
        if ($scheduledVisibilityService->shouldBeVisible($content) && $handler->isHidden($content)) {
            $handler->reveal($content);
        }
        $content = $this->getRepository()->getContentService()->loadContent($content->contentInfo->getId());
        self::assertEquals($handler->isHidden($content), $expectedHidden);
    }

    private function getLocationHandler(): Location
    {
        $repository = $this->getRepository();

        return new Location($repository, $repository->getContentService(), $repository->getLocationService());
    }
}
