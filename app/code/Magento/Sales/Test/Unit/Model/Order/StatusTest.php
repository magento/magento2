<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /**
     * @var Status|MockObject
     */
    protected $resourceMock;

    /**
     * @var Manager|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Sales\Model\Order\Status
     */
    protected $model;

    /**
     * SetUp test
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->createMock(Status::class);
        $this->eventManagerMock = $this->createMock(Manager::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $this->model = $objectManager->getObject(
            \Magento\Sales\Model\Order\Status::class,
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
            ->with($params['state'])
            ->willReturn(false);
        $this->resourceMock->expects($this->once())
            ->method('checkIsStatusUsed')
            ->with($params['status'])
            ->willReturn(false);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('sales_order_status_unassign', $params);

        $this->resourceMock->expects($this->once())
            ->method('unassignState')
            ->with($params['status'], $params['state']);
        $this->assertEquals($this->model, $this->model->unassignState($params['state']));
    }

    /**
     *  Test for method unassignState state is last
     */
    public function testUnassignStateStateIsLast()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The last status can\'t be changed and needs to stay assigned to its current state.'
        );
        $params = [
            'status' => $this->model->getStatus(),
            'state' => 'test_state',
        ];
        $this->resourceMock->expects($this->once())
            ->method('checkIsStateLast')
            ->with($params['state'])
            ->willReturn(true);
        $this->assertEquals($this->model, $this->model->unassignState($params['state']));
    }

    /**
     * Test for method unassignState status in use
     */
    public function testUnassignStateStatusUsed()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The status can\'t be unassigned because the status is currently used by an order.'
        );
        $params = [
            'status' => $this->model->getStatus(),
            'state' => 'test_state',
        ];
        $this->resourceMock->expects($this->once())
            ->method('checkIsStateLast')
            ->with($params['state'])
            ->willReturn(false);
        $this->resourceMock->expects($this->once())
            ->method('checkIsStatusUsed')
            ->with($params['status'])
            ->willReturn(true);
        $this->assertEquals($this->model, $this->model->unassignState($params['state']));
    }

    /**
     * Retrieve prepared for test \Magento\Sales\Model\Order\Status
     *
     * @param null|MockObject $resource
     * @param null|MockObject $eventDispatcher
     * @return \Magento\Sales\Model\Order\Status
     */
    protected function _getPreparedModel($resource = null, $eventDispatcher = null)
    {
        if (!$resource) {
            $resource = $this->createMock(Status::class);
        }
        if (!$eventDispatcher) {
            $eventDispatcher = $this->getMockForAbstractClass(ManagerInterface::class);
        }
        $helper = new ObjectManager($this);
        $model = $helper->getObject(
            \Magento\Sales\Model\Order\Status::class,
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

        $resource = $this->createMock(Status::class);
        $resource->expects($this->once())
            ->method('beginTransaction');
        $resource->expects($this->once())
            ->method('assignState')
            ->with(
                $status,
                $state
            );
        $resource->expects($this->once())->method('commit');

        $eventDispatcher = $this->getMockForAbstractClass(ManagerInterface::class);

        $model = $this->_getPreparedModel($resource, $eventDispatcher);
        $model->setStatus($status);
        $this->assertInstanceOf(
            \Magento\Sales\Model\Order\Status::class,
            $model->assignState($state, $visibleOnFront)
        );
    }
}
