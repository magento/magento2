<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

/**
 * Class StatusTest
 *
 * @package Magento\Sales\Model\Order
 */
class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Sales\Model\Order\Status
     */
    protected $model;

    /**
     * SetUp test
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resourceMock = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Status',
            [],
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMock(
            'Magento\Framework\Event\Manager',
            [],
            [],
            '',
            false
        );
        $this->contextMock = $this->getMock(
            'Magento\Framework\Model\Context',
            [],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManagerMock));

        $this->model = $objectManager->getObject(
            'Magento\Sales\Model\Order\Status',
            [
                'context' => $this->contextMock,
                'resource' => $this->resourceMock,
                'data' => ['status' => 'test_status']
            ]
        );
    }

    /**
     *  Test for method unassignState
     */
    public function testUnassignStateSuccess()
    {
        $params = [
            'status' => $this->model->getStatus(),
            'state' => 'test_state',
        ];
        $this->resourceMock->expects($this->once())
            ->method('checkIsStateLast')
            ->with($this->equalTo($params['state']))
            ->will($this->returnValue(false));
        $this->resourceMock->expects($this->once())
            ->method('checkIsStatusUsed')
            ->with($this->equalTo($params['status']))
            ->will($this->returnValue(false));
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('sales_order_status_unassign'), $this->equalTo($params));

        $this->resourceMock->expects($this->once())
            ->method('unassignState')
            ->with($this->equalTo($params['status']), $this->equalTo($params['state']));
        $this->assertEquals($this->model, $this->model->unassignState($params['state']));
    }

    /**
     *  Test for method unassignState state is last
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The last status can't be unassigned from its current state.
     */
    public function testUnassignStateStateIsLast()
    {
        $params = [
            'status' => $this->model->getStatus(),
            'state' => 'test_state',
        ];
        $this->resourceMock->expects($this->once())
            ->method('checkIsStateLast')
            ->with($this->equalTo($params['state']))
            ->will($this->returnValue(true));
        $this->assertEquals($this->model, $this->model->unassignState($params['state']));
    }

    /**
     * Test for method unassignState status in use
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Status can't be unassigned, because it is used by existing order(s).
     */
    public function testUnassignStateStatusUsed()
    {
        $params = [
            'status' => $this->model->getStatus(),
            'state' => 'test_state',
        ];
        $this->resourceMock->expects($this->once())
            ->method('checkIsStateLast')
            ->with($this->equalTo($params['state']))
            ->will($this->returnValue(false));
        $this->resourceMock->expects($this->once())
            ->method('checkIsStatusUsed')
            ->with($this->equalTo($params['status']))
            ->will($this->returnValue(true));
        $this->assertEquals($this->model, $this->model->unassignState($params['state']));
    }

    /**
     * Retrieve prepared for test \Magento\Sales\Model\Order\Status
     *
     * @param null|\PHPUnit_Framework_MockObject_MockObject $resource
     * @param null|\PHPUnit_Framework_MockObject_MockObject $eventDispatcher
     * @return \Magento\Sales\Model\Order\Status
     */
    protected function _getPreparedModel($resource = null, $eventDispatcher = null)
    {
        if (!$resource) {
            $resource = $this->getMock('Magento\Sales\Model\ResourceModel\Order\Status', [], [], '', false);
        }
        if (!$eventDispatcher) {
            $eventDispatcher = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        }
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $helper->getObject(
            'Magento\Sales\Model\Order\Status',
            ['resource' => $resource, 'eventDispatcher' => $eventDispatcher]
        );
        return $model;
    }

    /**
     * Test for method assignState
     */
    public function testAssignState()
    {
        $state = 'test_state';
        $status = 'test_status';
        $visibleOnFront = true;

        $resource = $this->getMock('Magento\Sales\Model\ResourceModel\Order\Status', [], [], '', false);
        $resource->expects($this->once())
            ->method('beginTransaction');
        $resource->expects($this->once())
            ->method('assignState')
            ->with(
                $this->equalTo($status),
                $this->equalTo($state)
            );
        $resource->expects($this->once())->method('commit');

        $eventDispatcher = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        $model = $this->_getPreparedModel($resource, $eventDispatcher);
        $model->setStatus($status);
        $this->assertInstanceOf('Magento\Sales\Model\Order\Status', $model->assignState($state), $visibleOnFront);
    }
}
