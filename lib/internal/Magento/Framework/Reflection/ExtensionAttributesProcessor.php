<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Phrase;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Zend\Code\Reflection\MethodReflection;

/**
 * Processes extension attributes and produces a PHP array for the data.
 */
class ExtensionAttributesProcessor
{
    const IS_METHOD_PREFIX = 'is';
    const HAS_METHOD_PREFIX = 'has';
    const GETTER_PREFIX = 'get';

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    public function buildOutputDataArray(ExtensionAttributesInterface $dataObject, $dataObjectType)
    {
        $methods = $this->dataObjectProcessor->getMethodsMap($dataObjectType);
        $outputData = [];

        /** @var MethodReflection $method */
        foreach ($methods as $methodName => $methodReflectionData) {
            // any method with parameter(s) gets ignored because we do not know the type and value of
            // the parameter(s), so we are not able to process
            if ($methodReflectionData['parameterCount'] > 0) {
                continue;
            }
            $returnType = $methodReflectionData['type'];
            if (substr($methodName, 0, 2) === self::IS_METHOD_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 2));
                $outputData[$key] = $this->dataObjectProcessor->castValueToType($value, $returnType);
            } elseif (substr($methodName, 0, 3) === self::HAS_METHOD_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                $outputData[$key] = $this->dataObjectProcessor->castValueToType($value, $returnType);
            } elseif (substr($methodName, 0, 3) === self::GETTER_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                if (is_object($value) && !($value instanceof Phrase)) {
                    $value = $this->dataObjectProcessor->buildOutputDataArray($value, $returnType);
                } elseif (is_array($value)) {
                    $valueResult = [];
                    $arrayElementType = substr($returnType, 0, -2);
                    foreach ($value as $singleValue) {
                        if (is_object($singleValue) && !($singleValue instanceof Phrase)) {
                            $singleValue = $this->dataObjectProcessor->buildOutputDataArray(
                                $singleValue,
                                $arrayElementType
                            );
                        }
                        $valueResult[] = $this->dataObjectProcessor->castValueToType($singleValue, $arrayElementType);
                    }
                    $value = $valueResult;
                }
                $outputData[$key] = $this->dataObjectProcessor->castValueToType($value, $returnType);
            }
        }

        return $outputData;
    }
}
