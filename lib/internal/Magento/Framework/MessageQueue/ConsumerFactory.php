<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

/**
 * Class which creates Consumers
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     * @param ObjectManagerInterface $objectManager
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        QueueConfig $queueConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Return the actual Consumer implementation for the given consumer name.
     *
     * @param string $consumerName
     * @param int $batchSize [optional]
     * @return ConsumerInterface
     * @throws LocalizedException
     */
    public function get($consumerName, $batchSize = 0)
    {
        $consumerConfig = $this->getConsumerConfig()->getConsumer($consumerName);
        if ($consumerConfig === null) {
            throw new LocalizedException(
                new Phrase('Specified consumer "%consumer" is not declared.', ['consumer' => $consumerName])
            );
        }

        return $this->objectManager->create(
            $consumerConfig->getConsumerInstance(),
            [
                'configuration' => $this->createConsumerConfiguration($consumerConfig),
                'batchSize' => $batchSize,
            ]
        );
    }

    /**
     * Creates the objects necessary for the ConsumerConfigurationInterface to configure a Consumer.
     *
     * @param ConsumerConfigItemInterface $consumerConfigItem
     * @return ConsumerConfigurationInterface
     */
    private function createConsumerConfiguration($consumerConfigItem)
    {
        $customConsumerHandlers = [];
        foreach ($consumerConfigItem->getHandlers() as $handlerConfig) {
            $customConsumerHandlers[] = [
                $this->objectManager->create($handlerConfig->getType()),
                $handlerConfig->getMethod()
            ];
        }
        $topics = [];
        foreach ($this->getCommunicationConfig()->getTopics() as $topicConfig) {
            $topicName = $topicConfig[CommunicationConfig::TOPIC_NAME];
            $topics[$topicName] = [
                ConsumerConfigurationInterface::TOPIC_HANDLERS => $customConsumerHandlers
                    ?: $this->getHandlersFromCommunicationConfig($topicName),
                ConsumerConfigurationInterface::TOPIC_TYPE => $topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]
                    ? ConsumerConfiguration::TYPE_SYNC
                    : ConsumerConfiguration::TYPE_ASYNC
            ];
        }
        $configData = [
            ConsumerConfigurationInterface::CONSUMER_NAME => $consumerConfigItem->getName(),
            ConsumerConfigurationInterface::QUEUE_NAME => $consumerConfigItem->getQueue(),
            ConsumerConfigurationInterface::TOPICS => $topics,
            ConsumerConfigurationInterface::MAX_MESSAGES => $consumerConfigItem->getMaxMessages(),
        ];

        return $this->objectManager->create(
            \Magento\Framework\MessageQueue\ConsumerConfiguration::class,
            ['data' => $configData]
        );
    }

    /**
     * Get consumer config.
     *
     * @return ConsumerConfig
     *
     * @deprecated 102.0.2
     */
    private function getConsumerConfig()
    {
        if ($this->consumerConfig === null) {
            $this->consumerConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ConsumerConfig::class);
        }
        return $this->consumerConfig;
    }

    /**
     * Get communication config.
     *
     * @return CommunicationConfig
     *
     * @deprecated 102.0.2
     */
    private function getCommunicationConfig()
    {
        if ($this->communicationConfig === null) {
            $this->communicationConfig = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CommunicationConfig::class);
        }
        return $this->communicationConfig;
    }

    /**
     * Get handlers by topic based on communication config.
     *
     * @param string $topicName
     * @return array
     */
    private function getHandlersFromCommunicationConfig($topicName)
    {
        $topicConfig = $this->getCommunicationConfig()->getTopic($topicName);
        $handlers = [];
        foreach ($topicConfig[CommunicationConfig::TOPIC_HANDLERS] as $handlerConfig) {
            $handlers[] = [
                $this->objectManager->create($handlerConfig[CommunicationConfig::HANDLER_TYPE]),
                $handlerConfig[CommunicationConfig::HANDLER_METHOD]
            ];
        }
        return $handlers;
    }
}
