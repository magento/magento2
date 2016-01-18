<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;


use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Config\Validator;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * Converts MessageQueue config from \DOMDocument to array
 */
class TopicConfig implements \Magento\Framework\Config\ConverterInterface
{
    const DEFAULT_TYPE = 'amqp';
    const DEFAULT_EXCHANGE = 'magento';

    /**
     * @var Validator
     */
    private $xmlValidator;

    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param MethodsMap $methodsMap
     * @param Validator $xmlValidator
     * @param \Magento\Framework\Communication\ConfigInterface $communicationConfig
     */
    public function __construct(
        MethodsMap $methodsMap,
        Validator $xmlValidator,
        \Magento\Framework\Communication\ConfigInterface $communicationConfig
    ) {
        $this->methodsMap = $methodsMap;
        $this->xmlValidator = $xmlValidator;
        $this->communicationConfig = $communicationConfig;
    }

    /**
     * @inheritDoc
     */
    public function convert($source)
    {
        $topics = $this->extractTopics($source);
        $topics = $this->processWildcard($topics);
        $publishers = $this->buildPublishers($topics);
        $binds = $this->buildBinds($topics);
        $map = $this->buildExchangeTopicToQueue($topics);
        $consumers = $this->buildConsumers($topics);
        return [
            ConfigInterface::TOPICS => $this->buildTopicsConfiguration($topics),
            ConfigInterface::PUBLISHERS => $publishers,
            ConfigInterface::BINDS => $binds,
            ConfigInterface::CONSUMERS => $consumers,
            ConfigInterface::EXCHANGE_TOPIC_TO_QUEUES_MAP => $map,
        ];
    }

