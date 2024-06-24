<?php

declare(strict_types=1);

namespace Netgen\IbexaScheduledVisibility\Tests\Integration;

use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\ObjectState;

final class ObjectStateTest extends BaseTest
{
    /**
     * @dataProvider provideCases
     */
    public function testToggleVisibility(array $configuration, bool $expectedHidden)
    {
        $scheduledVisibilityService = $this->getScheduledVisibilityService();
        $content = $this->createContent($configuration['publish_from'], $configuration['publish_to']);
        $hiddenObjectState = $this->createObjectState();
        $visibleObjectStateId = 1;
        $objectStateGroupId = 2;
        $handler = $this->getObjectStateHandler($objectStateGroupId, $hiddenObjectState->id, $visibleObjectStateId);
        if ($scheduledVisibilityService->shouldHide($content)) {
            $handler->hide($content);
        } elseif ($scheduledVisibilityService->shouldReveal($content)) {
            $handler->reveal($content);
        }
        $content = $this->getRepository()->getContentService()->loadContent($content->contentInfo->getId());

        $objectStateService = $this->getRepository()->getObjectStateService();
        $objectStateGroup = $objectStateService->loadObjectStateGroup(2);
        $objectState = $this->getRepository()->sudo(
            static fn (): \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState => $objectStateService->getContentState($content->contentInfo, $objectStateGroup),
        );
        self::assertEquals($objectState->id, $expectedHidden ? $hiddenObjectState->id : $visibleObjectStateId);
    }

    private function getObjectStateHandler(int $objectStateGroupId, int $hiddenObjectStateId, int $visibleObjectStateId): ObjectState
    {
        $repository = $this->getRepository();

        return new ObjectState($repository, $repository->getObjectStateService(), $objectStateGroupId, $hiddenObjectStateId, $visibleObjectStateId);
    }

    private function createObjectState(): \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState
    {
        $repository = $this->getRepository();

        $objectStateService = $repository->getObjectStateService();
        $objectStateGroup = $objectStateService->loadObjectStateGroup(2);

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            'scheduled_visibility_object_state',
        );
        $objectStateCreateStruct->priority = 23;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-GB';
        $objectStateCreateStruct->names = [
            'eng-GB' => 'Scheduled visibility object state',
        ];

        return $objectStateService->createObjectState(
            $objectStateGroup,
            $objectStateCreateStruct,
        );
    }
}
