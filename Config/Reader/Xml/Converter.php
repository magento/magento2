<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\Config\Validator;

/**
 * Converts MessageQueue config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const SERVICE_METHOD_NAME_PATTERN = '/^([a-zA-Z\\\\]+)::([a-zA-Z]+)$/';
    const DEFAULT_HANDLER = 'defaultHandler';

    /**
     * @var \Magento\Framework\Communication\ConfigInterface
     */
    private $communicationConfig;

    /**
     * @var Validator
     */
    private $xmlValidator;

    /**
     * @var Converter\DeprecatedFormat
     */
    protected $deprecatedConfig;

    /**
     * @var Converter\TopicConfig
     */
    protected $topicConfig;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Communication\ConfigInterface $communicationConfig
     * @param Validator $xmlValidator
     * @param Converter\DeprecatedFormat $deprecatedConfig
     * @param Converter\TopicConfig $topicConfig
     */
    public function __construct(
        \Magento\Framework\Communication\ConfigInterface $communicationConfig,
        Validator $xmlValidator,
        Converter\DeprecatedFormat $deprecatedConfig,
        Converter\TopicConfig $topicConfig
    ) {
        $this->communicationConfig = $communicationConfig;
        $this->xmlValidator = $xmlValidator;
        $this->deprecatedConfig = $deprecatedConfig;
        $this->topicConfig = $topicConfig;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $deprecatedConfigNodes = $this->deprecatedConfig->convert($source);
        $topicConfigNodes = $this->topicConfig->convert($source);
        $merged = array_merge_recursive($deprecatedConfigNodes, $topicConfigNodes);
        $output = $this->processTopicsConfiguration($merged);

        return $output;
    }

    /**
     * Create topics configuration based on broker configuration.
     *
     * @param array $config
     * @return array
     */
    protected function processTopicsConfiguration($config)
    {
        $output = [];
        foreach ($this->communicationConfig->getTopics() as $topicConfig) {
            $topicName = $topicConfig[CommunicationConfig::TOPIC_NAME];
            if (!isset($config[$topicName])) {
                continue;
            }
            $schemaType =
                $topicConfig[CommunicationConfig::TOPIC_REQUEST_TYPE] == CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS
                ? QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT
                : QueueConfig::TOPIC_SCHEMA_TYPE_METHOD;
            $schemaValue = $topicConfig[CommunicationConfig::TOPIC_REQUEST];
            $output[$topicName] = [
                QueueConfig::TOPIC_NAME => $topicName,
                QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => $schemaType,
                    QueueConfig::TOPIC_SCHEMA_VALUE => $schemaValue
                ],
                QueueConfig::TOPIC_RESPONSE_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE =>
                        isset($topicConfig[CommunicationConfig::TOPIC_RESPONSE]) ? QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT
                            : null,
                    QueueConfig::TOPIC_SCHEMA_VALUE => $topicConfig[CommunicationConfig::TOPIC_RESPONSE]
                ],
                QueueConfig::TOPIC_PUBLISHER =>
                    $config[$topicName][QueueConfig::BROKER_TYPE] .
                    '-' . $config[$topicName][QueueConfig::BROKER_EXCHANGE]
            ];
        }
        return $output;
    }
}
