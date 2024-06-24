<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Configuration;

use function in_array;

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

    public function isContentTypeAllowed(string $contentType): bool
    {
        if (!$this->allContentTypes && !in_array($contentType, $this->allowedContentTypes, true)) {
            return false;
        }

        return true;
    }
}
