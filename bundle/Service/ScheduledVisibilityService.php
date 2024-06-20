<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\Service;

use DateTime;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\Date\Value;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\StrategyType;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility\ScheduledVisibilityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function in_array;

final class ScheduledVisibilityService
{
    public function __construct(
        private readonly iterable $strategies,
        private readonly ContainerInterface $container,
    ) {}

    public function toggleVisibility(Content $content): void
    {
        $strategyType = StrategyType::from($this->container->getParameter('netgen_ibexa_scheduled_visibility.strategy'));

        /** @var ScheduledVisibilityInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->getType() === $strategyType) {
                if ($this->shouldHide($content)) {
                    $strategy->hide($content);

                    return;
                }
                if ($this->shouldReveal($content)) {
                    $strategy->reveal($content);
                }

                return;
            }
        }
    }

    public function accept(Content $content): bool
    {
        $enabled = $this->container->getParameter('netgen_ibexa_scheduled_visibility.enabled');
        if (!$enabled) {
            return false;
        }

        $allowedAll = $this->container->getParameter('netgen_ibexa_scheduled_visibility.content_types.all');
        $allowedContentTypes = $this->container->getParameter('netgen_ibexa_scheduled_visibility.content_types.allowed');
        $contentType = $content->getContentType();
        if (!$allowedAll && !in_array($contentType->identifier, $allowedContentTypes, true)) {
            return false;
        }

        $fieldDefinitions = $contentType->getFieldDefinitions();
        if (!$fieldDefinitions->has('publish_from') || !$fieldDefinitions->has('publish_to')) {
            return false;
        }

        $publishFromField = $content->getField('publish_from');
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
        if ($field->value instanceof Value) {
            return $field->value->date;
        }
        if ($field->value instanceof \Ibexa\Core\FieldType\DateAndTime\Value) {
            return $field->value->value;
        }

        return null;
    }
}
