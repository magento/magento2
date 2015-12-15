<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\EnvReader;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\Config\Validator as ConfigValidator;
use Magento\Framework\Reflection\MethodsMap;

/**
 * Communication configuration validator. Validates data, that have been read from env.php.
 */
class Validator extends ConfigValidator
{
    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * Initialize dependencies
     *
     * @param MethodsMap $methodsMap
     */
    public function __construct(
        MethodsMap $methodsMap
    ) {
        $this->methodsMap = $methodsMap;
        parent::__construct($methodsMap);
    }

    /**
     * Validate config data
     *
     * @param array $configData
     * @param array|null $xmlConfigData
     * @return void
     */
    public function validate($configData, array $xmlConfigData = [])
    {
        if (isset($configData[QueueConfig::TOPICS])) {
            foreach ($configData[QueueConfig::TOPICS] as $topicName => $configDataItem) {
                $schemaType = $configDataItem[QueueConfig::TOPIC_SCHEMA][QueueConfig::TOPIC_SCHEMA_TYPE];
                $schemaMethod = $configDataItem[QueueConfig::TOPIC_SCHEMA][QueueConfig::TOPIC_SCHEMA_VALUE];
                $publisherName = $configDataItem[QueueConfig::TOPIC_PUBLISHER];
                $this->validateSchemaMethodType($schemaType, $schemaMethod, $topicName);
                $this->validateTopicPublisher(
                    $this->getAvailablePublishers($configData, $xmlConfigData),
                    $publisherName,
                    $topicName
                );
            }
        }
        if (isset($configData[QueueConfig::CONSUMERS])) {
            foreach ($configData[QueueConfig::CONSUMERS] as $consumerName => $configDataItem) {
                $handlers = isset($configDataItem[QueueConfig::CONSUMER_HANDLERS])
                    ? $configDataItem[QueueConfig::CONSUMER_HANDLERS] : [];
                foreach ($handlers as $handler) {
                    $this->validateHandlerType(
                        $handler[QueueConfig::CONSUMER_CLASS],
                        $handler[QueueConfig::CONSUMER_METHOD],
                        $consumerName
                    );
                }
            }
        }
        if (isset($configData[QueueConfig::BINDS])) {
            foreach ($configData[QueueConfig::BINDS] as $configDataItem) {
                $this->validateBindTopic(
                    $this->getAvailableTopics($configData, $xmlConfigData),
                    $configDataItem[QueueConfig::BIND_TOPIC]
                );
            }
        }
    }

    /**
     * Return all available publishers from xml and env configs
     *
     * @param array $configData
     * @param array $xmlConfigData
     * @return array
     */
    private function getAvailablePublishers($configData, $xmlConfigData)
    {
        $envConfigPublishers = isset($configData[QueueConfig::PUBLISHERS]) ? $configData[QueueConfig::PUBLISHERS] : [];
        $xmlConfigPublishers = isset($xmlConfigData[QueueConfig::PUBLISHERS])
            ? $xmlConfigData[QueueConfig::PUBLISHERS] : [];
        return array_unique(
            array_merge(
                array_keys($xmlConfigPublishers),
                array_keys($envConfigPublishers)
            )
        );
    }

    /**
     * Return all available topics from xml and env configs
     *
     * @param $configData
     * @param array $xmlConfigData
     * @return array
     */
    private function getAvailableTopics($configData, $xmlConfigData)
    {
        $envConfigTopics = isset($configData[QueueConfig::TOPICS]) ? $configData[QueueConfig::TOPICS] : [];
        $xmlConfigTopics = isset($xmlConfigData[QueueConfig::TOPICS]) ? $xmlConfigData[QueueConfig::TOPICS] : [];
        return array_unique(
            array_merge(
                array_keys($xmlConfigTopics),
                array_keys($envConfigTopics)
            )
        );
    }
}
