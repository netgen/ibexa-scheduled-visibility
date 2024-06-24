<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\EventListener;

use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Configuration\ScheduledVisibilityConfiguration;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\VisibilityUpdateResult;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Service\ScheduledVisibilityService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function in_array;
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

        $allowedAll = $this->configurationService->isAllContentTypes();
        $allowedContentTypes = $this->configurationService->getAllowedContentTypes();

        $contentType = $content->getContentType();
        if (!$allowedAll && !in_array($contentType, $allowedContentTypes, true)) {
            return;
        }

        $action = $this->scheduledVisibilityService->updateVisibilityIfNeeded($content);
        if ($action !== VisibilityUpdateResult::NoChange) {
            $this->logger->info(
                sprintf(
                    'Content with id #%d has been %s.',
                    $content->getId(),
                    $action->value,
                ),
            );
        }
    }
}
