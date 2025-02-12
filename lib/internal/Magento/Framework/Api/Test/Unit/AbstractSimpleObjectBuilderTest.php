<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit;

use Magento\Framework\Api\ObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractSimpleObjectBuilderTest extends TestCase
{
    /**
     * @var MockObject|ObjectFactory
     */
    private $objectFactoryMock;

    /**
     * @var StubAbstractSimpleObjectBuilder
     */
    private $stubSimpleObjectBuilder;

    protected function setUp(): void
    {
        $this->objectFactoryMock = $this->createMock(ObjectFactory::class);
        $this->stubSimpleObjectBuilder = new StubAbstractSimpleObjectBuilder($this->objectFactoryMock);
    }

    public function testCreate()
    {
        $stubSimpleObjectMock = $this->createMock(StubAbstractSimpleObject::class);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(StubAbstractSimpleObject::class, ['data' => []])
            ->willReturn($stubSimpleObjectMock);
        $object = $this->stubSimpleObjectBuilder->create();
        $this->assertInstanceOf(StubAbstractSimpleObject::class, $object);
    }
}
