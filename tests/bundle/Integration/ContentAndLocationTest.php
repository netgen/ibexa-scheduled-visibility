<?php

declare(strict_types=1);

namespace Netgen\IbexaScheduledVisibility\Tests\Integration;

use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\ContentAndLocation;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Location;

final class ContentAndLocationTest extends BaseTest
{
    /**
     * @dataProvider provideCases
     */
    public function testUpdateVisibility(array $configuration, bool $expectedHidden)
    {
        $scheduledVisibilityService = $this->getScheduledVisibilityService();
        $content = $this->createContent($configuration['publish_from'], $configuration['publish_to']);
        $handler = $this->getContentAndLocationHandler();
        if ($scheduledVisibilityService->shouldBeHidden($content) && !$handler->isHidden($content)) {
            $handler->hide($content);
        }
        if ($scheduledVisibilityService->shouldBeVisible($content) && $handler->isHidden($content)) {
            $handler->reveal($content);
        }
        $content = $this->getRepository()->getContentService()->loadContent($content->getContentInfo()->getId());
        self::assertEquals($handler->isHidden($content), $expectedHidden);
    }

    private function getContentAndLocationHandler(): ContentAndLocation
    {
        /** @var \Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Content $contentHandler */
        $contentHandler = $this->getSetupFactory()->getServiceContainer()->get(Content::class);

        /** @var \Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Location $locationHandler */
        $locationHandler = $this->getSetupFactory()->getServiceContainer()->get(Location::class);

        return new ContentAndLocation($contentHandler, $locationHandler);
    }
}
