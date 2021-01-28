<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class ValueFactoryTest extends \Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase
{
    protected function setUp(): void
    {
        $this->instanceClassName = \Magento\Framework\App\Config\ValueInterface::class;
        $this->factoryClassName = \Magento\Framework\App\Config\ValueFactory::class;
        parent::setUp();
    }

    /**
     */
    public function testCreateWithException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn('somethingElse');
        $this->factory->create();
    }
}
