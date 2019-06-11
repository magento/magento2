<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto;

use Exception;
use LogicException;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttribute\InjectorProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\NameFinder;
use Magento\Framework\Reflection\TypeProcessor;
use ReflectionException;
use ReflectionMethod;
use Zend\Code\Reflection\ClassReflection;

/**
 * DTO processor. Supports both mutable and immutable DTO.
 *
 * @api
 */
class DtoProcessor
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var NameFinder
     */
    private $nameFinder;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @var JoinProcessor
     */
    private $joinProcessor;

    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var InjectorProcessor
     */
    private $injectorProcessor;

    /**
     * @var DtoConfig
     */
    private $dtoConfig;

    /**
     * @param DtoConfig $dtoConfig
     * @param ObjectFactory $objectFactory
     * @param TypeProcessor $typeProcessor
     * @param ConfigInterface $config
     * @param NameFinder $nameFinder
     * @param JoinProcessor $joinProcessor
     * @param InjectorProcessor $injectorProcessor
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param MethodsMap $methodsMap
     */
    public function __construct(
        DtoConfig $dtoConfig,
        ObjectFactory $objectFactory,
        TypeProcessor $typeProcessor,
        ConfigInterface $config,
        NameFinder $nameFinder,
        JoinProcessor $joinProcessor,
        InjectorProcessor $injectorProcessor,
        ExtensionAttributesFactory $extensionAttributesFactory,
        MethodsMap $methodsMap
    ) {
        $this->config = $config;
        $this->typeProcessor = $typeProcessor;
        $this->nameFinder = $nameFinder;
        $this->objectFactory = $objectFactory;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->joinProcessor = $joinProcessor;
        $this->methodsMap = $methodsMap;
        $this->injectorProcessor = $injectorProcessor;
        $this->dtoConfig = $dtoConfig;
    }

    /**
     * Get real class name (if preferenced)
     *
     * @param string $className
     * @return string
     */
    private function getRealClassName(string $className): string
    {
        $preferenceClass = $this->config->getPreference($className);
        return $preferenceClass ?: $className;
    }


    /**
     * @param $value
     * @param string $type
     * @return mixed
     */
    private function castType($value, string $type)
    {
        if (is_array($value) || !$this->typeProcessor->isTypeSimple($type)) {
            return $value;
        }

        if ($type === 'int' || $type === 'integer') {
            return (int) $value;
        }

        if ($type === 'string') {
            return (string) $value;
        }

        if ($type === 'bool' || $type === 'boolean' || $type === 'true' || $type === 'false') {
            return (bool) $value;
        }

        if ($type === 'float') {
            return (float) $value;
        }

        if ($type === 'double') {
            return (double) $value;
        }

        return $value;
    }

    /**
     * @param $value
     * @param string $type
     * @return array|object
     * @throws ReflectionException
     */
    private function createObjectByType($value, string $type)
    {
        if (is_object($value) || ($type === 'array')) {
            return $value;
        }

        if ($this->typeProcessor->isArrayType($type)) {
            $res = [];
            foreach ($value as $k => $subValue) {
                $itemType = $this->typeProcessor->getArrayItemType($type);
                $res[$k] = $this->createObjectByType($subValue, $itemType);
            }

            return $res;
        }

        if ($this->typeProcessor->isTypeSimple($type)) {
            return $this->castType($value, $type);
        }

        return $this->createFromArray($value, $type);
    }

    /**
     * Return the property type by its getter name
     * @param ClassReflection $classReflection
     * @param string $propertyName
     * @return string
     */
    private function getPropertyTypeFromGetterMethod(ClassReflection $classReflection, string $propertyName): string
    {
        $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
        try {
            $methodName = $this->nameFinder->getGetterMethodName($classReflection, $camelCaseProperty);
        } catch (Exception $e) {
            return '';
        }

        $methodReflection = $classReflection->getMethod($methodName);
        if ($methodReflection->isPublic()) {
            $paramType = (string) $this->typeProcessor->getGetterReturnType($methodReflection)['type'];
            return $this->typeProcessor->resolveFullyQualifiedClassName($classReflection, $paramType);
        }

        return '';
    }

    /**
     * Inject extension attributes from an array definition
     *
     * @param string $type
     * @param array $data
     * @return array
     * @throws ReflectionException
     */
    private function injectExtensionAttributesByArray(string $type, array $data): array
    {
        $extensionAttributesType = $this->getPropertyTypeFromGetterMethod(
            new ClassReflection($type),
            ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY
        );

        $extensionAttributes = $data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] ?? [];
        $extensionAttributes = array_replace(
            $extensionAttributes,
            $this->injectorProcessor->execute($type, $extensionAttributes)
        );

        foreach ($extensionAttributes as $attributeName => $attributeValue) {
            if (!is_array($attributeValue) && !is_object($attributeValue)) {
                continue;
            }

            $methodReturnType = $this->getPropertyTypeFromGetterMethod(
                new ClassReflection($extensionAttributesType),
                $attributeName
            );
            $attributeType = $this->typeProcessor->isArrayType($methodReturnType)
                ? $this->typeProcessor->getArrayItemType($methodReturnType)
                : $methodReturnType;

            $extensionAttributes[$attributeName] = $this->createFromArray($attributeValue, $attributeType);
        }

        $data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] = $this->extensionAttributesFactory->create(
            $type,
            ['data' => $extensionAttributes]
        );

        return $data;
    }

    /**
     * Populate data object using data in array format.
     *
     * @param array $data
     * @param string $type
     * @return object
     * @throws ReflectionException
     */
    public function createFromArray(array $data, string $type)
    {
        if (!$this->dtoConfig->isDto($type)) {
            throw new LogicException($type . ' is not configured as DTO');
        }

        // Normalize snake case properties
        foreach ($data as $k => $v) {
            $snakeCaseKey = SimpleDataObjectConverter::camelCaseToSnakeCase($k);
            if ($snakeCaseKey !== $k) {
                $data[$snakeCaseKey] = $v;
                unset($data[$k]);
            }
        }

        if (!isset($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]) ||
            !is_object($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])
        ) {
            $data = $this->injectExtensionAttributesByArray($type, $data);
        }

        $data = $this->joinProcessor->extractExtensionAttributes($type, $data);

        $dtoConfig = $this->dtoConfig->get($type);

        $constructorParams = [];
        foreach ($data as $k => $v) {
            $propertyName = SimpleDataObjectConverter::snakeCaseToCamelCase($k);

            if (!isset($dtoConfig['properties'][$propertyName])) {
                continue;
            }

            $constructorParams[$propertyName] =
                $this->createObjectByType($v, $dtoConfig['properties'][$propertyName]['type']);
        }

        return $this->objectFactory->create($type, $constructorParams);
    }

    /**
     * Create a new DTO with updated information from array
     *
     * @param $sourceObject
     * @param array $data
     * @return object
     * @throws ReflectionException
     */
    public function createUpdatedObjectFromArray(
        $sourceObject,
        array $data
    ) {
        $sourceObjectData = $this->getObjectData($sourceObject);
        $data = array_replace($sourceObjectData, $data);

        return $this->createFromArray($data, get_class($sourceObject));
    }

    /**
     * @param $value
     * @return mixed
     * @throws ReflectionException
     */
    private function explodeObjectValue($value)
    {
        if (is_object($value)) {
            return $this->getObjectData($value);
        }

        if (is_array($value)) {
            $res = [];
            foreach ($value as $subValue) {
                $res[] = $this->explodeObjectValue($subValue);
            }

            return $res;
        }

        return $value;
    }

    /**
     * Merge data into object data
     *
     * @param $sourceObject
     * @return array
     * @throws ReflectionException
     */
    public function getObjectData($sourceObject): array
    {
        $objectType = get_class($sourceObject);

        if ($sourceObject === null || !is_object($sourceObject)) {
            return [];
        }

        $sourceObjectMethods = $this->methodsMap->getMethodsMap($objectType);

        $res = [];
        foreach ($sourceObjectMethods as $sourceObjectMethod => $sourceObjectMethodInfo) {
            if (!$this->methodsMap->isMethodValidForDataField($objectType, $sourceObjectMethod)) {
                continue;
            }

            if (preg_match('/^(is|get)([A-Z]\w*)$/', $sourceObjectMethod, $matches)) {
                $propertyName = SimpleDataObjectConverter::camelCaseToSnakeCase($matches[2]);
                $methodName = $matches[0];

                $methodReflection = new ReflectionMethod($sourceObject, $methodName);
                if ($methodReflection->getNumberOfRequiredParameters() === 0) {
                    try {
                        $value = $this->explodeObjectValue($sourceObject->$methodName());
                    } catch (Exception $e) {
                        continue;
                    }

                    if (($propertyName === 'extension_attributes') && empty($value)) {
                        continue;
                    }

                    if ($value !== null) {
                        $res[$propertyName] = $this->castType(
                            $value,
                            $sourceObjectMethodInfo['type']
                        );
                    }
                }
            }
        }

        return $res;
    }
}
