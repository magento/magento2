<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use Magento\Signifyd\Model\OrderStateService;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class OrderStateServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    private static $orderId = 123;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactory;

    /**
     * @var OrderManagementInterface|MockObject
     */
    private $orderManagement;

    /**
     * @var CommentsHistoryUpdater|MockObject
     */
    private $commentsHistoryUpdater;

    /**
     * @var CaseInterface|MockObject
     */
    private $caseEntity;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var OrderStateService
     */
    private $orderStateService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderManagement = $this->getMockBuilder(OrderManagementInterface::class)
            ->getMockForAbstractClass();

        $this->commentsHistoryUpdater = $this->getMockBuilder(CommentsHistoryUpdater::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->order);

        $this->caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->caseEntity->expects($this->once())
            ->method('getOrderId')
            ->willReturn(self::$orderId);

        $this->orderStateService = new OrderStateService(
            $this->orderFactory,
            $this->orderManagement,
            $this->commentsHistoryUpdater
        );
    }

    /**
     * Tests update order state flow when case guarantee disposition is PENDING.
     *
     * @param bool $canHold
     * @param bool $hold
     * @param int $addCommentCall
     * @dataProvider updateByCaseWithGuaranteePendingDataProvider
     */
    public function testUpdateByCaseWithGuaranteePending($canHold, $hold, $addCommentCall)
    {
        $this->caseEntity->expects($this->once())
            ->method('getGuaranteeDisposition')
            ->willReturn(CaseInterface::GUARANTEE_PENDING);
        $this->order->expects($this->any())
            ->method('canHold')
            ->willReturn($canHold);
        $this->orderManagement->expects($this->any())
            ->method('hold')
            ->willReturn($hold);
        $this->commentsHistoryUpdater->expects($this->exactly($addCommentCall))
            ->method('addComment')
            ->with(
                $this->caseEntity,
                __('Awaiting the Signifyd guarantee disposition.'),
                Order::STATE_HOLDED
            );

        $this->orderStateService->updateByCase($this->caseEntity);
    }

    /**
     * @return array
     */
    public function updateByCaseWithGuaranteePendingDataProvider()
    {
        return [
            ['canHold' => true, 'hold' => true, 'addCommentCall' => 1],
            ['canHold' => false, 'hold' => true, 'addCommentCall' => 0],
            ['canHold' => true, 'hold' => false, 'addCommentCall' => 0],
        ];
    }

    /**
     * Tests update order state flow when case guarantee disposition is APPROVED.
     *
     * @param bool $canUnhold
     * @param int $unholdCall
     * @dataProvider updateByCaseWithGuaranteeApprovedDataProvider
     */
    public function testUpdateByCaseWithGuaranteeApproved($canUnhold, $unholdCall)
    {
        $this->caseEntity->expects($this->once())
            ->method('getGuaranteeDisposition')
            ->willReturn(CaseInterface::GUARANTEE_APPROVED);
        $this->order->expects($this->any())
            ->method('canUnhold')
            ->willReturn($canUnhold);
        $this->orderManagement->expects($this->exactly($unholdCall))
            ->method('unHold');
        $this->commentsHistoryUpdater->expects($this->never())
            ->method('addComment');

        $this->orderStateService->updateByCase($this->caseEntity);
    }

    /**
     * @return array
     */
    public function updateByCaseWithGuaranteeApprovedDataProvider()
    {
        return [
            ['canUnhold' => true,  'unholdCall' => 1],
            ['canUnhold' => false, 'unholdCall' => 0]
        ];
    }

    /**
     * Tests update order state flow when case guarantee disposition is DECLINED.
     *
     * @param bool $canHold
     * @param int $holdCall
     * @dataProvider updateByCaseWithGuaranteeDeclinedDataProvider
     */
    public function testUpdateByCaseWithGuaranteeDeclined($canHold, $holdCall)
    {
        $this->caseEntity->expects($this->once())
            ->method('getGuaranteeDisposition')
            ->willReturn(CaseInterface::GUARANTEE_DECLINED);
        $this->order->expects($this->any())
            ->method('canHold')
            ->willReturn($canHold);
        $this->orderManagement->expects($this->exactly($holdCall))
            ->method('hold');
        $this->commentsHistoryUpdater->expects($this->never())
            ->method('addComment');

        $this->orderStateService->updateByCase($this->caseEntity);
    }

    /**
     * @return array
     */
    public function updateByCaseWithGuaranteeDeclinedDataProvider()
    {
        return [
            ['canHold' => true,  'holdCall' => 1],
            ['canHold' => false, 'holdCall' => 0]
        ];
    }
}
