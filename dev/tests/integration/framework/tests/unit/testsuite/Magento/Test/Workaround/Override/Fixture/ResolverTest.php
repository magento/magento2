<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Override\Fixture;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\TestFramework\Workaround\Override\Fixture\Resolver.
 */
class ResolverTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetApplierByFixtureType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported fixture type unsupportedFixtureType provided');
        $resolverMock = $this->createResolverMock();
        $reflection = new \ReflectionClass(Resolver::class);
        $reflectionMethod = $reflection->getMethod('getApplierByFixtureType');
        $reflectionMethod->invoke($resolverMock, 'unsupportedFixtureType');
    }

    /**
     * @return void
     */
    public function testRequireDataFixture(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Fixture type is not specified for resolver');
        $this->createResolverMock();
        $resolver = Resolver::getInstance();
        $resolver->requireDataFixture('path/to/fixture.php');
    }

    /**
     * Create mock for resolver object
     *
     * @return MockObject
     */
    private function createResolverMock(): MockObject
    {
        $mock = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->addMethods(['getComponentRegistrar'])
            ->getMock();
        $mock->method('getComponentRegistrar')->willReturn(new ComponentRegistrar());
        $reflection = new \ReflectionClass(Resolver::class);
        $reflectionProperty = $reflection->getProperty('instance');
        $reflectionProperty->setValue(Resolver::class, $mock);

        return $mock;
    }
}
