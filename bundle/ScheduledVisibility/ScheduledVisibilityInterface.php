<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;

interface ScheduledVisibilityInterface
{
    public function hide(Content $content): void;

    public function reveal(Content $content): void;

    public function isHidden(Content $content): bool;

    public function isVisible(Content $content): bool;
}
