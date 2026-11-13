<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use WeakMap;

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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var WeakMap<ConsumerConfigurationInterface, ResetAfterRequestInterface[]>
     */
    private WeakMap $instancesByConfiguration;

    /**
     * @param object[] $services Items not implementing ResetAfterRequestInterface are ignored
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $services = [], ?LoggerInterface $logger = null)
    {
        $this->services = array_filter(
            $services,
            static fn ($service) => $service instanceof ResetAfterRequestInterface
        );
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->instancesByConfiguration = new WeakMap();
    }

    /**
     * Reset the registered services and the handler instances the consumer was configured with
     *
     * Handlers are created per consumer rather than shared, so they hold their own caches. A failing reset is
     * logged and never thrown: it runs after the message was settled and must not fail or re-run it.
     *
     * @param ConsumerConfigurationInterface $configuration
     * @return void
     */
    public function resetState(ConsumerConfigurationInterface $configuration): void
    {
        $this->instancesByConfiguration[$configuration] ??= $this->collectInstances($configuration);
        foreach ($this->instancesByConfiguration[$configuration] as $instance) {
            try {
                $instance->_resetState();
            } catch (Throwable $exception) {
                $this->logger->error(
                    sprintf('Could not reset state of %s after a queue message.', get_class($instance)),
                    ['exception' => $exception]
                );
            }
        }
    }

    /**
     * Collect the registered services and the resettable handler instances, each once
     *
     * @param ConsumerConfigurationInterface $configuration
     * @return ResetAfterRequestInterface[]
     */
    private function collectInstances(ConsumerConfigurationInterface $configuration): array
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
        return array_values($instances);
    }
}
