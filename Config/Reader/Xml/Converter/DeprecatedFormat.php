<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;

use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\Config\Validator;
use Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;

class DeprecatedFormat implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var Validator
     */
    private $xmlValidator;

    /**
     * Initialize dependencies
     *
     * @param MethodsMap $methodsMap
     * @param Validator $xmlValidator
     */
    public function __construct(
        MethodsMap $methodsMap,
        Validator $xmlValidator
    ) {
        $this->methodsMap = $methodsMap;
        $this->xmlValidator = $xmlValidator;
    }

    /**
     * @inheritDoc
     */
    public function convert($source)
    {
        $publishers = $this->extractPublishers($source);
        $topics = $this->extractTopics($source, $publishers);
        $binds = $this->extractBinds($source, $topics);
        $consumers = $this->extractConsumers($source, $binds, $topics);
        return [
            QueueConfig::PUBLISHERS => $publishers,
            QueueConfig::TOPICS => $topics,
            QueueConfig::CONSUMERS => $consumers,
            QueueConfig::BINDS => $binds,
            QueueConfig::EXCHANGE_TOPIC_TO_QUEUES_MAP => $this->buildExchangeTopicToQueuesMap($binds, $topics),
        ];
    }

    /**
     * Extract publishers configuration.
     *
     * @param \DOMDocument $config
     * @return array
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
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @param array $publishers
     * @return array
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
     * Identify which option is used to define message schema: data interface or service method params
     *
     * @param string $schemaId
     * @return string
     */
    protected function identifySchemaType($schemaId)
    {
        return preg_match(Converter::SERVICE_METHOD_NAME_PATTERN, $schemaId)
            ? QueueConfig::TOPIC_SCHEMA_TYPE_METHOD
            : QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT;
    }

    /**
     * Get message schema defined by service method signature.
     *
     * @param string $schemaId
     * @param string $topic
     * @return array
     */
    protected function getSchemaDefinedByMethod($schemaId, $topic)
    {
        preg_match(Converter::SERVICE_METHOD_NAME_PATTERN, $schemaId, $matches);
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
     * Extract consumers configuration.
     *
     * @param \DOMDocument $config
     * @param array $binds
     * @param array $topics
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function extractConsumers(\DOMDocument $config, $binds, $topics)
    {
        $map = [];
        foreach ($binds as $bind) {
            $pattern = $this->xmlValidator->buildWildcardPattern($bind['topic']);
            $extractedTopics = preg_grep($pattern, array_keys($topics));
            foreach ($extractedTopics as $extractedTopic) {
                $map[$bind['queue']][] = $extractedTopic;
            }
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
                    $handlers[$topic][Converter::DEFAULT_HANDLER] = $handler;
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
     * Extract binds configuration.
     *
     * @param \DOMDocument $config
     * @param array $topics
     * @return array
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
}