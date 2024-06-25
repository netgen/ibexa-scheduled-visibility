<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Content as ContentHandler;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Handler\Location as LocationHandler;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

final class ContentAndLocation implements ScheduledVisibilityInterface
{
    public function __construct(
        private readonly ContentHandler $contentHandler,
        private readonly LocationHandler $locationHandler,
    ) {}

    public function hide(Content $content): void
    {
        $this->contentHandler->hide($content);
        $this->locationHandler->hide($content);
    }

    public function reveal(Content $content): void
    {
        $this->contentHandler->reveal($content);
        $this->locationHandler->reveal($content);
    }

    public function isHidden(Content $content): bool
    {
        return $this->contentHandler->isHidden($content) && $this->locationHandler->isHidden($content);
    }

    public function isVisible(Content $content): bool
    {
        return $this->contentHandler->isVisible($content) && $this->locationHandler->isVisible($content);
    }
}
