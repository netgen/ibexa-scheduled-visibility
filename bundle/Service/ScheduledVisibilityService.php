<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Service;

use DateTime;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\Date\Value as DateValue;
use Ibexa\Core\FieldType\DateAndTime\Value as DateAndTimeValue;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\HandlerType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;

use function in_array;

final class ScheduledVisibilityService
{
    public function __construct(
        private readonly iterable $handlers,
        private readonly string $type,
        private readonly bool $enabled,
        private readonly bool $allContentTypes,
        private readonly array $allowedContentTypes,
    ) {}

    public function toggleVisibility(Content $content): void
    {
        if (!$this->accept($content)) {
            return;
        }

        $handlerType = HandlerType::from($this->type);

        /** @var ScheduledVisibilityInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->getType() === $handlerType) {
                if ($this->shouldHide($content)) {
                    $handler->hide($content);

                    return;
                }
                if ($this->shouldReveal($content)) {
                    $handler->reveal($content);
                }

                return;
            }
        }
    }

    public function accept(Content $content): bool
    {
        $enabled = $this->enabled;
        if (!$enabled) {
            return false;
        }

        $allowedAll = $this->allContentTypes;
        $allowedContentTypes = $this->allowedContentTypes;
        $contentType = $content->getContentType();
        if (!$allowedAll && !in_array($contentType->identifier, $allowedContentTypes, true)) {
            return false;
        }

        $fieldDefinitions = $contentType->getFieldDefinitions();
        if (!$fieldDefinitions->has('publish_from') || !$fieldDefinitions->has('publish_to')) {
            return false;
        }

        /** @var Field $publishFromField */
        $publishFromField = $content->getField('publish_from');

        /** @var Field $publishToField */
        $publishToField = $content->getField('publish_to');
        if (
            ($publishFromField->fieldTypeIdentifier !== 'ezdatetime' && $publishFromField->fieldTypeIdentifier !== 'ezdate')
            || ($publishToField->fieldTypeIdentifier !== 'ezdatetime' && $publishToField->fieldTypeIdentifier !== 'ezdate')
        ) {
            return false;
        }

        $publishFromDateTime = $this->getDateTime($publishFromField);
        $publishToDateTime = $this->getDateTime($publishToField);
        if ($publishFromDateTime !== null && $publishToDateTime !== null && $publishToDateTime <= $publishFromDateTime) {
            return false;
        }

        return true;
    }

    public function shouldHide(Content $content): bool
    {
        $publishFromField = $content->getField('publish_from');
        $publishToField = $content->getField('publish_to');

        $publishFromDateTime = $this->getDateTime($publishFromField);
        $publishToDateTime = $this->getDateTime($publishToField);

        $currentDateTime = new DateTime();
        if (($publishFromDateTime !== null && $publishFromDateTime > $currentDateTime)
            || ($publishToDateTime !== null && $publishToDateTime <= $currentDateTime)) {
            return true;
        }

        return false;
    }

    public function shouldReveal(Content $content): bool
    {
        $publishFromField = $content->getField('publish_from');
        $publishToField = $content->getField('publish_to');

        $publishFromDateTime = $this->getDateTime($publishFromField);
        $publishToDateTime = $this->getDateTime($publishToField);

        if ($publishToDateTime === null && $publishFromDateTime === null) {
            return true;
        }

        $currentDateTime = new DateTime();
        if (($publishFromDateTime !== null && $publishFromDateTime <= $currentDateTime
            && ($publishToDateTime === null || $publishToDateTime > $currentDateTime))
            || ($publishFromDateTime === null && $publishToDateTime > $currentDateTime)
        ) {
            return true;
        }

        return false;
    }

    private function getDateTime(Field $field): null|DateTime
    {
        if ($field->value instanceof DateValue) {
            return $field->value->date;
        }
        if ($field->value instanceof DateAndTimeValue) {
            return $field->value->value;
        }

        return null;
    }
}
