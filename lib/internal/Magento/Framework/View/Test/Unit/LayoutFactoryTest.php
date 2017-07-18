<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class LayoutFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\LayoutFactory */
    protected $layoutFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->layoutFactory = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\LayoutFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        $instance = \Magento\Framework\View\LayoutInterface::class;
        $layoutMock = $this->getMock($instance, [], [], '', false);
        $data = ['some' => 'data'];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($instance), $this->equalTo($data))
            ->will($this->returnValue($layoutMock));
        $this->assertInstanceOf($instance, $this->layoutFactory->create($data));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass must be an instance of LayoutInterface.
     */
    public function testCreateException()
    {
        $data = ['some' => 'other_data'];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new \stdClass()));
        $this->layoutFactory->create($data);
    }
}
