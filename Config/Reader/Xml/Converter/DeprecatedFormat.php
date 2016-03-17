<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;

use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Config\Validator;
use Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;

class DeprecatedFormat implements \Magento\Framework\Config\ConverterInterface
{
    const SERVICE_METHOD_NAME_PATTERN = '/^([a-zA-Z\\\\]+)::([a-zA-Z]+)$/';
    const DEFAULT_HANDLER = 'defaultHandler';

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
            ConfigInterface::PUBLISHERS => $publishers,
            ConfigInterface::TOPICS => $topics,
            ConfigInterface::CONSUMERS => $consumers,
            ConfigInterface::BINDS => $binds,
            ConfigInterface::EXCHANGE_TOPIC_TO_QUEUES_MAP => $this->buildExchangeTopicToQueuesMap($binds, $topics),
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
                ConfigInterface::PUBLISHER_NAME => $publisherName,
                ConfigInterface::PUBLISHER_CONNECTION =>
                    $publisherNode->attributes->getNamedItem('connection')->nodeValue,
                ConfigInterface::PUBLISHER_EXCHANGE => $publisherNode->attributes->getNamedItem('exchange')->nodeValue
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
            $schemaValue = ($schemaType == ConfigInterface::TOPIC_SCHEMA_TYPE_METHOD)
                ? $this->getSchemaDefinedByMethod($schemaId, $topicName)
                : $schemaId;
            $publisherName = $topicNode->attributes->getNamedItem('publisher')->nodeValue;
            $this->xmlValidator->validateTopicPublisher(array_keys($publishers), $publisherName, $topicName);

            $output[$topicName] = [
                ConfigInterface::TOPIC_NAME => $topicName,
                ConfigInterface::TOPIC_SCHEMA => [
                    ConfigInterface::TOPIC_SCHEMA_TYPE => $schemaType,
                    ConfigInterface::TOPIC_SCHEMA_VALUE => $schemaValue
                ],
                ConfigInterface::TOPIC_RESPONSE_SCHEMA => [
                    ConfigInterface::TOPIC_SCHEMA_TYPE => null,
                    ConfigInterface::TOPIC_SCHEMA_VALUE => null
                ],
                'is_synchronous' => false,
                ConfigInterface::TOPIC_PUBLISHER => $publisherName
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
        return preg_match(self::SERVICE_METHOD_NAME_PATTERN, $schemaId)
            ? ConfigInterface::TOPIC_SCHEMA_TYPE_METHOD
            : ConfigInterface::TOPIC_SCHEMA_TYPE_OBJECT;
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
        preg_match(self::SERVICE_METHOD_NAME_PATTERN, $schemaId, $matches);
        $serviceClass = $matches[1];
        $serviceMethod = $matches[2];
        $this->xmlValidator->validateSchemaMethodType($serviceClass, $serviceMethod, $topic);
        $result = [];
        $paramsMeta = $this->methodsMap->getMethodParams($serviceClass, $serviceMethod);
        foreach ($paramsMeta as $paramPosition => $paramMeta) {
            $result[] = [
                ConfigInterface::SCHEMA_METHOD_PARAM_NAME => $paramMeta[MethodsMap::METHOD_META_NAME],
                ConfigInterface::SCHEMA_METHOD_PARAM_POSITION => $paramPosition,
                ConfigInterface::SCHEMA_METHOD_PARAM_IS_REQUIRED =>
                    !$paramMeta[MethodsMap::METHOD_META_HAS_DEFAULT_VALUE],
                ConfigInterface::SCHEMA_METHOD_PARAM_TYPE => $paramMeta[MethodsMap::METHOD_META_TYPE],
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
                ConfigInterface::CONSUMER_CLASS => $consumerNode->attributes->getNamedItem('class')->nodeValue,
                ConfigInterface::CONSUMER_METHOD => $consumerNode->attributes->getNamedItem('method')->nodeValue,
            ];
            $this->xmlValidator->validateHandlerType(
                $handler[ConfigInterface::CONSUMER_CLASS],
                $handler[ConfigInterface::CONSUMER_METHOD],
                $consumerName
            );
            $handlers = [];
            if (isset($map[$queueName])) {
                foreach ($map[$queueName] as $topic) {
                    $handlers[$topic][self::DEFAULT_HANDLER] = $handler;
                }
            }
            $output[$consumerName] = [
                ConfigInterface::CONSUMER_NAME => $consumerName,
                ConfigInterface::CONSUMER_QUEUE => $queueName,
                ConfigInterface::CONSUMER_CONNECTION => $connections ? $connections->nodeValue : null,
                ConfigInterface::CONSUMER_TYPE => ConfigInterface::CONSUMER_TYPE_ASYNC,
                ConfigInterface::CONSUMER_HANDLERS => $handlers,
                ConfigInterface::CONSUMER_MAX_MESSAGES => $maxMessages ? $maxMessages->nodeValue : null,
                ConfigInterface::CONSUMER_INSTANCE_TYPE =>
                    $consumerInstanceType ? $consumerInstanceType->nodeValue : null,
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
                ConfigInterface::BIND_QUEUE => $queueName,
                ConfigInterface::BIND_EXCHANGE => $exchangeName,
                ConfigInterface::BIND_TOPIC => $topicName,
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
            $key = $bind[ConfigInterface::BIND_EXCHANGE] . '--' . $bind[ConfigInterface::BIND_TOPIC];
            if (strpos($key, '*') !== false || strpos($key, '#') !== false) {
                $wildcardKeys[] = $key;
            }
            $output[$key][] = $bind[ConfigInterface::BIND_QUEUE];
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
