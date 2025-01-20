<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Config\Validator;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\ConsumerInterface;

/**
 * Converts MessageQueue config from \DOMDocument to array
 *
 * @deprecated 103.0.0
 */
class TopicConfig implements \Magento\Framework\Config\ConverterInterface
{
    public const DEFAULT_TYPE = 'db';
    public const DEFAULT_EXCHANGE = 'magento';
    public const DEFAULT_INSTANCE = ConsumerInterface::class;

    /**
     * @var Validator
     */
    private $xmlValidator;

    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     *
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param MethodsMap $methodsMap
     * @param Validator $xmlValidator
     * @param CommunicationConfig $communicationConfig
     * @param DefaultValueProvider|null $defaultValueProvider
     */
    public function __construct(
        MethodsMap $methodsMap,
        Validator $xmlValidator,
        CommunicationConfig $communicationConfig,
        ?DefaultValueProvider $defaultValueProvider = null
    ) {
        $this->methodsMap = $methodsMap;
        $this->xmlValidator = $xmlValidator;
        $this->communicationConfig = $communicationConfig;
        $this->defaultValueProvider = $defaultValueProvider
            ?? ObjectManager::getInstance()->get(DefaultValueProvider::class);
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

    /**
     * Generate list of topics.
     *
     * @param array $topics
     * @return array
     */
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
                'is_synchronous' => $topicDefinition[CommunicationConfig::TOPIC_IS_SYNCHRONOUS],
                'publisher' => $topicConfig['type'] . '-' . $topicConfig['exchange']
            ];
        }
        return $output;
    }

    /**
     * Generate consumers.
     *
     * @param array $topics
     * @return array
     */
    private function buildConsumers($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            $topic = $this->communicationConfig->getTopic($topicName);
            foreach ($topicConfig['queues'] as $queueName => $queueConfig) {
                $handlers = [];
                foreach ($queueConfig['handlers'] as $handler) {
                    if (!isset($handler[QueueConfig::CONSUMER_CLASS])) {
                        $handlerExploded = explode('::', $handler);
                        unset($handler);
                        $handler[QueueConfig::CONSUMER_CLASS] = $handlerExploded[0];
                        $handler[QueueConfig::CONSUMER_METHOD] = $handlerExploded[1];
                    }
                    $handlers[] = $handler;
                }
                $queueConfig['handlers'] = $handlers;

                $output[$queueConfig['consumer']] = [
                    'name' => $queueConfig['consumer'],
                    'queue' => $queueName,
                    'handlers' => [$topicName => $queueConfig['handlers']],
                    'instance_type' => $queueConfig['consumerInstance'] != null
                        ? $queueConfig['consumerInstance'] : self::DEFAULT_INSTANCE,
                    'consumer_type' => $topic[CommunicationConfig::TOPIC_IS_SYNCHRONOUS] ? 'sync' : 'async',
                    'max_messages' => $queueConfig['maxMessages'],
                    'connection' => $topicConfig['type']
                ];
            }
        }
        return $output;
    }

    /**
     * Generate topics list based on wildcards.
     *
     * @param array $topics
     * @return array
     */
    private function processWildcard($topics)
    {
        $topicDefinitions = $this->communicationConfig->getTopics();
        $wildcardKeys = [];
        $topicNames = array_keys($topics);
        foreach ($topicNames as $topicName) {
            if (strpos($topicName, '*') !== false || strpos($topicName, '#') !== false) {
                $wildcardKeys[] = $topicName;
            }
        }
        foreach (array_unique($wildcardKeys) as $wildcardKey) {
            $pattern = $this->xmlValidator->buildWildcardPattern($wildcardKey);
            foreach (array_keys($topicDefinitions) as $topicName) {
                if (preg_match($pattern, $topicName)) {
                    if (isset($topics[$topicName])) {
                        // @codingStandardsIgnoreStart
                        $topics[$topicName] = array_merge($topics[$topicName], $topics[$wildcardKey]);
                        // @codingStandardsIgnoreEnd
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
     * Generate publishers
     *
     * @param array $topics
     * @return array
     */
    private function buildPublishers($topics)
    {
        $output = [];
        foreach ($topics as $topicConfig) {
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
     * Generate binds
     *
     * @param array $topics
     * @return array
     */
    private function buildBinds($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            $queueNames = array_keys($topicConfig['queues']);
            foreach ($queueNames as $queueName) {
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

    /**
     * Generate topic-to-queues map.
     *
     * @param array $topics
     * @return array
     */
    private function buildExchangeTopicToQueue($topics)
    {
        $output = [];
        foreach ($topics as $topicName => $topicConfig) {
            $key = $topicConfig['type'] . '-' . $topicConfig['exchange'] . '--' . $topicName;
            $queueNames = array_keys($topicConfig['queues']);
            foreach ($queueNames as $queueName) {
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
        /** @var $brokerNode \DOMElement */
        foreach ($config->getElementsByTagName('broker') as $brokerNode) {
            $topicName = $this->getAttributeValue($brokerNode, 'topic');
            $output[$topicName] = [
                ConfigInterface::TOPIC_NAME => $topicName,
                'type' => $this->getAttributeValue(
                    $brokerNode,
                    'type',
                    $this->defaultValueProvider->getConnection()
                ),
                'exchange' => $this->getAttributeValue($brokerNode, 'exchange', self::DEFAULT_EXCHANGE),
                'consumerInstance' => $this->getAttributeValue($brokerNode, 'consumerInstance'),
                'maxMessages' => $this->getAttributeValue($brokerNode, 'maxMessages'),
                'queues' => $this->extractQueuesFromBroker($brokerNode, $topicName)
            ];
        }
        return $output;
    }

    /**
     * Extract queues configuration from the topic node.
     *
     * @param \DOMElement $brokerNode
     * @param string $topicName
     * @return array
     */
    protected function extractQueuesFromBroker(\DOMElement $brokerNode, $topicName)
    {
        $queues = [];
        $topicConfig = $this->communicationConfig->getTopic($topicName);
        /** @var $queueNode \DOMElement */
        foreach ($brokerNode->getElementsByTagName('queue') as $queueNode) {
            $handler = $this->getAttributeValue($queueNode, 'handler');
            $queueName = $this->getAttributeValue($queueNode, 'name');
            $queue = [
               'name'=> $queueName,
               'handlerName' => $this->getAttributeValue($queueNode, 'handlerName'),
               'handlers' => $handler ? ['default' => $handler] : $topicConfig['handlers'],
               'exchange' => $this->getAttributeValue($queueNode, 'exchange'),
               'consumer' => $this->getAttributeValue($queueNode, 'consumer'),
               'consumerInstance' => $this->getAttributeValue($queueNode, 'consumerInstance'),
               'maxMessages' => $this->getAttributeValue($queueNode, 'maxMessages', null),
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
        $item = $node->attributes->getNamedItem($attributeName);
        return $item ? $item->nodeValue : $default;
    }
}
