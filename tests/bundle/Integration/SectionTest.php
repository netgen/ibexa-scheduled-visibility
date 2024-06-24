<?php

declare(strict_types=1);

namespace Netgen\IbexaScheduledVisibility\Tests\Integration;

use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Section;

final class SectionTest extends BaseTest
{
    /**
     * @dataProvider provideCases
     */
    public function testToggleVisibility(array $configuration, bool $expectedHidden)
    {
        $scheduledVisibilityService = $this->getScheduledVisibilityService();
        $content = $this->createContent($configuration['publish_from'], $configuration['publish_to']);
        $hiddenSection = $this->createSection();
        $visibleSectionId = 1;
        $handler = $this->getSectionHandler($hiddenSection->id, $visibleSectionId);
        if ($scheduledVisibilityService->shouldHide($content)) {
            $handler->hide($content);
        } elseif ($scheduledVisibilityService->shouldReveal($content)) {
            $handler->reveal($content);
        }
        $content = $this->getRepository()->getContentService()->loadContent($content->contentInfo->getId());
        self::assertEquals($content->contentInfo->sectionId, $expectedHidden ? $hiddenSection->id : $visibleSectionId);
    }

    private function getSectionHandler(int $hiddenSectionId, int $visibleSectionId): Section
    {
        $repository = $this->getRepository();

        return new Section($repository, $repository->getSectionService(), $hiddenSectionId, $visibleSectionId);
    }

    private function createSection(): \Ibexa\Contracts\Core\Repository\Values\Content\Section
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Scheduled Visibility section';
        $sectionCreate->identifier = 'scheduled_visibility_section_';

        return $sectionService->createSection($sectionCreate);
    }
}
