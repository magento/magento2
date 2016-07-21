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
use Magento\Framework\MessageQueue\ConsumerInterface;

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
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;


    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        QueueConfig $queueConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->queueConfig = $queueConfig;
        $this->objectManager = $objectManager;
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

        return $this->objectManager->create(
            $consumerConfig[QueueConfig::BROKER_CONSUMER_INSTANCE_TYPE],
            ['configuration' => $this->createConsumerConfiguration($consumerConfig)]
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
        $topics = [];
        foreach ($consumerConfig[QueueConfig::CONSUMER_HANDLERS] as $topic => $topicHandlers) {
            $topicCommunicationType = $consumerConfig[QueueConfig::CONSUMER_TYPE] == QueueConfig::CONSUMER_TYPE_SYNC
                ? ConsumerConfiguration::TYPE_SYNC
                : ConsumerConfiguration::TYPE_ASYNC;
            $handlers = [];
            foreach ($topicHandlers as $handlerConfig) {
                $handlers[] = [
                    $this->objectManager->create($handlerConfig[QueueConfig::CONSUMER_CLASS]),
                    $handlerConfig[QueueConfig::CONSUMER_METHOD]
                ];
            }
            $topics[$topic] = [
                ConsumerConfigurationInterface::TOPIC_HANDLERS => $handlers,
                ConsumerConfigurationInterface::TOPIC_TYPE => $topicCommunicationType
            ];
        }

        $configData = [
            ConsumerConfigurationInterface::CONSUMER_NAME => $consumerConfig[QueueConfig::CONSUMER_NAME],
            ConsumerConfigurationInterface::QUEUE_NAME => $consumerConfig[QueueConfig::CONSUMER_QUEUE],
            ConsumerConfigurationInterface::TOPICS => $topics
        ];

        return $this->objectManager->create(
            'Magento\Framework\MessageQueue\ConsumerConfiguration',
            ['data' => $configData]
        );
    }
}
