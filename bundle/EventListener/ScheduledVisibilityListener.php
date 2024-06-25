<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\EventListener;

use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Configuration\ScheduledVisibilityConfiguration;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\VisibilityUpdateResult;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Exception\InvalidStateException;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Service\ScheduledVisibilityService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function sprintf;

final class ScheduledVisibilityListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ScheduledVisibilityService $scheduledVisibilityService,
        private readonly ScheduledVisibilityConfiguration $configurationService,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PublishVersionEvent::class => ['onPublishVersion'],
        ];
    }

    public function onPublishVersion(PublishVersionEvent $event): void
    {
        $content = $event->getContent();

        if (!$this->configurationService->isEnabled()) {
            return;
        }

        if (!$this->configurationService->isContentTypeAllowed($content->getContentType()->identifier)) {
            return;
        }

        try {
            $action = $this->scheduledVisibilityService->updateVisibilityIfNeeded($content);
        } catch (InvalidStateException $exception) {
            $this->logger->error($exception->getMessage());

            return;
        }
        if ($action !== VisibilityUpdateResult::NoChange) {
            $this->logger->info(
                sprintf(
                    "Content '%s' with id #%d has been %s.",
                    $content->getContentInfo()->getName(),
                    $content->getId(),
                    $action->value,
                ),
            );
        }
    }
}
