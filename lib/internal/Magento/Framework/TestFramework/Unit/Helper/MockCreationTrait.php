<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Unit\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

/**
 * Trait for creating partial mocks with reflection for PHPUnit 12 migration
 *
 * Provides helper methods for creating mocks when standard PHPUnit methods are insufficient
 */
trait MockCreationTrait
{
    /**
     * Create a partial mock with reflection.
     *
     * Use this when you need to mock methods that don't exist in the class/interface
     * and cannot use standard createPartialMock() which would throw CannotUseOnlyMethodsException.
     *
     * Note: setAccessible() calls removed for PHP 8.5+ compatibility.
     * Since PHP 8.1, properties and methods are always accessible via reflection.
     *
     * @param string $className
     * @param array $methods Methods to mock
     * @param array $constructorArgs Optional constructor arguments (enables constructor if provided)
     * @return MockObject
     */
    protected function createPartialMockWithReflection(
        string $className,
        array $methods,
        array $constructorArgs = []
    ): MockObject {
        $reflection = new ReflectionClass($this);
        $getMockBuilderMethod = $reflection->getMethod('getMockBuilder');
        $mockBuilder = $getMockBuilderMethod->invoke($this, $className);

        $builderReflection = new ReflectionClass($mockBuilder);
        $methodsProperty = $builderReflection->getProperty('methods');
        $methodsProperty->setValue($mockBuilder, $methods);

        if (empty($constructorArgs)) {
            $mockBuilder->disableOriginalConstructor();
        } else {
            $mockBuilder->setConstructorArgs($constructorArgs);
        }
        
        return $mockBuilder->getMock();
    }
}
