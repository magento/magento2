<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader\XmlReader;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Communication\Config\Validator as ConfigValidator;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Reflection\MethodsMap;

/**
 * Communication configuration validator.
 * @since 2.1.0
 */
class Validator extends ConfigValidator
{
    /**
     * @var TypeProcessor
     * @since 2.1.0
     */
    private $typeProcessor;

    /**
     * @var MethodsMap
     * @since 2.1.0
     */
    private $methodsMap;

    /**
     * @var BooleanUtils
     * @since 2.1.0
     */
    private $booleanUtils;

    /**
     * Initialize dependencies
     *
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMap
     * @param BooleanUtils $booleanUtils
     * @since 2.1.0
     */
    public function __construct(
        TypeProcessor $typeProcessor,
        MethodsMap $methodsMap,
        BooleanUtils $booleanUtils
    ) {
        $this->booleanUtils = $booleanUtils;
        $this->typeProcessor = $typeProcessor;
        $this->methodsMap = $methodsMap;
        parent::__construct($typeProcessor, $methodsMap);
    }

    /**
     * Validate service method
     *
     * @param string $serviceMethod
     * @param string $topicName
     * @param string $className
     * @param string $methodName
     * @return void
     * @since 2.1.0
     */
    public function validateServiceMethod($serviceMethod, $topicName, $className, $methodName)
    {
        try {
            $this->methodsMap->getMethodParams($className, $methodName);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Service method specified in the definition of topic "%s" is not available. Given "%s"',
                    $topicName,
                    $serviceMethod
                )
            );
        }
    }

    /**
     * Validate response request
     *
     * @param string $requestResponseSchema
     * @param string $requestSchema
     * @param string $topicName
     * @param string $responseSchema
     * @param array $handlers
     * @return void
     * @since 2.1.0
     */
    public function validateResponseRequest(
        $requestResponseSchema,
        $requestSchema,
        $topicName,
        $responseSchema,
        $handlers
    ) {
        /** Validate schema attributes */
        if (!$requestResponseSchema && !$requestSchema) {
            throw new \LogicException(
                sprintf(
                    'Either "request" or "schema" attribute must be specified for topic "%s"',
                    $topicName
                )
            );
        }
        if (($requestResponseSchema || $responseSchema) && (count($handlers) >= 2)) {
            throw new \LogicException(
                sprintf(
                    'Topic "%s" is configured for synchronous requests, that is why it must have exactly one '
                    . 'response handler declared. The following handlers declared: %s',
                    $topicName,
                    implode(', ', array_keys($handlers))
                )
            );
        }
    }

    /**
     * Validate declaration of the topic
     *
     * @param string $requestResponseSchema
     * @param string $topicName
     * @param string $requestSchema
     * @param string $responseSchema
     * @return void
     * @since 2.1.0
     */
    public function validateDeclarationOfTopic(
        $requestResponseSchema,
        $topicName,
        $requestSchema,
        $responseSchema
    ) {
        if (!$requestResponseSchema && !($requestSchema && $responseSchema) && !$requestSchema) {
            throw new \LogicException(
                sprintf(
                    'Declaration of topic "%s" is invalid. Specify at least "request" or "schema".',
                    $topicName
                )
            );
        }
    }
}
