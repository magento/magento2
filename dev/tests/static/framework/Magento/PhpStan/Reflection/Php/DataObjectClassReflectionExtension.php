<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PhpStan\Reflection\Php;

use Magento\Framework\DataObject;
use Magento\Framework\Session\SessionManager;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;

/**
 * Extension to add support of magic methods (get/set/uns/has) based on @see DataObject
 */
class DataObjectClassReflectionExtension implements MethodsClassReflectionExtension
{
    private const MAGIC_METHODS_PREFIXES = [
        'get',
        'set',
        'uns',
        'has'
    ];

    private const PREFIX_LENGTH = 3;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var AnnotationsMethodsClassReflectionExtension
     */
    private $annotationsMethodsClassReflectionExtension;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->annotationsMethodsClassReflectionExtension = $this->container
            ->getByType(AnnotationsMethodsClassReflectionExtension::class);
    }

    /**
     * Check if class has relations with DataObject and requested method can be considered as a magic method.
     *
     * @param ClassReflection $classReflection
     * @param string $methodName
     *
     * @return bool
     */
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        // Workaround due to annotation extension always loads last.
        if ($this->annotationsMethodsClassReflectionExtension->hasMethod($classReflection, $methodName)) {
            // In case when annotation already available for the method, we will not use 'magic methods' approach.
            return false;
        }
        if ($classReflection->isSubclassOf(DataObject::class) || $classReflection->getName() == DataObject::class) {
            return in_array($this->getPrefix($methodName), self::MAGIC_METHODS_PREFIXES);
        }
        /** SessionManager redirects all calls to `__call` to container which extends DataObject */
        if ($classReflection->isSubclassOf(SessionManager::class)
            || $classReflection->getName() === SessionManager::class
        ) {
            /** @see \Magento\Framework\Session\SessionManager::__call */
            return in_array($this->getPrefix($methodName), self::MAGIC_METHODS_PREFIXES);
        }

        return false;
    }

    /**
     * Get prefix from method name.
     *
     * @param string $methodName
     *
     * @return string
     */
    private function getPrefix(string $methodName): string
    {
        return (string)substr($methodName, 0, self::PREFIX_LENGTH);
    }

    /**
     * Get method reflection instance.
     *
     * @param ClassReflection $classReflection
     * @param string $methodName
     *
     * @return MethodReflection
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new DataObjectMethodReflection($classReflection, $methodName);
    }
}
