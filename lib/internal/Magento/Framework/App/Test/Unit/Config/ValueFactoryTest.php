<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class ValueFactoryTest extends \Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase
{
    protected function setUp()
    {
        $this->instanceClassName = \Magento\Framework\App\Config\ValueInterface::class;
        $this->factoryClassName = \Magento\Framework\App\Config\ValueFactory::class;
        parent::setUp();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('somethingElse'));
        $this->factory->create();
    }
}
