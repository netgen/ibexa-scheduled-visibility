<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState as ObjectStateValue;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\HandlerType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

final class ObjectState implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly ObjectStateService $objectStateService,
        private readonly int $objectStateGroupId,
        private readonly int $hiddenObjectStateId,
        private readonly int $visibleObjectStateId,
    ) {}

    /**
     * @throws NotFoundException
     */
    public function hide(Content $content): void
    {
        $hiddenObjectStateId = $this->hiddenObjectStateId;

        $this->setObjectState($content, $hiddenObjectStateId);
    }

    /**
     * @throws NotFoundException
     */
    public function reveal(Content $content): void
    {
        $visibleObjectStateId = $this->visibleObjectStateId;

        $this->setObjectState($content, $visibleObjectStateId);
    }

    public function getType(): HandlerType
    {
        return HandlerType::ObjectState;
    }

    /**
     * @throws NotFoundException
     */
    public function isHidden(Content $content): bool
    {
        $objectStateGroupId = $this->objectStateGroupId;
        $objectStateGroup = $this->repository->sudo(
            fn (): ObjectStateGroup => $this->objectStateService->loadObjectStateGroup($objectStateGroupId),
        );

        $objectState = $this->repository->sudo(
            fn (): ObjectStateValue => $this->objectStateService->getContentState($content->contentInfo, $objectStateGroup),
        );

        return $this->hiddenObjectStateId === $objectState->id;
    }

    /**
     * @throws NotFoundException
     */
    private function setObjectState(Content $content, int $objectStateId): void
    {
        /** @var ObjectStateValue $objectState */
        $objectState = $this->repository->sudo(
            fn (): ObjectStateValue => $this->objectStateService->loadObjectState($objectStateId),
        );

        $objectStateGroup = $objectState->getObjectStateGroup();

        $this->repository->sudo(
            fn () => $this->objectStateService->setContentState(
                $content->contentInfo,
                $objectStateGroup->id,
                $objectState,
            ),
        );
    }
}
