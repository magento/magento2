<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Phrase;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;

/**
 * Data object processor for array serialization using class reflection
 */
class DataObjectProcessor
{
    /**
     * @var MethodsMap
     */
    private $methodsMapProcessor;

    /**
     * @var TypeCaster
     */
    private $typeCaster;

    /**
     * @var FieldNamer
     */
    private $fieldNamer;

    /**
     * @var ExtensionAttributesProcessor
     */
    private $extensionAttributesProcessor;

    /**
     * @var CustomAttributesProcessor
     */
    private $customAttributesProcessor;

    /**
     * @param MethodsMap $methodsMapProcessor
     * @param TypeCaster $typeCaster
     * @param FieldNamer $fieldNamer
     * @param CustomAttributesProcessor $customAttributesProcessor
     * @param ExtensionAttributesProcessor $extensionAttributesProcessor
     */
    public function __construct(
        MethodsMap $methodsMapProcessor,
        TypeCaster $typeCaster,
        FieldNamer $fieldNamer,
        CustomAttributesProcessor $customAttributesProcessor,
        ExtensionAttributesProcessor $extensionAttributesProcessor
    ) {
        $this->methodsMapProcessor = $methodsMapProcessor;
        $this->typeCaster = $typeCaster;
        $this->fieldNamer = $fieldNamer;
        $this->extensionAttributesProcessor = $extensionAttributesProcessor;
        $this->customAttributesProcessor = $customAttributesProcessor;
    }

    /**
     * Use class reflection on given data interface to build output data array
     *
     * @param mixed $dataObject
     * @param string $dataObjectType
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function buildOutputDataArray($dataObject, $dataObjectType)
    {
        $methods = $this->methodsMapProcessor->getMethodsMap($dataObjectType);
        $outputData = [];

        /** @var MethodReflection $method */
        foreach (array_keys($methods) as $methodName) {
            if (!$this->methodsMapProcessor->isMethodValidForDataField($dataObjectType, $methodName)) {
                continue;
            }

            $value = $dataObject->{$methodName}();
            $isMethodReturnValueRequired = $this->methodsMapProcessor->isMethodReturnValueRequired(
                $dataObjectType,
                $methodName
            );
            if ($value === null && !$isMethodReturnValueRequired) {
                continue;
            }

            $returnType = $this->methodsMapProcessor->getMethodReturnType($dataObjectType, $methodName);
            $key = $this->fieldNamer->getFieldNameForMethodName($methodName);
            if ($key === CustomAttributesDataInterface::CUSTOM_ATTRIBUTES && $value === []) {
                continue;
            }

            if ($key === CustomAttributesDataInterface::CUSTOM_ATTRIBUTES) {
                $value = $this->customAttributesProcessor->buildOutputDataArray($dataObject, $dataObjectType);
            } elseif ($key === "extension_attributes") {
                $value = $this->extensionAttributesProcessor->buildOutputDataArray($value, $returnType);
            } else {
                if (is_object($value) && !($value instanceof Phrase)) {
                    $value = $this->buildOutputDataArray($value, $returnType);
                } elseif (is_array($value)) {
                    $valueResult = [];
                    $arrayElementType = substr($returnType, 0, -2);
                    foreach ($value as $singleValue) {
                        if (is_object($singleValue) && !($singleValue instanceof Phrase)) {
                            $singleValue = $this->buildOutputDataArray($singleValue, $arrayElementType);
                        }
                        $valueResult[] = $this->typeCaster->castValueToType($singleValue, $arrayElementType);
                    }
                    $value = $valueResult;
                } else {
                    $value = $this->typeCaster->castValueToType($value, $returnType);
                }
            }

            $outputData[$key] = $value;
        }
        return $outputData;
    }
}
