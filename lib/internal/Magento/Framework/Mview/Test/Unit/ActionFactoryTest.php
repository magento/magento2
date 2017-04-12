<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new \Magento\Framework\Mview\ActionFactory($this->objectManagerMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage NotAction doesn't implement \Magento\Framework\Mview\ActionInterface
     */
    public function testGetWithException()
    {
        $notActionInterfaceMock = $this->getMock('NotAction', [], [], '', false);
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'NotAction'
        )->will(
            $this->returnValue($notActionInterfaceMock)
        );
        $this->model->get('NotAction');
    }

    public function testGet()
    {
        $actionInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\ActionInterface::class,
            [],
            '',
            false
        );
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            \Magento\Framework\Mview\ActionInterface::class
        )->will(
            $this->returnValue($actionInterfaceMock)
        );
        $this->model->get(\Magento\Framework\Mview\ActionInterface::class);
        $this->assertInstanceOf(\Magento\Framework\Mview\ActionInterface::class, $actionInterfaceMock);
    }
}
