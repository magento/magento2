<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * Class which creates Consumers
 */
class ConsumerFactory
{
    /**
     * All of the merged queue config information
     *
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var ConsumerInterface[]
     */
    private $consumers;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Initialize dependencies.
     *
     * <type name="Magento\Framework\MessageQueue\ConsumerFactory">
     *     <arguments>
     *         <argument name="consumers" xsi:type="array">
     *             <item name="amqp" xsi:type="array">
     *                 <item name="type" xsi:type="string">Magento\Framework\MessageQueue\Consumer</item>
     *                 <item name="connectionName" xsi:type="string">amqp</item>
     *             </item>
     *         </argument>
     *     </arguments>
     * </type>
     *
     * @param QueueConfig $queueConfig
     * @param ObjectManagerInterface $objectManager
     * @param array $consumers Consumer configuration data
     */
    public function __construct(
        QueueConfig $queueConfig,
        ObjectManagerInterface $objectManager,
        $consumers = []
    ) {
        $this->queueConfig = $queueConfig;
        $this->objectManager = $objectManager;
        $this->consumers = [];

        foreach ($consumers as $consumerConfig) {
            $this->add($consumerConfig['connectionName'], $consumerConfig['type']);
        }
    }

    /**
     * Return the actual Consumer implementation for the given consumer name.
     *
     * @param string $consumerName
     * @return ConsumerInterface
     * @throws LocalizedException
     */
    public function get($consumerName)
    {
        $consumerConfig = $this->queueConfig->getConsumer($consumerName);
        if ($consumerConfig === null) {
            throw new LocalizedException(
                new Phrase('Specified consumer "%consumer" is not declared.', ['consumer' => $consumerName])
            );
        }
        $consumerConfigObject = $this->createConsumerConfiguration($consumerConfig);
        $consumer = $this->createConsumer(
            $consumerConfig[QueueConfig::CONSUMER_CONNECTION],
            isset($consumerConfig[QueueConfig::BROKER_CONSUMER_INSTANCE_TYPE])
            ? $consumerConfig[QueueConfig::BROKER_CONSUMER_INSTANCE_TYPE]
            : null,
            $consumerConfigObject
        );

        return $consumer;
    }

    /**
     * Add consumer.
     *
     * @param string $name
     * @param string $typeName
     * @return $this
     */
    private function add($name, $typeName)
    {
        $this->consumers[$name] = $typeName;
        return $this;
    }

    /**
     * Return an instance of a consumer for a connection name.
     *
     * @param string $connectionName
     * @param string|null $instanceType
     * @param ConsumerConfigurationInterface $configuration
     * @return ConsumerInterface
     * @throws LocalizedException
     */
    private function createConsumer($connectionName, $instanceType, $configuration)
    {
        if ($instanceType !== null) {
            $executorObject = $this->objectManager->create($instanceType, ['configuration' => $configuration]);
        } elseif (isset($this->consumers[$connectionName][$configuration->getType()])) {
            $typeName = $this->consumers[$connectionName][$configuration->getType()];
            $executorObject = $this->objectManager->create($typeName, ['configuration' => $configuration]);
        } else {
            throw new LocalizedException(
                new Phrase('Could not find an implementation type for connection "%name".', ['name' => $connectionName])
            );
        }
        return $executorObject;
    }

    /**
     * Creates the objects necessary for the ConsumerConfigurationInterface to configure a Consumer.
     *
     * @param array $consumerConfig
     * @return ConsumerConfigurationInterface
     */
    private function createConsumerConfiguration($consumerConfig)
    {
        $handlers = [];
        foreach ($consumerConfig[QueueConfig::CONSUMER_HANDLERS] as $topic => $topicHandlers) {
            foreach ($topicHandlers as $handlerConfig) {
                $handlers[$topic][] = [
                    $this->objectManager->create($handlerConfig[QueueConfig::CONSUMER_CLASS]),
                    $handlerConfig[QueueConfig::CONSUMER_METHOD]
                ];
            }
        }

        $configData = [
            ConsumerConfiguration::CONSUMER_NAME => $consumerConfig[QueueConfig::CONSUMER_NAME],
            ConsumerConfiguration::QUEUE_NAME => $consumerConfig[QueueConfig::CONSUMER_QUEUE],
            ConsumerConfiguration::CONSUMER_TYPE =>
                $consumerConfig[QueueConfig::CONSUMER_TYPE] == QueueConfig::CONSUMER_TYPE_SYNC
                ? ConsumerConfiguration::TYPE_SYNC
                : ConsumerConfiguration::TYPE_ASYNC,
            ConsumerConfiguration::HANDLERS => $handlers,
        ];

        return $this->objectManager->create(
            \Magento\Framework\MessageQueue\ConsumerConfiguration::class,
            ['data' => $configData]
        );
    }
}
