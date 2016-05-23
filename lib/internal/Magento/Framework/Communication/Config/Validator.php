<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Reflection\MethodsMap;

/**
 * Communication configuration validator.
 */
class Validator
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
     * Initialize dependencies.
     *
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMap
     */
    public function __construct(
        TypeProcessor $typeProcessor,
        MethodsMap $methodsMap
    ) {
        $this->typeProcessor = $typeProcessor;
        $this->methodsMap = $methodsMap;
    }

    /**
     * @param string $responseSchema
     * @param string $topicName
     * @return void
     */
    public function validateResponseSchemaType($responseSchema, $topicName)
    {
        try {
            $this->validateType($responseSchema);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Response schema definition for topic "%s" should reference existing type or service class. '
                    . 'Given "%s"',
                    $topicName,
                    $responseSchema
                )
            );
        }
    }

    /**
     * @param string $requestSchema
     * @param string $topicName
     * @return void
     */
    public function validateRequestSchemaType($requestSchema, $topicName)
    {
        try {
            $this->validateType($requestSchema);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Request schema definition for topic "%s" should reference existing service class. '
                    . 'Given "%s"',
                    $topicName,
                    $requestSchema
                )
            );
        }
    }

    /**
     * @param string $serviceName
     * @param string $methodName
     * @param string $handlerName
     * @param string $topicName
     * @return void
     */
    public function validateResponseHandlersType($serviceName, $methodName, $handlerName, $topicName)
    {
        try {
            $this->methodsMap->getMethodParams($serviceName, $methodName);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Service method specified in the definition of handler "%s" for topic "%s"'
                    . ' is not available. Given "%s"',
                    $handlerName,
                    $topicName,
                    $serviceName . '::' . $methodName
                )
            );
        }
    }

    /**
     * Ensure that specified type is either a simple type or a valid service data type.
     *
     * @param string $typeName
     * @return $this
     * @throws \Exception In case when type is invalid
     */
    protected function validateType($typeName)
    {
        if ($this->typeProcessor->isTypeSimple($typeName)) {
            return $this;
        }
        if ($this->typeProcessor->isArrayType($typeName)) {
            $arrayItemType = $this->typeProcessor->getArrayItemType($typeName);
            $this->methodsMap->getMethodsMap($arrayItemType);
        } else {
            $this->methodsMap->getMethodsMap($typeName);
        }
        return $this;
    }
}
