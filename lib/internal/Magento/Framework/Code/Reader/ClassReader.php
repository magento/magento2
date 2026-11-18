<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Reader;

use Magento\Framework\GetParameterClassTrait;
use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\ObjectManager\Attribute\Autowire;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Class ClassReader
 */
class ClassReader implements ClassReaderInterface
{
    use GetParameterClassTrait;

    /**
     * @var array
     */
    private $parentsCache = [];

    /** @var array<string, array|null> */
    private array $constructorCache = [];

    /**
     * Read class constructor signature
     *
     * @param  string $className
     * @return array|null
     * @throws ReflectionException
     */
    public function getConstructor($className)
    {
        if (array_key_exists($className, $this->constructorCache)) {
            return $this->constructorCache[$className];
        }

        $class = new ReflectionClass($className);
        $result = null;
        $constructor = $class->getConstructor();
        if ($constructor) {
            $result = [];
            $parameters = $constructor->getParameters();
            $attributeParameters = $this->getAttributeParameters($class, $parameters);
            /** @var $parameter ReflectionParameter */
            foreach ($parameters as $parameter) {
                try {
                    $parameterClass = $this->getParameterClass($parameter);

                    $parameterInfo = [
                        $parameter->getName(),
                        $parameterClass ? $parameterClass->getName() : null,
                        !$parameter->isOptional() && !$parameter->isDefaultValueAvailable(),
                        $this->getReflectionParameterDefaultValue($parameter),
                        $parameter->isVariadic(),
                    ];
                    if ($this->isAutowire(
                        $parameter,
                        $parameterClass,
                        $attributeParameters[$parameter->getName()] ?? null
                    )) {
                        $parameterInfo[] = true;
                    }
                    $result[] = $parameterInfo;
                } catch (ReflectionException $e) {
                    $message = sprintf(
                        'Impossible to process constructor argument %s of %s class',
                        $parameter->__toString(),
                        $className
                    );
                    throw new ReflectionException($message, 0, $e);
                }
            }
        }

        return $this->constructorCache[$className] = $result;
    }

    /**
     * Recover attributes omitted by generated Interceptor constructors.
     *
     * @param ReflectionClass $class
     * @param ReflectionParameter[] $parameters
     * @return array<string, ReflectionParameter>
     */
    private function getAttributeParameters(ReflectionClass $class, array $parameters): array
    {
        $parent = $class->getParentClass();
        if ($parent
            && strcasecmp($class->getName(), $parent->getName() . '\\Interceptor') === 0
            && $class->implementsInterface(InterceptorInterface::class)
        ) {
            $constructor = $parent->getConstructor();
            $parameters = $constructor ? $constructor->getParameters() : [];
        }

        $result = [];
        foreach ($parameters as $parameter) {
            $result[$parameter->getName()] = $parameter;
        }
        return $result;
    }

    /**
     * Check whether an optional parameter can opt into object resolution.
     *
     * @param ReflectionParameter $parameter
     * @param ReflectionClass|null $parameterClass
     * @param ReflectionParameter|null $attributeParameter
     * @return bool
     */
    private function isAutowire(
        ReflectionParameter $parameter,
        ?ReflectionClass $parameterClass,
        ?ReflectionParameter $attributeParameter
    ): bool {
        return $parameterClass !== null
            && $parameter->isOptional()
            && !$parameter->isVariadic()
            && $attributeParameter !== null
            && $attributeParameter->getAttributes(Autowire::class) !== [];
    }

    /**
     * Get reflection parameter default value
     *
     * @param  ReflectionParameter $parameter
     * @return array|mixed|null
     */
    private function getReflectionParameterDefaultValue(ReflectionParameter $parameter)
    {
        if ($parameter->isVariadic()) {
            return [];
        }

        return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
    }

    /**
     * Retrieve parent relation information for type in a following format
     * array(
     *     'Parent_Class_Name',
     *     'Interface_1',
     *     'Interface_2',
     *     ...
     * )
     *
     * @param  string $className
     * @return string[]
     */
    public function getParents($className)
    {
        if (isset($this->parentsCache[$className])) {
            return $this->parentsCache[$className];
        }

        $parentClass = get_parent_class($className);
        if ($parentClass) {
            $result = [];
            $interfaces = class_implements($className);
            if ($interfaces) {
                $parentInterfaces = class_implements($parentClass);
                if ($parentInterfaces) {
                    $result = array_values(array_diff($interfaces, $parentInterfaces));
                } else {
                    $result = array_values($interfaces);
                }
            }
            array_unshift($result, $parentClass);
        } else {
            $result = array_values(class_implements($className));
            if ($result) {
                array_unshift($result, null);
            } else {
                $result = [];
            }
        }

        $this->parentsCache[$className] = $result;

        return $result;
    }

    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}
