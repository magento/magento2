<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

class ObjectManagerProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerProvider
     */
    private $object;

    /**
     * @var \Magento\Framework\Console\ParameterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $parameter;

    protected function setUp()
    {
        $this->parameter = $this->getMockForAbstractClass('Magento\Framework\Console\ParameterInterface');
        $this->object = new ObjectManagerProvider($this->parameter);
    }

    public function testGet()
    {
        $this->parameter->expects($this->once())->method('getCustomParameters')->willReturn([]);
        $objectManager = $this->object->get();
        $this->assertInstanceOf('Magento\Framework\ObjectManagerInterface', $objectManager);
        $this->assertSame($objectManager, $this->object->get());
    }
}