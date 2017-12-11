<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\ServiceContract;

use Magento\Framework\GraphQl\Type\Definition\InterfaceType;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Webapi\Model\ServiceMetadata;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;

/**
 * Translate web API service contract layer from array-style schema to GraphQL types
 */
class TypeGenerator
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var ServiceMetadata
     */
    private $metadata;

    /**
     * @var SimpleDataObjectConverter
     */
    private $simpleDataConverter;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeProcessor $typeProcessor
     * @param ServiceMetadata $metadata
     * @param SimpleDataObjectConverter $simpleDataConverter
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        TypeProcessor $typeProcessor,
        ServiceMetadata $metadata,
        SimpleDataObjectConverter $simpleDataConverter,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeProcessor = $typeProcessor;
        $metadata->getServicesConfig();
        $this->metadata = $metadata;
        $this->simpleDataConverter = $simpleDataConverter;
        $this->typeFactory = $typeFactory;
    }

    /**
     * Convert service contract data schema array to ObjectType
     *
     * Format of input array should be [
     *     'NameOfType' => [
     *         'scalar_field_name' => 'scalarTypeString',
     *         'complex_type_name' => [...]
     *     ]
     * ]
     *
     * @param string $typeName
     * @param array $schema
     * @return TypeInterface|\GraphQL\Type\Definition\Type
     */
    public function generate(
        string $typeName,
        array $schema
    ) {
        if ($this->typePool->isTypeRegistered($typeName)) {
            return $this->typePool->getComplexType($typeName);
        }

        return $this->generateNestedTypes($typeName, $schema);
    }

    /**
     * Get service contract type data from associative service contract name
     *
     * @param string $type
     * @return array
     * @throws \LogicException
     */
    public function getTypeData(string $type)
    {
        $typesData = $this->typeProcessor->getTypeData($type);

        $result = [];
        if (isset($typesData['parameters'])) {
            foreach ($typesData['parameters'] as $attributeCode => $parameter) {
                $snakeAttributeCode = $this->simpleDataConverter->camelCaseToSnakeCase(
                    $attributeCode
                );

                if ($snakeAttributeCode == 'custom_attributes') {
                    continue;
                }

                if ($this->typeProcessor->isTypeAny($parameter['type'])) {
                    throw new \LogicException("Mixed type detected");
                } elseif ($this->typeProcessor->isArrayType($parameter['type'])) {
                    $arrayItemType = $this->typeProcessor->getArrayItemType($parameter['type']);
                    if ($this->typeProcessor->isTypeSimple($arrayItemType)) {
                        $result[$snakeAttributeCode][] = $arrayItemType;
                    } else {
                        $result[$snakeAttributeCode][] = $this->getTypeData($arrayItemType);
                    }
                } elseif ($this->typeProcessor->isTypeSimple($parameter['type'])) {
                    $result[$snakeAttributeCode] = $parameter['type'];
                } else {
                    if ($snakeAttributeCode == 'extension_attributes') {
                        $extensionAttributes = $this->getTypeData($parameter['type']);
                        $result = array_merge($result, $extensionAttributes);
                    } else {
                        $result[$snakeAttributeCode] = $this->getTypeData($parameter['type']);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Iterate through array-style schema gathered from Service Contract metadata to translate to GraphQL types
     *
     * @param string $typeName
     * @param array $schema
     * @param bool $skipField
     * @param string|null $parentField
     * @return InterfaceType|\GraphQL\Type\Definition\Type|mixed|null
     */
    private function generateNestedTypes(
        string $typeName,
        array $schema,
        bool $skipField = false,
        string $parentField = null
    ) {
        if (!$skipField) {
            $generatedType = ['name' => $typeName];
        }
        $generatedFields = [];
        $callableFields = [];

        foreach ($schema as $field => $type) {
            if (is_array($type)) {
                $isAssociativeArray = $this->isAssociativeArray($type);
                if ($isAssociativeArray) {
                    $parentField = ucfirst($this->underscoreToCamelCase($parentField));
                    $subTypeName = $parentField ?: ucfirst($this->underscoreToCamelCase($field));
                    $generatedTypeName = $typeName . $subTypeName;
                    if ($this->typePool->isTypeRegistered($generatedTypeName)) {
                        return $this->typePool->getComplexType($generatedTypeName);
                    }
                    $generated = $this->generateNestedTypes($generatedTypeName, $type);
                    $this->typePool->registerType($generated);
                    $generatedFields[$field] = ['type' => $generated];
                } else {
                    $convertedField = $this->generateNestedTypes(
                        $typeName,
                        $type,
                        !$isAssociativeArray,
                        $field
                    );
                    $generatedFields[$field] = ['type' => $this->typeFactory->createList($convertedField)];
                }
            } else {
                if (strpos($typeName, $type) !== false) {
                    $callableFields[$field] = $type;
                    continue;
                }
                $generated = $this->typePool->getType(ucfirst($type));
                $generatedFields[$field] = ['type' => $generated];
            }
            if ($skipField) {
                return $generated;
            }
        }
        $generatedType['fields'] = $this->processGeneratedFields($generatedFields, $callableFields);

        return $this->typeFactory->createObject($generatedType);
    }

    /**
     * Check if array is associative
     *
     * @param $arr
     * @return bool
     */
    private function isAssociativeArray($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Transform string from underscore to camelcase
     *
     * @param string $value
     * @return string
     */
    private function underscoreToCamelCase($value)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
    }

    /**
     * @param array $generatedFields
     * @param string[] $callableFields
     * @return array|\Closure
     */
    private function processGeneratedFields($generatedFields, $callableFields)
    {
        if (empty($callableFields)) {
            return $generatedFields;
        } else {
            return function () use ($generatedFields, $callableFields) {
                foreach ($callableFields as $name => $type) {
                    $callableFields[$name] = $this->typePool->getType($type);
                }

                return array_merge($callableFields, $generatedFields);
            };
        }
    }
}
