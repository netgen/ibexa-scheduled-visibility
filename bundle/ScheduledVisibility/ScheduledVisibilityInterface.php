<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility;

use Ibexa\Core\Repository\Values\Content\Content;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\StrategyType;

interface ScheduledVisibilityInterface
{
    public function hide(Content $content): void;

    public function reveal(Content $content): void;

    public function getType(): StrategyType;
}
