<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums;

enum StrategyType: string
{
    case Location = 'location';

    case Section = 'section';

    case ObjectState = 'object_state';
}
