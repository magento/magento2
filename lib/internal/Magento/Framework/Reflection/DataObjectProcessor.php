<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Reflection;

use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Phrase;

/**
 * Data object processor for array serialization using class reflection
 *
 * @api
 * @since 100.0.2
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
        $methodFieldNames = [];

        $excludedMethodsForDataObjectType = $this->excludedMethodsClassMap[$dataObjectType] ?? [];

        foreach (array_keys($methods) as $methodName) {
            if (!$this->methodsMapProcessor->isMethodValidForDataField($dataObjectType, $methodName)) {
                continue;
            }

            $key = $this->fieldNamer->getFieldNameForMethodName($methodName);
            if ($key === null) {
                continue;
            }

            $methodFieldNames[$key] = true;

            if (in_array($methodName, $excludedMethodsForDataObjectType)) {
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
            if ($key === CustomAttributesDataInterface::CUSTOM_ATTRIBUTES && $value === []) {
                continue;
            }

            if ($key === CustomAttributesDataInterface::CUSTOM_ATTRIBUTES) {
                if (!($dataObject instanceof CustomAttributesDataInterface)) {
                    continue;
                }
                $value = $this->customAttributesProcessor->buildOutputDataArray($dataObject, $dataObjectType);
            } elseif ($key === "extension_attributes") {
                if (!($value instanceof \Magento\Framework\Api\ExtensionAttributesInterface)) {
                    continue;
                }
                $value = $this->extensionAttributesProcessor->buildOutputDataArray($value, $returnType);
                if (empty($value)) {
                    continue;
                }
            } else {
                $value = $this->processValue($value, $returnType);
            }

            $outputData[$key] = $value;
        }

        $outputData = $this->addPublicProperties($dataObject, $dataObjectType, $outputData, $methodFieldNames);

        $outputData = $this->changeOutputArray($dataObject, $outputData);

        return $outputData;
    }

    /**
     * Process value based on its type and return type
     *
     * @param mixed $value
     * @param string $returnType
     * @return mixed
     */
    private function processValue($value, $returnType)
    {
        if (is_object($value) && !($value instanceof Phrase)) {
            return $this->buildOutputDataArray($value, $returnType);
        }
        if (is_array($value)) {
            if ($returnType === TypeProcessor::UNSTRUCTURED_ARRAY) {
                return $value;
            }
            $valueResult = [];
            $arrayElementType = $returnType !== null ? substr($returnType, 0, -2) : '';
            foreach ($value as $singleValue) {
                if (is_object($singleValue) && !($singleValue instanceof Phrase)) {
                    $singleValue = $this->buildOutputDataArray($singleValue, $arrayElementType);
                }
                $valueResult[] = $this->typeCaster->castValueToType($singleValue, $arrayElementType);
            }
            return $valueResult;
        }
        return $this->typeCaster->castValueToType($value, $returnType);
    }

    /**
     * Append public properties that do not have a matching getter.
     *
     * @param object $dataObject
     * @param string $dataObjectType
     * @param array $outputData
     * @param array $methodFieldNames
     * @return array
     */
    private function addPublicProperties($dataObject, $dataObjectType, array $outputData, array $methodFieldNames): array
    {
        $reflection = new \ReflectionObject($dataObject);
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            if (!$property->isInitialized($dataObject)) {
                continue;
            }

            $key = SimpleDataObjectConverter::camelCaseToSnakeCase($property->getName());
            if (isset($methodFieldNames[$key]) || array_key_exists($key, $outputData)) {
                continue;
            }

            $value = $property->getValue($dataObject);
            $propertyMetadata = $this->getPropertyMetadata($property);
            $returnType = $this->resolvePropertyReturnType($propertyMetadata['type'], $value, $property);

            if ($value === null && !$propertyMetadata['isRequired']) {
                continue;
            }

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
                $value = $this->processValue($value, $returnType);
            }

            $outputData[$key] = $value;
        }

        return $outputData;
    }

    /**
     * Resolve property type metadata for serialization.
     *
     * @param \ReflectionProperty $property
     * @return array{type: string|null, isRequired: bool}
     */
    private function getPropertyMetadata(\ReflectionProperty $property): array
    {
        $type = $property->getType();
        if ($type === null) {
            return ['type' => null, 'isRequired' => false];
        }

        $allowsNull = $type->allowsNull();
        $typeName = null;

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($unionType->getName() !== 'null') {
                    $typeName = $unionType->getName();
                    break;
                }
            }
        } elseif ($type instanceof \ReflectionIntersectionType) {
            $types = $type->getTypes();
            $typeName = $types ? $types[0]->getName() : null;
        } else {
            $typeName = $type->getName();
        }

        $typeName = $this->normalizePropertyType($property, $typeName);

        return [
            'type' => $typeName,
            'isRequired' => !$allowsNull,
        ];
    }

    /**
     * Normalize property type names for casting.
     *
     * @param \ReflectionProperty $property
     * @param string|null $typeName
     * @return string|null
     */
    private function normalizePropertyType(\ReflectionProperty $property, ?string $typeName): ?string
    {
        if ($typeName === null) {
            return null;
        }

        if ($typeName === 'self' || $typeName === 'static') {
            return $property->getDeclaringClass()->getName();
        }

        if ($typeName === 'parent') {
            $parent = $property->getDeclaringClass()->getParentClass();
            return $parent ? $parent->getName() : null;
        }

        if ($typeName === 'array' || $typeName === 'iterable' || $typeName === 'mixed') {
            return TypeProcessor::UNSTRUCTURED_ARRAY;
        }

        return $typeName;
    }

    /**
     * Ensure object values use a compatible return type.
     *
     * @param string|null $returnType
     * @param mixed $value
     * @param \ReflectionProperty $property
     * @return string|null
     */
    private function resolvePropertyReturnType(?string $returnType, $value, \ReflectionProperty $property): ?string
    {
        if (is_array($value) && $returnType === null) {
            return TypeProcessor::UNSTRUCTURED_ARRAY;
        }

        if (is_object($value) && !($value instanceof Phrase)) {
            if ($returnType === null || !$this->isObjectType($returnType)) {
                return get_class($value);
            }

            if ($returnType === 'self' || $returnType === 'static') {
                return $property->getDeclaringClass()->getName();
            }

            if ($returnType === 'parent') {
                $parent = $property->getDeclaringClass()->getParentClass();
                return $parent ? $parent->getName() : get_class($value);
            }
        }

        return $returnType;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isObjectType(string $type): bool
    {
        return interface_exists($type) || class_exists($type);
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
