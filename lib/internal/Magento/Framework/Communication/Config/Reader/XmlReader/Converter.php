<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader\XmlReader;

use Magento\Framework\Communication\ConfigInterface as Config;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Communication\Config\Reader\XmlReader\Validator;

/**
 * Converts Communication config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const SERVICE_METHOD_NAME_PATTERN = '/^([a-zA-Z\\\\]+)::([a-zA-Z]+)$/';

    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var Validator
     */
    private $xmlValidator;

    /**
     * Initialize dependencies
     *
     * @param MethodsMap $methodsMap
     * @param BooleanUtils $booleanUtils
     * @param Validator $xmlValidator
     */
    public function __construct(
        MethodsMap $methodsMap,
        BooleanUtils $booleanUtils,
        Validator $xmlValidator
    ) {
        $this->methodsMap = $methodsMap;
        $this->booleanUtils = $booleanUtils;
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
     */
    protected function extractTopics($config)
    {
        $output = [];
        /** @var $topicNode \DOMNode */
        foreach ($config->getElementsByTagName('topic') as $topicNode) {
            $topicAttributes = $topicNode->attributes;
            $topicName = $topicAttributes->getNamedItem('name')->nodeValue;

            $requestResponseSchema = $this->extractSchemaDefinedByServiceMethod($topicNode);
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
            if ($requestResponseSchema) {
                $output[$topicName] = [
                    Config::TOPIC_NAME => $topicName,
                    Config::TOPIC_IS_SYNCHRONOUS => true,
                    Config::TOPIC_REQUEST => $requestResponseSchema[Config::SCHEMA_METHOD_PARAMS],
                    Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_METHOD,
                    Config::TOPIC_RESPONSE => $requestResponseSchema[Config::SCHEMA_METHOD_RETURN_TYPE],
                    Config::TOPIC_HANDLERS => $handlers
                        ?: ['defaultHandler' => $requestResponseSchema[Config::SCHEMA_METHOD_HANDLER]]
                ];
            } else if ($requestSchema && $responseSchema) {
                $output[$topicName] = [
                    Config::TOPIC_NAME => $topicName,
                    Config::TOPIC_IS_SYNCHRONOUS => true,
                    Config::TOPIC_REQUEST => $requestSchema,
                    Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_CLASS,
                    Config::TOPIC_RESPONSE => $responseSchema,
                    Config::TOPIC_HANDLERS => $handlers
                ];
            } else if ($requestSchema) {
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
                $serviceName = $handlerAttributes->getNamedItem('type')->nodeValue;
                $methodName = $handlerAttributes->getNamedItem('method')->nodeValue;
                $this->xmlValidator->validateResponseHandlersType($serviceName, $methodName, $handlerName, $topicName);
                $handlerNodes[$handlerName] = [
                    Config::HANDLER_TYPE => $serviceName,
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
     * Get message schema defined by service method signature.
     *
     * @param \DOMNode $topicNode
     * @return array
     */
    protected function extractSchemaDefinedByServiceMethod($topicNode)
    {
        $topicAttributes = $topicNode->attributes;
        if (!$topicAttributes->getNamedItem('schema')) {
            return null;
        }
        $topicName = $topicAttributes->getNamedItem('name')->nodeValue;
        list($className, $methodName) = $this->parseServiceMethod(
            $topicAttributes->getNamedItem('schema')->nodeValue,
            $topicName
        );
        $result = [
            Config::SCHEMA_METHOD_PARAMS => [],
            Config::SCHEMA_METHOD_RETURN_TYPE => $this->methodsMap->getMethodReturnType($className, $methodName),
            Config::SCHEMA_METHOD_HANDLER => [Config::HANDLER_TYPE => $className, Config::HANDLER_METHOD => $methodName]
        ];
        $paramsMeta = $this->methodsMap->getMethodParams($className, $methodName);
        foreach ($paramsMeta as $paramPosition => $paramMeta) {
            $result[Config::SCHEMA_METHOD_PARAMS][] = [
                Config::SCHEMA_METHOD_PARAM_NAME => $paramMeta[MethodsMap::METHOD_META_NAME],
                Config::SCHEMA_METHOD_PARAM_POSITION => $paramPosition,
                Config::SCHEMA_METHOD_PARAM_IS_REQUIRED => !$paramMeta[MethodsMap::METHOD_META_HAS_DEFAULT_VALUE],
                Config::SCHEMA_METHOD_PARAM_TYPE => $paramMeta[MethodsMap::METHOD_META_TYPE],
            ];
        }
        return $result;
    }

    /**
     * Parse service method name, also ensure that it exists.
     *
     * @param string $serviceMethod
     * @param string $topicName
     * @return string[] Contains class name and method name, in a call-back compatible format
     */
    protected function parseServiceMethod($serviceMethod, $topicName)
    {
        preg_match(self::SERVICE_METHOD_NAME_PATTERN, $serviceMethod, $matches);
        $className = $matches[1];
        $methodName = $matches[2];
        $this->xmlValidator->validateServiceMethod($serviceMethod, $topicName, $className, $methodName);
        return [$className, $methodName];
    }
}
