<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\EventListener;

use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Service\ScheduledVisibilityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ScheduledVisibilityListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ScheduledVisibilityService $scheduledVisibilityService
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
        $this->scheduledVisibilityService->toggleVisibility($content);
    }
}
