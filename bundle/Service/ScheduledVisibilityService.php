<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Service;

use DateTime;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\Date\Value as DateValue;
use Ibexa\Core\FieldType\DateAndTime\Value as DateAndTimeValue;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\VisibilityUpdateResult;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Exception\InvalidStateException;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\Registry;
use OutOfBoundsException;

final class ScheduledVisibilityService
{
    public function __construct(
        private readonly Registry $registry,
    ) {}

    /**
     * @throws OutOfBoundsException
     * @throws InvalidStateException
     */
    public function updateVisibilityIfNeeded(Content $content, ?string $handlerIdentifier = null): VisibilityUpdateResult
    {
        if (!$this->isValid($content)) {
            throw new InvalidStateException($content);
        }

        $handler = $this->registry->get($handlerIdentifier);

        if ($this->shouldBeHidden($content) && !$handler->isHidden($content)) {
            $handler->hide($content);

            return VisibilityUpdateResult::Hidden;
        }

        if ($this->shouldBeVisible($content) && $handler->isHidden($content)) {
            $handler->reveal($content);

            return VisibilityUpdateResult::Revealed;
        }

        return VisibilityUpdateResult::NoChange;
    }

    public function shouldBeHidden(Content $content): bool
    {
        $publishFromField = $content->getField('publish_from');
        $publishToField = $content->getField('publish_to');

        $publishFromDateTime = $this->getDateTime($publishFromField);
        $publishToDateTime = $this->getDateTime($publishToField);

        if ($publishToDateTime === null && $publishFromDateTime === null) {
            return false;
        }

        $currentDateTime = new DateTime();

        if ($publishFromDateTime !== null && $publishFromDateTime > $currentDateTime) {
            return true;
        }

        if ($publishToDateTime !== null && $publishToDateTime <= $currentDateTime) {
            return true;
        }

        return false;
    }

    public function shouldBeVisible(Content $content): bool
    {
        $publishFromField = $content->getField('publish_from');
        $publishToField = $content->getField('publish_to');

        $publishFromDateTime = $this->getDateTime($publishFromField);
        $publishToDateTime = $this->getDateTime($publishToField);

        if ($publishToDateTime === null && $publishFromDateTime === null) {
            return false;
        }

        $currentDateTime = new DateTime();

        if ($publishFromDateTime === null && $publishToDateTime > $currentDateTime) {
            return true;
        }

        if ($publishToDateTime === null && $publishFromDateTime <= $currentDateTime) {
            return true;
        }

        if ($publishFromDateTime !== null && $publishFromDateTime <= $currentDateTime
            && $publishToDateTime !== null && $publishToDateTime > $currentDateTime) {
            return true;
        }

        return false;
    }

    private function isValid(Content $content): bool
    {
        $publishFromField = $content->getField('publish_from');
        $publishToField = $content->getField('publish_to');

        if ($publishFromField === null || $publishToField === null) {
            return false;
        }

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