    private function buildTopicsConfiguration($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            $topicDefinition = $this->communicationConfig->getTopic($topicName);
            $schemaType =
                $topicDefinition['request_type'] == CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS
                    ? QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT
                    : QueueConfig::TOPIC_SCHEMA_TYPE_METHOD;
            $schemaValue = $topicDefinition[CommunicationConfig::TOPIC_REQUEST];
            $output[$topicName] = [
                'name' => $topicName,
                'schema' => [
                    'schema_type' => $schemaType,
                    'schema_value' => $schemaValue
                ],
                'response_schema' => [
                    'schema_type' => isset($topicDefinition['response']) ? QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT : null,
                    'schema_value' => $topicDefinition['response']
                ],
                'publisher' => $topicConfig['type'] . '-' . $topicConfig['exchange']
            ];
        }
        return $output;
    }

    /**
     * @param array $topics
     * @return array
     */
    private function buildConsumers($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            $topic = $this->communicationConfig->getTopic($topicName);
            foreach ($topicConfig['queues'] as $queueName => $queueConfig) {
                $output[$queueConfig['consumer']] = [
                    'name' => $queueConfig['consumer'],
                    'queue' => $queueName,
                    'handlers' => $queueConfig['handlers'],
                    'instance_type' => $queueConfig['consumerInstance'],
                    'consumer_type' => $topic[\Magento\Framework\Communication\ConfigInterface::TOPIC_IS_SYNCHRONOUS] ? 'sync' : 'async',
                    'max_messages' => $queueConfig['maxMessages'],
                    'connection' => $topicConfig['type']
                ];
            }
        }
        return $output;
    }

    /**
     * @param array $topics
     * @return array
     */
    private function processWildcard($topics)
    {
        $topicDefinitions = $this->communicationConfig->getTopics();
        $wildcardKeys = [];
        foreach ($topics as $topicName => $topicConfig) {
            if (strpos($topicName, '*') !== false || strpos($topicName, '#') !== false) {
                $wildcardKeys[] = $topicName;
            }
        }
        foreach (array_unique($wildcardKeys) as $wildcardKey) {
            $pattern = $this->xmlValidator->buildWildcardPattern($wildcardKey);
            foreach (array_keys($topicDefinitions) as $topicName) {
                if (preg_match($pattern, $topicName)) {

                    if (isset($topics[$topicName])) {
                        $topics[$topicName] = array_merge($topics[$topicName], $topics[$wildcardKey]);
                    } else {
                        $topics[$topicName] = $topics[$wildcardKey];
                    }
                }
            }
            unset($topics[$wildcardKey]);
        }
        return $topics;
    }

    /**
     * @param array $topics
     * @return array
     */
    private function buildPublishers($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            $publisherName = $topicConfig['type'] . '-' . $topicConfig['exchange'];
            $output[$publisherName] = [
                'name' => $publisherName,
                'connection' => $topicConfig['type'],
                'exchange' => $topicConfig['exchange']
            ];
        }
        return $output;
    }

    /**
     * @param $topics
     * @return array
     */
    private function buildBinds($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            foreach ($topicConfig['queues'] as $queueName => $queue) {
                $name = $topicName . '--' . $topicConfig['exchange']. '--' .$queueName;
                $output[$name] = [
                    'queue' => $queueName,
                    'exchange' => $topicConfig['exchange'],
                    'topic' => $topicName,
                ];
            }
        }
        return $output;
    }

    private function buildExchangeTopicToQueue($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            $key = $topicConfig['exchange'] . '--' . $topicName;
            foreach ($topicConfig['queues'] as $queueName => $queueConfig) {
                $output[$key][] = $queueName;
                $output[$key] = array_unique($output[$key]);
            }
        }
        return $output;
    }


    /**
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    private function extractTopics($config)
    {
        $output = [];
        /** @var $topicNode \DOMElement */
        foreach ($config->getElementsByTagName('topic') as $topicNode) {
            $topicName = $this->getAttributeValue($topicNode, 'name');
            $output[$topicName] = [
                ConfigInterface::TOPIC_NAME => $topicName,
                'type' => $this->getAttributeValue($topicNode, 'type', self::DEFAULT_TYPE),
                'exchange' => $this->getAttributeValue($topicNode, 'exchange', self::DEFAULT_EXCHANGE),
                'consumerInstance' => $this->getAttributeValue($topicNode, 'consumerInstance'),
                'maxMessages' => $this->getAttributeValue($topicNode, 'maxMessages'),
                'queues' => $this->extractQueuesFromTopic($topicNode, $topicName)
            ];
        }
        return $output;
    }

    /**
     * Extract queues configuration from the topic node.
     *
     * @param \DOMElement $topicNode
     * @return mixed
     */
    protected function extractQueuesFromTopic(\DOMElement $topicNode, $topicName)
    {
        $queues = [];
        $topicConfig = $this->communicationConfig->getTopic($topicName);
        /** @var $queueNode \DOMElement */
        foreach ($topicNode->getElementsByTagName('queue') as $queueNode) {
            $handler = $this->getAttributeValue($queueNode, 'handler');
            $queueName = $this->getAttributeValue($queueNode, 'name');
            $queue = [
               'name'=> $queueName,
               'handlerName' => $this->getAttributeValue($queueNode, 'handlerName'),
               'handlers' => $handler ? ['default' => $handler] : $topicConfig['handlers'],
               'exchange' => $this->getAttributeValue($queueNode, 'exchange'),
               'consumer' => $this->getAttributeValue($queueNode, 'consumer'),
               'consumerInstance' => $this->getAttributeValue($queueNode, 'consumerInstance'),
               'maxMessages' => $this->getAttributeValue($queueNode, 'maxMessages', PHP_INT_MAX),
               'type' => $this->getAttributeValue($queueNode, 'type')

            ];
            $queues[$queueName] = $queue;
        }
        return $queues;
    }

    /**
     * Get attribute value of the given node
     *
     * @param \DOMNode $node
     * @param string $attributeName
     * @param mixed $default
     * @return string|null
     */
    protected function getAttributeValue(\DOMNode $node, $attributeName, $default = null)
    {
        $item =  $node->attributes->getNamedItem($attributeName);
        return $item ? $item->nodeValue : $default;
    }
}
