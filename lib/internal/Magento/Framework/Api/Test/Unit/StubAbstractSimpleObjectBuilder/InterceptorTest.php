<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\StubAbstractSimpleObjectBuilder;

use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\Test\Unit\StubAbstractSimpleObject;
use Magento\Framework\Api\Test\Unit\StubAbstractSimpleObjectBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InterceptorTest extends TestCase
{
    /**
     * @var MockObject|ObjectFactory
     */
    private $objectFactoryMock;

    /**
     * @var StubAbstractSimpleObjectBuilder
     */
    private $stubSimpleObjectBuilderInterceptor;

    protected function setUp(): void
    {
        $this->objectFactoryMock = $this->createMock(ObjectFactory::class);
        $this->stubSimpleObjectBuilderInterceptor = new Interceptor($this->objectFactoryMock);
    }

    public function testCreate()
    {
        $stubSimpleObjectMock = $this->createMock(StubAbstractSimpleObject::class);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(StubAbstractSimpleObject::class, ['data' => []])
            ->willReturn($stubSimpleObjectMock);
        $object = $this->stubSimpleObjectBuilderInterceptor->create();
        $this->assertInstanceOf(StubAbstractSimpleObject::class, $object);
    }
}
