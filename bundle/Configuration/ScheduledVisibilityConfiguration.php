<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Configuration;

use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Registry;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

final class ScheduledVisibilityConfiguration
{
    public function __construct(
        private readonly Registry $registry,
        private readonly string $type,
        private readonly bool $enabled,
        private readonly bool $allContentTypes,
        private readonly array $allowedContentTypes,
    ) {}

    public function getHandler(): ScheduledVisibilityInterface
    {
        return $this->registry->get($this->type);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAllContentTypes(): bool
    {
        return $this->allContentTypes;
    }

    public function getAllowedContentTypes(): array
    {
        return $this->allowedContentTypes;
    }
}
