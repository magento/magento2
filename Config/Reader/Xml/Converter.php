<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml;

use Magento\Framework\Reflection\MethodsMap;
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
     * Initialize dependencies
     *
     * @param \Magento\Framework\Communication\ConfigInterface $communicationConfig
     * @param Validator $xmlValidator
     */
    public function __construct(
        \Magento\Framework\Communication\ConfigInterface $communicationConfig,
        Validator $xmlValidator
    ) {
        $this->communicationConfig = $communicationConfig;
        $this->xmlValidator = $xmlValidator;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $brokers = $this->processBrokerConfiguration($source);
        $publishers = $this->processPublisherConfiguration($brokers);
        $topics = $this->processTopicsConfiguration($brokers);
        $binds = $this->processBindsConfiguration($brokers);
        $consumers = $this->processConsumerConfiguration($brokers);

        return [
            QueueConfig::PUBLISHERS => $publishers,
            QueueConfig::TOPICS => $topics,
            QueueConfig::CONSUMERS => $consumers,
            QueueConfig::BINDS => $binds,
            QueueConfig::EXCHANGE_TOPIC_TO_QUEUES_MAP => $this->buildExchangeTopicToQueuesMap($binds, $topics),
        ];
    }

    /**
     * Extract broker configuration.
     *
     * @param \DOMDocument $config
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function processBrokerConfiguration($config)
    {
        $output = [];
        /** @var $brokerNode \DOMNode */
        foreach ($config->documentElement->childNodes as $brokerNode) {
            if ($brokerNode->nodeName != 'broker' || $brokerNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $topicName = $brokerNode->attributes->getNamedItem('topic')->nodeValue;
            $type = $brokerNode->attributes->getNamedItem('type')->nodeValue;
            $exchange = $brokerNode->attributes->getNamedItem('exchange')->nodeValue;


            $output[$topicName] = [
                QueueConfig::BROKER_TOPIC => $topicName,
                QueueConfig::BROKER_TYPE => $type,
                QueueConfig::BROKER_EXCHANGE => $exchange,
            ];

            /** @var \DOMNode $consumerNode */
            foreach ($brokerNode->childNodes as $consumerNode) {
                if ($consumerNode->nodeName != 'consumer' || $consumerNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $consumerName = $consumerNode->attributes->getNamedItem('name')->nodeValue;
                $queue = $consumerNode->attributes->getNamedItem('queue')->nodeValue;
                $consumerInstanceType = $consumerNode->attributes->getNamedItem('instanceType')
                    ? $consumerNode->attributes->getNamedItem('instanceType')->nodeValue
                    : null;
                $maxMessages = $consumerNode->attributes->getNamedItem('max_messages')
                    ? $consumerNode->attributes->getNamedItem('max_messages')->nodeValue
                    : null;

                $output[$topicName][QueueConfig::BROKER_CONSUMERS][$consumerName] = [
                    QueueConfig::BROKER_CONSUMER_NAME => $consumerName,
                    QueueConfig::BROKER_CONSUMER_QUEUE => $queue,
                    QueueConfig::BROKER_CONSUMER_INSTANCE_TYPE => $consumerInstanceType,
                    QueueConfig::BROKER_CONSUMER_MAX_MESSAGES => $maxMessages,
                ];
            }
        }
        return $output;
    }

    /**
     * Create consumer configuration based on broker configuration.
     *
     * @param array $config
     * @return array
     */
    protected function processConsumerConfiguration($config)
    {
        $output = [];
        foreach ($config as $topicName => $brokerConfig) {
            $handlers = [];
            $handlers[$topicName] = $this->getTopicHandlers($topicName);
            $topicConfig = $this->communicationConfig->getTopic($topicName);

            foreach ($brokerConfig[QueueConfig::BROKER_CONSUMERS] as $consumerKey => $consumerConfig) {
                $output[$consumerKey] = [
                    QueueConfig::CONSUMER_NAME => $consumerKey,
                    QueueConfig::CONSUMER_QUEUE => $consumerConfig[QueueConfig::BROKER_CONSUMER_QUEUE],
                    QueueConfig::CONSUMER_CONNECTION => $brokerConfig[QueueConfig::BROKER_TYPE],
                    QueueConfig::CONSUMER_TYPE =>
                        $topicConfig[\Magento\Framework\Communication\ConfigInterface::TOPIC_IS_SYNCHRONOUS]
                            ? QueueConfig::CONSUMER_TYPE_SYNC : QueueConfig::CONSUMER_TYPE_ASYNC,
                    QueueConfig::CONSUMER_HANDLERS => $handlers,
                    QueueConfig::CONSUMER_MAX_MESSAGES => $consumerConfig[QueueConfig::BROKER_CONSUMER_MAX_MESSAGES],
                    QueueConfig::CONSUMER_INSTANCE_TYPE => $consumerConfig[QueueConfig::BROKER_CONSUMER_INSTANCE_TYPE],
                ];
            }
        }
        return $output;
    }

    /**
     * Create publishers configuration based on broker configuration.
     *
     * @param array $config
     * @return array
     */
    protected function processPublisherConfiguration($config)
    {
        $output = [];
        foreach ($config as $brokerConfig) {
            $publisherName = $brokerConfig['type'] . '-' . $brokerConfig['exchange'];
            $output[$publisherName] = [
                QueueConfig::PUBLISHER_NAME => $publisherName,
                QueueConfig::PUBLISHER_CONNECTION => $brokerConfig['type'],
                QueueConfig::PUBLISHER_EXCHANGE => $brokerConfig['exchange'],
            ];
        }
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

    /**
     * Create binds configuration based on broker configuration.
     *
     * @param array $config
     * @return array
     */
    protected function processBindsConfiguration($config)
    {
        $output = [];
        foreach ($config as $brokerConfig) {
            foreach ($brokerConfig[QueueConfig::BROKER_CONSUMERS] as $consumerConfig) {
                $queueName = $consumerConfig[QueueConfig::BROKER_CONSUMER_QUEUE];
                $exchangeName = $brokerConfig[QueueConfig::BROKER_EXCHANGE];
                $topicName = $brokerConfig[QueueConfig::BROKER_TOPIC];
                $key = $this->getBindName($topicName, $exchangeName, $queueName);
                $output[$key] = [
                    QueueConfig::BIND_QUEUE => $queueName,
                    QueueConfig::BIND_EXCHANGE => $exchangeName,
                    QueueConfig::BIND_TOPIC => $topicName,
                ];
            }
        }
        return $output;
    }

    /**
     * Build map which allows optimized search of queues corresponding to the specified exchange and topic pair.
     *
     * @param array $binds
     * @param array $topics
     * @return array
     */
    protected function buildExchangeTopicToQueuesMap($binds, $topics)
    {
        $output = [];
        $wildcardKeys = [];
        foreach ($binds as $bind) {
            $key = $bind[QueueConfig::BIND_EXCHANGE] . '--' . $bind[QueueConfig::BIND_TOPIC];
            if (strpos($key, '*') !== false || strpos($key, '#') !== false) {
                $wildcardKeys[] = $key;
            }
            $output[$key][] = $bind[QueueConfig::BIND_QUEUE];
        }

        foreach (array_unique($wildcardKeys) as $wildcardKey) {
            $keySplit = explode('--', $wildcardKey);
            $exchangePrefix = $keySplit[0];
            $key = $keySplit[1];
            $pattern = $this->xmlValidator->buildWildcardPattern($key);
            foreach (array_keys($topics) as $topic) {
                if (preg_match($pattern, $topic)) {
                    $fullTopic = $exchangePrefix . '--' . $topic;
                    if (isset($output[$fullTopic])) {
                        $output[$fullTopic] = array_merge($output[$fullTopic], $output[$wildcardKey]);
                    } else {
                        $output[$fullTopic] = $output[$wildcardKey];
                    }
                }
            }
            unset($output[$wildcardKey]);
        }
        return $output;
    }

    /**
     * Return bind name
     *
     * @param string $topicName
     * @param string $exchangeName
     * @param string $queueName
     * @return string
     */
    private function getBindName($topicName, $exchangeName, $queueName)
    {
        return $topicName . '--' . $exchangeName . '--' . $queueName;
    }

    /**
     * Return topic handlers
     *
     * @param string $topicName
     * @return array
     */
    private function getTopicHandlers($topicName)
    {
        $topicHandlers = [];
        $communicationTopicHandlers = $this->communicationConfig->getTopicHandlers($topicName);
        foreach ($communicationTopicHandlers as $handlerName => $handler) {
            $topicHandlers[$handlerName] = [
                QueueConfig::CONSUMER_HANDLER_TYPE => $handler[CommunicationConfig::HANDLER_TYPE],
                QueueConfig::CONSUMER_HANDLER_METHOD => $handler[CommunicationConfig::HANDLER_METHOD]
            ];
        }
        return $topicHandlers;
    }
}
