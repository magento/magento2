<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;

/**
 * Determines method metadata information.
 */
class MethodsMap
{
    const SERVICE_INTERFACE_METHODS_CACHE_PREFIX = 'serviceInterfaceMethodsMap';
    const BASE_MODEL_CLASS = 'Magento\Framework\Model\AbstractExtensibleModel';

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var array
     */
    private $serviceInterfaceMethodsMap = [];

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param TypeProcessor $typeProcessor
     * @param \Magento\Framework\Api\AttributeTypeResolverInterface $typeResolver
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        TypeProcessor $typeProcessor,
        \Magento\Framework\Api\AttributeTypeResolverInterface $typeResolver
    ) {
        $this->cache = $cache;
        $this->typeProcessor = $typeProcessor;
        $this->attributeTypeResolver = $typeResolver;
    }

    /**
     * Get return type by interface name and method
     *
     * @param string $interfaceName
     * @param string $methodName
     * @return string
     */
    public function getMethodReturnType($interfaceName, $methodName)
    {
        return $this->getMethodsMap($interfaceName)[$methodName]['type'];
    }

    /**
     * Return service interface or Data interface methods loaded from cache
     *
     * @param string $interfaceName
     * @return array
     * <pre>
     * Service methods' reflection data stored in cache as 'methodName' => 'returnType'
     * ex.
     * [
     *  'create' => '\Magento\Customer\Api\Data\Customer',
     *  'validatePassword' => 'boolean'
     * ]
     * </pre>
     */
    public function getMethodsMap($interfaceName)
    {
        $key = self::SERVICE_INTERFACE_METHODS_CACHE_PREFIX . "-" . md5($interfaceName);
        if (!isset($this->serviceInterfaceMethodsMap[$key])) {
            $methodMap = $this->cache->load($key);
            if ($methodMap) {
                $this->serviceInterfaceMethodsMap[$key] = unserialize($methodMap);
            } else {
                $methodMap = $this->getMethodMapViaReflection($interfaceName);
                $this->serviceInterfaceMethodsMap[$key] = $methodMap;
                $this->cache->save(serialize($this->serviceInterfaceMethodsMap[$key]), $key);
            }
        }
        return $this->serviceInterfaceMethodsMap[$key];
    }

    /**
     * Use reflection to load the method information
     *
     * @param string $interfaceName
     * @return array
     */
    private function getMethodMapViaReflection($interfaceName)
    {
        $methodMap = [];
        $class = new ClassReflection($interfaceName);
        $baseClassMethods = false;
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Include all the methods of classes inheriting from AbstractExtensibleObject.
            // Ignore all the methods of AbstractExtensibleModel's parent classes
            if ($method->class === self::BASE_MODEL_CLASS) {
                $baseClassMethods = true;
            } elseif ($baseClassMethods) {
                // ReflectionClass::getMethods() sorts the methods by class (lowest in inheritance tree first)
                // then by the order they are defined in the class definition
                break;
            }

            if ($this->isSuitableMethod($method)) {
                $methodMap[$method->getName()] = $this->typeProcessor->getGetterReturnType($method);
            }
        }
        return $methodMap;
    }

    /**
     * Determines if the method is suitable to be used by the processor.
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    private function isSuitableMethod($method)
    {
        $isSuitableMethodType = !($method->isConstructor() || $method->isFinal()
            || $method->isStatic() || $method->isDestructor());

        $isExcludedMagicMethod = strpos($method->getName(), '__') === 0;
        return $isSuitableMethodType && !$isExcludedMagicMethod;
    }
}
