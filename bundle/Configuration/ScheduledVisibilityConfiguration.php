<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Configuration;

final class ScheduledVisibilityConfiguration
{
    public function __construct(
        private readonly string $type,
        private readonly bool $enabled,
        private readonly bool $allContentTypes,
        private readonly array $allowedContentTypes,
    ) {}

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

    public function getType(): string
    {
        return $this->type;
    }
}
