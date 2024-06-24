<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums;

enum HandlerType: string
{
    case Content = 'content';

    case Location = 'location';

    case ContentAndLocation = 'content_and_location';

    case Section = 'section';

    case ObjectState = 'object_state';
}
