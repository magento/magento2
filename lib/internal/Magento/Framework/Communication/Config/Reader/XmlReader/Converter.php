<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader\XmlReader;

use Magento\Framework\Communication\Config\ConfigParser;
use Magento\Framework\Communication\Config\ReflectionGenerator;
use Magento\Framework\Communication\ConfigInterface as Config;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Converts Communication config from \DOMDocument to array
 * @since 2.1.0
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @deprecated
     * @see ConfigParser::parseServiceMethod
     */
    const SERVICE_METHOD_NAME_PATTERN = '/^([a-zA-Z\\\\]+)::([a-zA-Z]+)$/';

    /**
     * @var ReflectionGenerator
     * @since 2.1.0
     */
    private $reflectionGenerator;

    /**
     * @var BooleanUtils
     * @since 2.1.0
     */
    private $booleanUtils;

    /**
     * @var Validator
     * @since 2.1.0
     */
    private $xmlValidator;

    /**
     * @var ConfigParser
     * @since 2.2.0
     */
    private $configParser;

    /**
     * Initialize dependencies
     *
     * @param ReflectionGenerator $reflectionGenerator
     * @param BooleanUtils $booleanUtils
     * @param Validator $xmlValidator
     * @since 2.1.0
     */
    public function __construct(
        ReflectionGenerator $reflectionGenerator,
        BooleanUtils $booleanUtils,
        Validator $xmlValidator
    ) {
        $this->reflectionGenerator = $reflectionGenerator;
        $this->booleanUtils = $booleanUtils;
        $this->xmlValidator = $xmlValidator;
    }

    /**
     * The getter function to get the new ConfigParser dependency.
     *
     * @return \Magento\Framework\Communication\Config\ConfigParser
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getConfigParser()
    {
        if ($this->configParser === null) {
            $this->configParser = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Communication\Config\ConfigParser::class);
        }
        return $this->configParser;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @since 2.1.0
     */
    public function convert($source)
    {
        $topics = $this->extractTopics($source);
        return [
            Config::TOPICS => $topics,
        ];
    }

    /**
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @return array
     * @since 2.1.0
     */
    protected function extractTopics($config)
    {
        $output = [];
        /** @var $topicNode \DOMNode */
        foreach ($config->getElementsByTagName('topic') as $topicNode) {
            $topicAttributes = $topicNode->attributes;
            $topicName = $topicAttributes->getNamedItem('name')->nodeValue;

            $serviceMethod = $this->getServiceMethodBySchema($topicNode);
            $requestResponseSchema = $serviceMethod
                ? $this->reflectionGenerator->extractMethodMetadata(
                    $serviceMethod[ConfigParser::TYPE_NAME],
                    $serviceMethod[ConfigParser::METHOD_NAME]
                )
                : null;
            $requestSchema = $this->extractTopicRequestSchema($topicNode);
            $responseSchema = $this->extractTopicResponseSchema($topicNode);
            $handlers = $this->extractTopicResponseHandlers($topicNode);
            $this->xmlValidator->validateResponseRequest(
                $requestResponseSchema,
                $requestSchema,
                $topicName,
                $responseSchema,
                $handlers
            );
            $this->xmlValidator->validateDeclarationOfTopic(
                $requestResponseSchema,
                $topicName,
                $requestSchema,
                $responseSchema
            );
            if ($serviceMethod) {
                $output[$topicName] = $this->reflectionGenerator->generateTopicConfigForServiceMethod(
                    $topicName,
                    $serviceMethod[ConfigParser::TYPE_NAME],
                    $serviceMethod[ConfigParser::METHOD_NAME],
                    $handlers
                );
            } elseif ($requestSchema && $responseSchema) {
                $output[$topicName] = [
                    Config::TOPIC_NAME => $topicName,
                    Config::TOPIC_IS_SYNCHRONOUS => true,
                    Config::TOPIC_REQUEST => $requestSchema,
                    Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_CLASS,
                    Config::TOPIC_RESPONSE => $responseSchema,
                    Config::TOPIC_HANDLERS => $handlers
                ];
            } elseif ($requestSchema) {
                $output[$topicName] = [
                    Config::TOPIC_NAME => $topicName,
                    Config::TOPIC_IS_SYNCHRONOUS => false,
                    Config::TOPIC_REQUEST => $requestSchema,
                    Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_CLASS,
                    Config::TOPIC_RESPONSE => null,
                    Config::TOPIC_HANDLERS => $handlers
                ];
            }
        }
        return $output;
    }

    /**
     * Extract response handlers.
     *
     * @param \DOMNode $topicNode
     * @return array List of handlers, each contain service name and method name
     * @since 2.1.0
     */
    protected function extractTopicResponseHandlers($topicNode)
    {
        $topicName = $topicNode->attributes->getNamedItem('name')->nodeValue;
        $topicChildNodes = $topicNode->childNodes;
        $handlerNodes = [];
        /** @var \DOMNode $topicChildNode */
        foreach ($topicChildNodes as $topicChildNode) {
            if ($topicChildNode->nodeName === 'handler') {
                $handlerAttributes = $topicChildNode->attributes;
                if ($handlerAttributes->getNamedItem('disabled')
                    && $this->booleanUtils->toBoolean($handlerAttributes->getNamedItem('disabled')->nodeValue)
                ) {
                    continue;
                }
                $handlerName = $handlerAttributes->getNamedItem('name')->nodeValue;
                $serviceType = $handlerAttributes->getNamedItem('type')->nodeValue;
                $methodName = $handlerAttributes->getNamedItem('method')->nodeValue;
                $this->xmlValidator->validateResponseHandlersType($serviceType, $methodName, $handlerName, $topicName);
                $handlerNodes[$handlerName] = [
                    Config::HANDLER_TYPE => $serviceType,
                    Config::HANDLER_METHOD => $methodName
                ];
            }
        }
        return $handlerNodes;
    }

    /**
     * Extract request schema class name.
     *
     * @param \DOMNode $topicNode
     * @return string|null
     * @since 2.1.0
     */
    protected function extractTopicRequestSchema($topicNode)
    {
        $topicAttributes = $topicNode->attributes;
        if (!$topicAttributes->getNamedItem('request')) {
            return null;
        }
        $topicName = $topicAttributes->getNamedItem('name')->nodeValue;
        $requestSchema = $topicAttributes->getNamedItem('request')->nodeValue;
        $this->xmlValidator->validateRequestSchemaType($requestSchema, $topicName);
        return $requestSchema;
    }

    /**
     * Extract response schema class name.
     *
     * @param \DOMNode $topicNode
     * @return string|null
     * @since 2.1.0
     */
    protected function extractTopicResponseSchema($topicNode)
    {
        $topicAttributes = $topicNode->attributes;
        if (!$topicAttributes->getNamedItem('response')) {
            return null;
        }
        $topicName = $topicAttributes->getNamedItem('name')->nodeValue;
        $responseSchema = $topicAttributes->getNamedItem('response')->nodeValue;
        $this->xmlValidator->validateResponseSchemaType($responseSchema, $topicName);
        return $responseSchema;
    }

    /**
     * Get service class and method specified in schema attribute.
     *
     * @param \DOMNode $topicNode
     * @return array|null Contains class name and method name
     * @since 2.1.0
     */
    protected function getServiceMethodBySchema($topicNode)
    {
        $topicAttributes = $topicNode->attributes;
        if (!$topicAttributes->getNamedItem('schema')) {
            return null;
        }
        $topicName = $topicAttributes->getNamedItem('name')->nodeValue;
        $serviceMethod = $topicAttributes->getNamedItem('schema')->nodeValue;
        return $this->parseServiceMethod($serviceMethod, $topicName);
    }

    /**
     * Parse service method name, also ensure that it exists.
     *
     * @param string $serviceMethod
     * @param string $topicName
     * @return array Contains class name and method name
     * @since 2.1.0
     */
    protected function parseServiceMethod($serviceMethod, $topicName)
    {
        $parsedServiceMethod = $this->getConfigParser()->parseServiceMethod($serviceMethod);
        $this->xmlValidator->validateServiceMethod(
            $serviceMethod,
            $topicName,
            $parsedServiceMethod[ConfigParser::TYPE_NAME],
            $parsedServiceMethod[ConfigParser::METHOD_NAME]
        );
        return $parsedServiceMethod;
    }
}
