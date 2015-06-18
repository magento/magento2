<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->model = new \Magento\Indexer\Model\ActionFactory($this->objectManagerMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage NotAction doesn't implement \Magento\Indexer\Model\ActionInterface
     */
    public function testGetWithException()
    {
        $notActionInterfaceMock = $this->getMock('NotAction', [], [], '', false);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('NotAction', [])
            ->willReturn($notActionInterfaceMock);
        $this->model->create('NotAction');
    }

    public function testCreate()
    {
        $actionInterfaceMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\ActionInterface',
            [],
            '',
            false
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Indexer\Model\ActionInterface', [])
            ->willReturn($actionInterfaceMock);
        $this->model->create('Magento\Indexer\Model\ActionInterface');
        $this->assertInstanceOf('Magento\Indexer\Model\ActionInterface', $actionInterfaceMock);
    }
}
