<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\HandlerType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function sprintf;

final class ObjectState implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly Repository $repository,
        private readonly ObjectStateService $objectStateService,
        private readonly int $objectStateGroupId,
        private readonly int $hiddenObjectStateId,
        private readonly int $visibleObjectStateId,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    public function hide(Content $content): void
    {
        try {
            if ($this->isHidden($content)) {
                return;
            }
        } catch (NotFoundException $e) {
            $this->logger->error(
                sprintf(
                    'Configured object state group does not exist: %s',
                    $e->getMessage(),
                ),
            );
        }

        $hiddenObjectStateId = $this->hiddenObjectStateId;

        $this->toggleObjectState($content, $hiddenObjectStateId);
    }

    public function reveal(Content $content): void
    {
        try {
            if (!$this->isHidden($content)) {
                return;
            }
        } catch (NotFoundException $e) {
            $this->logger->error(
                sprintf(
                    'Configured object state group does not exist: %s',
                    $e->getMessage(),
                ),
            );
        }

        $visibleObjectStateId = $this->visibleObjectStateId;

        $this->toggleObjectState($content, $visibleObjectStateId);
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
            fn (): \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState => $this->objectStateService->getContentState($content->contentInfo, $objectStateGroup),
        );

        $hiddenObjectStateId = $this->hiddenObjectStateId;

        return $hiddenObjectStateId === $objectState->id;
    }

    private function toggleObjectState(Content $content, int $objectStateId): void
    {
        try {
            $objectState = $this->repository->sudo(
                fn (): \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState => $this->objectStateService->loadObjectState($objectStateId),
            );
        } catch (NotFoundException $e) {
            $this->logger->error(
                sprintf(
                    'Configured object state with id #%d does not exist: %s',
                    $objectStateId,
                    $e->getMessage(),
                ),
            );

            return;
        }

        $objectStateGroupId = $this->objectStateGroupId;

        try {
            $objectStateGroup = $this->repository->sudo(
                fn (): ObjectStateGroup => $this->objectStateService->loadObjectStateGroup($objectStateGroupId),
            );
        } catch (NotFoundException $e) {
            $this->logger->error(
                sprintf(
                    'Configured object state group does not exist: %s',
                    $e->getMessage(),
                ),
            );

            return;
        }

        $this->repository->sudo(
            fn () => $this->objectStateService->setContentState($content->contentInfo, $objectStateGroup, $objectState),
        );
    }
}
