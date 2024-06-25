<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Exception;

use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

use function sprintf;

final class InvalidStateException extends Exception
{
    public function __construct(Content $content, ?Exception $previous = null)
    {
        $message = sprintf("Content '%s' with id #%d is not valid for scheduled visibility update.", $content->getContentInfo()->getName(), $content->getId());

        parent::__construct($message, 0, $previous);
    }
}
