<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection;

use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Phrase;

/**
 * Data object processor for array serialization using class reflection
 *
 * @api
 * @since 100.0.2
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
     * @var array
     */
    private $processors;

    /**
     * @var array[]
     */
    private $excludedMethodsClassMap;

    /**
     * @param MethodsMap $methodsMapProcessor
     * @param TypeCaster $typeCaster
     * @param FieldNamer $fieldNamer
     * @param CustomAttributesProcessor $customAttributesProcessor
     * @param ExtensionAttributesProcessor $extensionAttributesProcessor
     * @param array $processors
     * @param array $excludedMethodsClassMap
     */
    public function __construct(
        MethodsMap $methodsMapProcessor,
        TypeCaster $typeCaster,
        FieldNamer $fieldNamer,
        CustomAttributesProcessor $customAttributesProcessor,
        ExtensionAttributesProcessor $extensionAttributesProcessor,
        array $processors = [],
        array $excludedMethodsClassMap = []
    ) {
        $this->methodsMapProcessor = $methodsMapProcessor;
        $this->typeCaster = $typeCaster;
        $this->fieldNamer = $fieldNamer;
        $this->extensionAttributesProcessor = $extensionAttributesProcessor;
        $this->customAttributesProcessor = $customAttributesProcessor;
        $this->processors = $processors;
        $this->excludedMethodsClassMap = $excludedMethodsClassMap;
    }

    /**
     * Use class reflection on given data interface to build output data array
     *
     * @param mixed $dataObject
     * @param string $dataObjectType
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function buildOutputDataArray($dataObject, $dataObjectType)
    {
        $methods = $this->methodsMapProcessor->getMethodsMap($dataObjectType);
        $outputData = [];

        $excludedMethodsForDataObjectType = $this->excludedMethodsClassMap[$dataObjectType] ?? [];

        foreach (array_keys($methods) as $methodName) {
            if (in_array($methodName, $excludedMethodsForDataObjectType)) {
                continue;
            }

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
                if (empty($value)) {
                    continue;
                }
            } else {
                if (is_object($value) && !($value instanceof Phrase)) {
                    $value = $this->buildOutputDataArray($value, $returnType);
                } elseif (is_array($value)) {
                    $valueResult = [];
                    $arrayElementType = $returnType !== null ? substr($returnType, 0, -2) : '';
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

        $outputData = $this->changeOutputArray($dataObject, $outputData);

        return $outputData;
    }

    /**
     * Change output array if needed.
     *
     * @param mixed $dataObject
     * @param array $outputData
     * @return array
     */
    private function changeOutputArray($dataObject, array $outputData): array
    {
        foreach ($this->processors as $dataObjectClassName => $processor) {
            if ($dataObject instanceof $dataObjectClassName) {
                $outputData = $processor->execute($dataObject, $outputData);
            }
        }

        return $outputData;
    }
}
