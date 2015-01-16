<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

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
            'Magento\Indexer\Model\ActionInterface',
            [],
            '',
            false
        );
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Indexer\Model\ActionInterface'
        )->will(
            $this->returnValue($actionInterfaceMock)
        );
        $this->model->get('Magento\Indexer\Model\ActionInterface');
        $this->assertInstanceOf('Magento\Indexer\Model\ActionInterface', $actionInterfaceMock);
    }
}
