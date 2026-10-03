<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Resets the state of configured services and consumer handlers after a consumer has processed a message.
 *
 * A consumer is a long-running process, so services that cache entities in memory would otherwise serve
 * data loaded while handling an earlier message. Only register services whose reset clears such caches;
 * services holding connections (database, AMQP) must never be registered here. Register the shared service
 * itself, not its generated proxy: a proxy only resets a subject that was resolved through that same proxy.
 */
class MessageStateResetter
{
    /**
     * @var ResetAfterRequestInterface[]
     */
    private array $services;

    /**
     * @param object[] $services Items not implementing ResetAfterRequestInterface are ignored
     */
    public function __construct(array $services = [])
    {
        $this->services = array_filter(
            $services,
            static fn ($service) => $service instanceof ResetAfterRequestInterface
        );
    }

    /**
     * Reset the registered services and the handler instances the consumer was configured with
     *
     * Handlers are created per consumer rather than shared, so they hold their own caches.
     *
     * @param ConsumerConfigurationInterface $configuration
     * @return void
     */
    public function resetState(ConsumerConfigurationInterface $configuration): void
    {
        $instances = [];
        foreach ($this->services as $service) {
            $instances[spl_object_id($service)] = $service;
        }
        foreach ($configuration->getTopicNames() as $topicName) {
            foreach ($configuration->getHandlers($topicName) ?? [] as $handler) {
                $instance = is_array($handler) ? ($handler[0] ?? null) : null;
                if ($instance instanceof ResetAfterRequestInterface) {
                    $instances[spl_object_id($instance)] = $instance;
                }
            }
        }
        foreach ($instances as $instance) {
            $instance->_resetState();
        }
    }
}
