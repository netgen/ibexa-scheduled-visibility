<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\ScheduledVisibility;

use OutOfBoundsException;

use function sprintf;

final class Registry
{
    /**
     * @var ScheduledVisibilityInterface[]
     */
    private array $handlerMap = [];

    /**
     * @param ScheduledVisibilityInterface[] $handlerMap
     */
    public function __construct(array $handlerMap = [])
    {
        foreach ($handlerMap as $identifier => $handler) {
            $this->register($identifier, $handler);
        }
    }

    public function register(string $identifier, ScheduledVisibilityInterface $handler): void
    {
        $this->handlerMap[$identifier] = $handler;
    }

    /**
     * @throws OutOfBoundsException
     */
    public function get(?string $identifier): ScheduledVisibilityInterface
    {
        return $this->handlerMap[$identifier] ?? throw new OutOfBoundsException(
            sprintf(
                "No handler is registered for identifier '%s'",
                $identifier,
            ),
        );
    }
}
