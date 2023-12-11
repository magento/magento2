<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Override\Fixture\Resolver;

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver\TestSetter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\TestFramework\Workaround\Override\Fixture\Resolver\TestSetter.
 */
class TestSetterTest extends TestCase
{
    /** @var TestSetter */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new TestSetter();
    }

    /**
     * @return void
     */
    public function testStartTest(): void
    {
        $resolverMock = $this->createResolverMock();
        $resolverMock->expects($this->once())
            ->method('setCurrentTest')
            ->with($this);
        $this->object->startTest($this);
    }

    /**
     * @return void
     */
    public function testEndTest(): void
    {
        $resolverMock = $this->createResolverMock();
        $resolverMock->expects($this->once())
            ->method('setCurrentTest')
            ->with(null);
        $this->object->endTest($this);
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
            ->setMethods(['setCurrentTest'])
            ->getMock();
        $reflection = new \ReflectionClass(Resolver::class);
        $reflectionProperty = $reflection->getProperty('instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(Resolver::class, $mock);

        return $mock;
    }
}
