<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\XmlReader;

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
     * @var MethodsMap
     */
    private $methodsMap;

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
     * @param MethodsMap $methodsMap
     * @param \Magento\Framework\Communication\ConfigInterface $communicationConfig
     * @param Validator $xmlValidator
     */
    public function __construct(
        MethodsMap $methodsMap,
        \Magento\Framework\Communication\ConfigInterface $communicationConfig,
        Validator $xmlValidator
    ) {
        $this->methodsMap = $methodsMap;
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
        /** Process Publishers Configuration */
        $publishers = $this->extractPublishers($source);
        $brokerPublishers = $this->processPublisherConfiguration($brokers);
        $publishers = array_merge($publishers, $brokerPublishers);

        /** Process Topics Configuration */
        $topics = $this->extractTopics($source, $publishers);
        $brokerTopics = $this->processTopicsConfiguration($brokers);
        $topics = array_merge($topics, $brokerTopics);

        $binds = $this->extractBinds($source, $topics);

        /** Process Consumers Configuration */
        $consumers = $this->extractConsumers($source, $binds);
        $brokerConsumers = $this->processConsumerConfiguration($brokers);
        $consumers = array_merge($consumers, $brokerConsumers);
        $brokerBinds = $this->processBindsConfiguration($brokers);
        //nested unique array
        $binds = array_map("unserialize", array_unique(array_map("serialize", array_merge($binds, $brokerBinds))));
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
            $pattern = $this->buildWildcardPattern($key);
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
     * Construct perl regexp pattern for matching topic names from wildcard key.
     *
     * @param string $wildcardKey
     * @return string
     */
    protected function buildWildcardPattern($wildcardKey)
    {
        $pattern = '/^' . str_replace('.', '\.', $wildcardKey);
        $pattern = str_replace('#', '.+', $pattern);
        $pattern = str_replace('*', '[^\.]+', $pattern);
        if (strpos($wildcardKey, '#') == strlen($wildcardKey)) {
            $pattern .= '/';
        } else {
            $pattern .= '$/';
        }

        return $pattern;
    }

    /**
     * Get message schema defined by service method signature.
     *
     * @param string $schemaId
     * @param string $topic
     * @return array
     * @deprecated
     */
    protected function getSchemaDefinedByMethod($schemaId, $topic)
    {
        preg_match(self::SERVICE_METHOD_NAME_PATTERN, $schemaId, $matches);
        $serviceClass = $matches[1];
        $serviceMethod = $matches[2];
        $this->xmlValidator->validateSchemaMethodType($serviceClass, $serviceMethod, $topic);
        $result = [];
        $paramsMeta = $this->methodsMap->getMethodParams($serviceClass, $serviceMethod);
        foreach ($paramsMeta as $paramPosition => $paramMeta) {
            $result[] = [
                QueueConfig::SCHEMA_METHOD_PARAM_NAME => $paramMeta[MethodsMap::METHOD_META_NAME],
                QueueConfig::SCHEMA_METHOD_PARAM_POSITION => $paramPosition,
                QueueConfig::SCHEMA_METHOD_PARAM_IS_REQUIRED => !$paramMeta[MethodsMap::METHOD_META_HAS_DEFAULT_VALUE],
                QueueConfig::SCHEMA_METHOD_PARAM_TYPE => $paramMeta[MethodsMap::METHOD_META_TYPE],
            ];
        }
        return $result;
    }

    /**
     * Identify which option is used to define message schema: data interface or service method params
     *
     * @param string $schemaId
     * @return string
     * @deprecatedQueueConfig
     */
    protected function identifySchemaType($schemaId)
    {
        return preg_match(self::SERVICE_METHOD_NAME_PATTERN, $schemaId)
            ? QueueConfig::TOPIC_SCHEMA_TYPE_METHOD
            : QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT;
    }

    /**
     * Extract publishers configuration.
     *
     * @param \DOMDocument $config
     * @return array
     * @deprecated
     */
    protected function extractPublishers(\DOMDocument $config)
    {
        $output = [];
        /** @var $publisherNode \DOMNode */
        foreach ($config->getElementsByTagName('publisher') as $publisherNode) {
            $publisherName = $publisherNode->attributes->getNamedItem('name')->nodeValue;
            $output[$publisherName] = [
                QueueConfig::PUBLISHER_NAME => $publisherName,
                QueueConfig::PUBLISHER_CONNECTION => $publisherNode->attributes->getNamedItem('connection')->nodeValue,
                QueueConfig::PUBLISHER_EXCHANGE => $publisherNode->attributes->getNamedItem('exchange')->nodeValue
            ];
        }
        return $output;
    }

    /**
     * Extract consumers configuration.
     *
     * @param \DOMDocument $config
     * @param array $binds
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @deprecated
     */
    protected function extractConsumers(\DOMDocument $config, $binds)
    {
        $map = [];
        foreach ($binds as $bind) {
            $map[$bind['queue']][] = $bind['topic'];
        }
        $output = [];
        /** @var $consumerNode \DOMNode */
        foreach ($config->documentElement->childNodes as $consumerNode) {
            if ($consumerNode->nodeName != 'consumer' || $consumerNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $consumerName = $consumerNode->attributes->getNamedItem('name')->nodeValue;
            $maxMessages = $consumerNode->attributes->getNamedItem('max_messages');
            $connections = $consumerNode->attributes->getNamedItem('connection');
            $consumerInstanceType = $consumerNode->attributes->getNamedItem('executor');
            $queueName = $consumerNode->attributes->getNamedItem('queue')->nodeValue;
            $handler = [
                QueueConfig::CONSUMER_CLASS => $consumerNode->attributes->getNamedItem('class')->nodeValue,
                QueueConfig::CONSUMER_METHOD => $consumerNode->attributes->getNamedItem('method')->nodeValue,
            ];
            $this->xmlValidator->validateHandlerType(
                $handler[QueueConfig::CONSUMER_CLASS],
                $handler[QueueConfig::CONSUMER_METHOD],
                $consumerName
            );
            $handlers = [];
            if (isset($map[$queueName])) {
                foreach ($map[$queueName] as $topic) {
                    $handlers[$topic][self::DEFAULT_HANDLER] = $handler;
                }
            }
            $output[$consumerName] = [
                QueueConfig::CONSUMER_NAME => $consumerName,
                QueueConfig::CONSUMER_QUEUE => $queueName,
                QueueConfig::CONSUMER_CONNECTION => $connections ? $connections->nodeValue : null,
                QueueConfig::CONSUMER_TYPE => QueueConfig::CONSUMER_TYPE_ASYNC,
                QueueConfig::CONSUMER_HANDLERS => $handlers,
                QueueConfig::CONSUMER_MAX_MESSAGES => $maxMessages ? $maxMessages->nodeValue : null,
                QueueConfig::CONSUMER_INSTANCE_TYPE => $consumerInstanceType ? $consumerInstanceType->nodeValue : null,
            ];
        }
        return $output;
    }

    /**
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @param array $publishers
     * @return array
     * @deprecated
     */
    protected function extractTopics(\DOMDocument $config, $publishers)
    {
        $output = [];
        /** @var $topicNode \DOMNode */
        foreach ($config->getElementsByTagName('topic') as $topicNode) {
            $topicName = $topicNode->attributes->getNamedItem('name')->nodeValue;
            $schemaId = $topicNode->attributes->getNamedItem('schema')->nodeValue;
            $schemaType = $this->identifySchemaType($schemaId);
            $schemaValue = ($schemaType == QueueConfig::TOPIC_SCHEMA_TYPE_METHOD)
                ? $this->getSchemaDefinedByMethod($schemaId, $topicName)
                : $schemaId;
            $publisherName = $topicNode->attributes->getNamedItem('publisher')->nodeValue;
            $this->xmlValidator->validateTopicPublisher(array_keys($publishers), $publisherName, $topicName);

            $output[$topicName] = [
                QueueConfig::TOPIC_NAME => $topicName,
                QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => $schemaType,
                    QueueConfig::TOPIC_SCHEMA_VALUE => $schemaValue
                ],
                QueueConfig::TOPIC_RESPONSE_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => null,
                    QueueConfig::TOPIC_SCHEMA_VALUE => null
                ],
                QueueConfig::TOPIC_PUBLISHER => $publisherName
            ];
        }
        return $output;
    }

    /**
     * Extract binds configuration.
     *
     * @param \DOMDocument $config
     * @param array $topics
     * @return array
     * @deprecated
     */
    protected function extractBinds(\DOMDocument $config, $topics)
    {
        $output = [];
        /** @var $bindNode \DOMNode */
        foreach ($config->getElementsByTagName('bind') as $bindNode) {
            $queueName = $bindNode->attributes->getNamedItem('queue')->nodeValue;
            $exchangeName = $bindNode->attributes->getNamedItem('exchange')->nodeValue;
            $topicName = $bindNode->attributes->getNamedItem('topic')->nodeValue;
            $key = $this->getBindName($topicName, $exchangeName, $queueName);
            $this->xmlValidator->validateBindTopic(array_keys($topics), $topicName);
            $output[$key] = [
                QueueConfig::BIND_QUEUE => $queueName,
                QueueConfig::BIND_EXCHANGE => $exchangeName,
                QueueConfig::BIND_TOPIC => $topicName,
            ];
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
     * @param $topicName
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
