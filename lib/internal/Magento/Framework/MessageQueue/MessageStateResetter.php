<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use InvalidArgumentException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Resets the state of configured services after a consumer has processed a message.
 *
 * A consumer is a long-running process, so services that cache entities in memory would otherwise serve
 * data loaded while handling an earlier message. Only register services whose reset clears such caches;
 * services holding connections (database, AMQP) must never be registered here.
 */
class MessageStateResetter
{
    /**
     * @var ResetAfterRequestInterface[]
     */
    private array $services;

    /**
     * @param ResetAfterRequestInterface[] $services
     * @throws InvalidArgumentException
     */
    public function __construct(array $services = [])
    {
        foreach ($services as $name => $service) {
            if (!$service instanceof ResetAfterRequestInterface) {
                throw new InvalidArgumentException(
                    sprintf('Service "%s" must implement %s.', $name, ResetAfterRequestInterface::class)
                );
            }
        }
        $this->services = $services;
    }

    /**
     * Reset the state of every registered service
     *
     * @return void
     */
    public function resetState(): void
    {
        foreach ($this->services as $service) {
            $service->_resetState();
        }
    }
}
