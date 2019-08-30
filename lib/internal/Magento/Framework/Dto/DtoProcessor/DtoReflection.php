<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\DtoProcessor;

use Exception;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\Reflection\NameFinder;
use Magento\Framework\Reflection\TypeProcessor;
use ReflectionClass;
use ReflectionException;
use Zend\Code\Reflection\ClassReflection;

/**
 * Reflection methods for DTO classes
 */
class DtoReflection
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
     * @param ConfigInterface $config
     * @param NameFinder $nameFinder
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(
        ConfigInterface $config,
        NameFinder $nameFinder,
        TypeProcessor $typeProcessor
    ) {
        $this->config = $config;
        $this->typeProcessor = $typeProcessor;
        $this->nameFinder = $nameFinder;
    }

    /**
     * Return true if a class is a data object using "data" constructor field
     *
     * @param string $className
     * @return bool
     */
    public function isDataObject(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, AbstractSimpleObject::class) ||
            is_subclass_of($className, DataObject::class);
    }

    /**
     * Return true if a class is extensible through extension attributes
     *
     * @param string $className
     * @return bool
     * @throws ReflectionException
     */
    public function isExtensibleObject(string $className): bool
    {
        // TODO: Add a cache layer here
        $modelReflection = new ReflectionClass($className);

        if ($modelReflection->isInterface()
            && $modelReflection->isSubclassOf(ExtensibleDataInterface::class)
            && $modelReflection->hasMethod('getExtensionAttributes')
        ) {
            return true;
        }

        foreach ($modelReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->isSubclassOf(ExtensibleDataInterface::class)
                && $interfaceReflection->hasMethod('getExtensionAttributes')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if a class is extensible through custom attributes
     *
     * @param string $className
     * @return bool
     */
    public function isCustomAttributesObject(string $className): bool
    {
        return
            is_subclass_of($className, CustomAttributesDataInterface::class);
    }

    /**
     * Return true if a class is inherited from \Magento\Framework\Model\AbstractModel and requires setDataChange
     *
     * @param string $className
     * @return bool
     */
    public function isDataModel(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, AbstractModel::class);
    }

    /**
     * Get real class name (if has a preference)
     *
     * @param string $className
     * @return string
     */
    public function getRealClassName(string $className): string
    {
        $preferenceClass = $this->config->getPreference($className);
        return $preferenceClass ?: $className;
    }

    /**
     * Return the property type by its getter name
     * @param string $type
     * @param string $propertyName
     * @return string
     * @throws ReflectionException
     */
    public function getPropertyTypeFromGetterMethod(string $type, string $propertyName): string
    {
        // TODO: Add a cache layer
        $classReflection = new ClassReflection($type);

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
}
