<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * Dummy fixture
     *
     * @return void
     */
    public static function dummyFixture(): void
    {
    }

    /**
     * @return void
     */
    public function testProcessFixturePath(): void
    {
        if (!defined('INTEGRATION_TESTS_DIR')) {
            define('INTEGRATION_TESTS_DIR', __DIR__);
        }
        $fixture = $this->processFixturePath('Some/Module/_files/some_fixture.php');
        $this->assertEquals(
            INTEGRATION_TESTS_DIR . '/testsuite/' . 'Some/Module/_files/some_fixture.php',
            $fixture
        );
    }

    /**
     * @return void
     */
    public function testProcessFixturePathCallableFixture(): void
    {
        $fixture = $this->processFixturePath('dummyFixture');
        $this->assertTrue(is_array($fixture));
        $this->assertNotFalse(array_search('dummyFixture', $fixture));
        $this->assertNotFalse(array_search(get_class($this), $fixture));
    }

    /**
     * @return void
     */
    public function testProcessFixturePathNotRegisteredModule(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Can\'t find registered Module with name Some_Module .');
        $this->processFixturePath('Some_Module::some_fixture.php');
    }

    /**
     * @return void
     */
    public function testProcessFixturePathRegisteredModule(): void
    {
        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Some_Module',
            __DIR__
        );
        $fixture = $this->processFixturePath('Some_Module::some_fixture.php');
        $this->assertStringEndsWith('some_fixture.php', $fixture);
    }

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
        $reflectionMethod->setAccessible(true);
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
     * Invoke resolver processFixturePath method
     *
     * @param string $annotation
     * @return string|array
     */
    private function processFixturePath(string $annotation)
    {
        $resolverMock = $this->createResolverMock();
        $resolverMock->method('getComponentRegistrar')->willReturn(new ComponentRegistrar());
        $reflection = new \ReflectionClass(Resolver::class);
        $reflectionMethod = $reflection->getMethod('processFixturePath');
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($resolverMock, $this, $annotation);
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
            ->setMethods(['getComponentRegistrar'])
            ->getMock();
        $mock->method('getComponentRegistrar')->willReturn(new ComponentRegistrar());
        $reflection = new \ReflectionClass(Resolver::class);
        $reflectionProperty = $reflection->getProperty('instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(Resolver::class, $mock);

        return $mock;
    }
}
