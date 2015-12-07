<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Validator;

use Magento\Framework\Communication\ConfigInterface;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Communication\Config\Validator;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Reflection\MethodsMap;

/**
 * Communication configuration validator.
 */
class XmlValidator extends Validator
{
    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;


    /**
     * @param BooleanUtils $booleanUtils
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMap
     */
    public function __construct(
        BooleanUtils $booleanUtils,
        TypeProcessor $typeProcessor,
        MethodsMap $methodsMap
    ) {
        $this->booleanUtils = $booleanUtils;
        $this->typeProcessor = $typeProcessor;
        $this->methodsMap = $methodsMap;
        parent::__construct($typeProcessor, $methodsMap);
    }

    /**
     * @param string $serviceMethod
     * @param string $topicName
     * @param string $className
     * @param string $methodName
     * @return void
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
     * @param string $requestResponseSchema
     * @param string $requestSchema
     * @param string $topicName
     * @param string $responseSchema
     * @param array $handlers
     * @return void
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
        if ($responseSchema && !$handlers) {
            throw new \LogicException(
                sprintf(
                    '"handler" element must be declared for topic "%s", because it has "response" declared',
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
     * @param string $requestResponseSchema
     * @param string $topicName
     * @param string $requestSchema
     * @param string $responseSchema
     * @return void
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
