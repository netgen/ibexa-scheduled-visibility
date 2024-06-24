<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums;

enum VisibilityAction: string
{
    case Hidden = 'hidden';

    case Revealed = 'revealed';

    case NoChange = 'no_change';
}
