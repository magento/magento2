<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit;

class ActionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mview\ActionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new \Magento\Framework\Mview\ActionFactory($this->objectManagerMock);
    }

    /**
     */
    public function testGetWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NotAction doesn\'t implement \\Magento\\Framework\\Mview\\ActionInterface');

        $notActionInterfaceMock = $this->getMockBuilder('Action')->getMock();
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'NotAction'
        )->willReturn(
            $notActionInterfaceMock
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
        )->willReturn(
            $actionInterfaceMock
        );
        $this->model->get(\Magento\Framework\Mview\ActionInterface::class);
        $this->assertInstanceOf(\Magento\Framework\Mview\ActionInterface::class, $actionInterfaceMock);
    }
}
