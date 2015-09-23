<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Config\Data as QueueConfig;
use Magento\Framework\Amqp\Config\Converter as QueueConfigConverter;
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
     * @var array
     */
    private $queueConfigData;

    /**
     * Initialize dependencies.
     *
     * <type name="Magento\Framework\Amqp\ConsumerFactory">
     *     <arguments>
     *         <argument name="consumers" xsi:type="array">
     *             <item name="rabbitmq" xsi:type="array">
     *                 <item name="type" xsi:type="string">Magento\Framework\Amqp\Consumer</item>
     *                 <item name="connectionName" xsi:type="string">rabbitmq</item>
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
        $consumerConfig = $this->getConsumerConfigForName($consumerName);
        $consumer = $this->createConsumer(
            $consumerConfig[QueueConfigConverter::CONSUMER_CONNECTION],
            isset($consumerConfig['executor']) ? $consumerConfig['executor'] : null
        );

        $consumerConfigObject = $this->createConsumerConfiguration($consumerConfig);
        $consumer->configure($consumerConfigObject);
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
     * @param string|null $executorClass
     * @return ConsumerInterface
     * @throws LocalizedException
     */
    private function createConsumer($connectionName, $executorClass)
    {
        if ($executorClass !== null) {
            $executorObject = $this->objectManager->create($executorClass, []);
        } elseif (isset($this->consumers[$connectionName])) {
            $typeName =  $this->consumers[$connectionName];
            $executorObject = $this->objectManager->create($typeName, []);
        } else {
            throw new LocalizedException(
                new Phrase('Could not find an implementation type for connection "%name".', ['name' => $connectionName])
            );
        }
        return $executorObject;
    }

    /**
     * Returns the consumer configuration information.
     *
     * @param string $consumerName
     * @return array
     * @throws LocalizedException
     */
    private function getConsumerConfigForName($consumerName)
    {
        $queueConfig = $this->getQueueConfigData();
        if (isset($queueConfig[QueueConfigConverter::CONSUMERS][$consumerName])) {
            return $queueConfig[QueueConfigConverter::CONSUMERS][$consumerName];
        }
        throw new LocalizedException(
            new Phrase('Specified consumer "%consumer" is not declared.', ['consumer' => $consumerName])
        );
    }

    /**
     * Creates the objects necessary for the ConsumerConfigurationInterface to configure a Consumer.
     *
     * @param array $consumerConfig
     * @return ConsumerConfigurationInterface
     */
    private function createConsumerConfiguration($consumerConfig)
    {
        $dispatchInstance = $this->objectManager->create(
            $consumerConfig[QueueConfigConverter::CONSUMER_CLASS],
            []
        );
        $configData = [
            ConsumerConfiguration::CONSUMER_NAME => $consumerConfig[QueueConfigConverter::CONSUMER_NAME],
            ConsumerConfiguration::QUEUE_NAME => $consumerConfig[QueueConfigConverter::CONSUMER_QUEUE],
            ConsumerConfiguration::CALLBACK => [
                $dispatchInstance,
                $consumerConfig[QueueConfigConverter::CONSUMER_METHOD],
            ],
        ];

        return $this->objectManager->create('Magento\Framework\Amqp\ConsumerConfiguration', [ 'data' => $configData ]);
    }

    /**
     * Returns the queue configuration.
     *
     * @return array
     */
    private function getQueueConfigData()
    {
        if ($this->queueConfigData == null) {
            $this->queueConfigData = $this->queueConfig->get();
        }
        return $this->queueConfigData;
    }
}
