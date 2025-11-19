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
     * @param string $className
     * @param array $methods Methods to mock
     * @return MockObject
     */
    protected function createPartialMockWithReflection(string $className, array $methods): MockObject
    {
        $reflection = new ReflectionClass($this);
        $getMockBuilderMethod = $reflection->getMethod('getMockBuilder');
        $getMockBuilderMethod->setAccessible(true);
        $mockBuilder = $getMockBuilderMethod->invoke($this, $className);

        $builderReflection = new ReflectionClass($mockBuilder);
        $methodsProperty = $builderReflection->getProperty('methods');
        $methodsProperty->setAccessible(true);
        $methodsProperty->setValue($mockBuilder, $methods);

        $mockBuilder->disableOriginalConstructor();
        return $mockBuilder->getMock();
    }
}
