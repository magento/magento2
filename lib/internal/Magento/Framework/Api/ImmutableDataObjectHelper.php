<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Api;

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\Reflection\NameFinder;
use Magento\Framework\Reflection\TypeProcessor;
use ReflectionException;
use Zend\Code\Reflection\ClassReflection;

class ImmutableDataObjectHelper
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
     * @param ObjectFactory $objectFactory
     * @param TypeProcessor $typeProcessor
     * @param ConfigInterface $config
     * @param NameFinder $nameFinder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        TypeProcessor $typeProcessor,
        ConfigInterface $config,
        NameFinder $nameFinder
    ) {
        $this->config = $config;
        $this->typeProcessor = $typeProcessor;
        $this->nameFinder = $nameFinder;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Return true if a class is a data object using "data" constructor field
     *
     * @param string $className
     * @return bool
     */
    private function isDataObjectModel(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, DataObject::class) ||
            is_subclass_of($className, AbstractSimpleObject::class);
    }

    /**
     * Return true if a class is inherited from \Magento\Framework\Model\AbstractModel and requires setDataChange
     *
     * @param string $className
     * @return bool
     */
    private function isDataModel(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, AbstractModel::class);
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
     * @return array|object
     * @throws ReflectionException
     */
    private function getTypeValue($value, string $type)
    {
        if ($this->typeProcessor->isSimpleType($type)) {
            return $value;
        }

        return $this->createFromArray($value, $type);
    }

    /**
     * @param string $className
     * @param array $data
     * @return array
     * @throws ReflectionException
     */
    private function getConstructorData(string $className, array $data): array
    {
        $className = $this->getRealClassName($className);
        $class = new ClassReflection($className);

        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }

        // Inject data field if existing for backward compatibility with
        // \Magento\Framework\DataObject
        if ($this->isDataObjectModel($class->getName())) {
            $res['data'] = [];
            foreach ($data as $propertyName => $propertyValue) {
                // Find data getter to retrieve its type
                $snakeCaseProperty = SimpleDataObjectConverter::camelCaseToSnakeCase($propertyName);
                $type = $this->getPropertyTypeFromGetterMethod($class, $propertyName);
                if ($type !== '') {
                    $res['data'][$snakeCaseProperty] = $this->getTypeValue($propertyValue, $type);
                }
            }
        } else {
            // Inject into named parameters if a getter method exists
            $res = [];
            $parameters = $constructor->getParameters();
            foreach ($parameters as $parameter) {
                $type = $this->getPropertyTypeFromGetterMethod($class, $parameter->getName());
                $snakeCaseProperty = SimpleDataObjectConverter::camelCaseToSnakeCase($parameter->getName());
                if (($type !== '') && isset($data[$snakeCaseProperty])) {
                    $res[$parameter->getName()] = $this->getTypeValue($data[$snakeCaseProperty], $type);
                }
            }
        }

        return $res;
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
     * Populate data object using data in array format.
     *
     * @param array $data
     * @param string $interfaceName
     * @return object
     * @throws ReflectionException
     */
    public function createFromArray(array $data, string $interfaceName)
    {
        $constructorArgs = $this->getConstructorData($interfaceName, $data);
        $resObject = $this->objectFactory->create($interfaceName, $constructorArgs);

        if ($this->isDataModel($interfaceName)) {
            $resObject->setDataChanges(true);
        }

        return $resObject;
    }

    /**
     * Create anew DTO with updated information from array
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
        $data = array_replace_recursive($sourceObjectData, $data);

        return $this->createFromArray($data, get_class($sourceObject));
    }

    /**
     * Merge data into object data
     *
     * @param $sourceObject
     * @return array
     */
    public function getObjectData($sourceObject): array
    {
        if ($this->isDataObjectModel(get_class($sourceObject))) {
            return $sourceObject->getData();
        }

        $sourceObjectMethods = get_class_methods(get_class($sourceObject));

        $res = [];
        foreach ($sourceObjectMethods as $sourceObjectMethod) {
            if (preg_match('/^(is|get)([A-Z]\w*)$/', $sourceObjectMethod, $matches)) {
                $propertyName = SimpleDataObjectConverter::camelCaseToSnakeCase($matches[2]);
                $methodName = $matches[0];

                $value = $sourceObject->$methodName();
                $res[$propertyName] = is_object($value) ?
                    $this->getObjectData($value) :
                    $value;
            }
        }

        return $res;
    }
}
