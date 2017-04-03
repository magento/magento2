<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Service;

/**
 * Class OrderUnHoldTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Api\OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusHistoryRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\SearchCriteria
     */
    protected $searchCriteriaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\Filter
     */
    protected $filterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\OrderNotifier
     */
    protected $orderNotifierMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Status\History
     */
    protected $orderStatusHistoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface
     */
    protected $orderSearchResultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\ManagerInterface
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     */
    protected $orderCommentSender;

    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(
            \Magento\Sales\Api\OrderRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStatusHistoryRepositoryMock = $this->getMockBuilder(
            \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaMock = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteria::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock = $this->getMockBuilder(
            \Magento\Framework\Api\FilterBuilder::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock = $this->getMockBuilder(
            \Magento\Framework\Api\Filter::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderNotifierMock = $this->getMockBuilder(
            \Magento\Sales\Model\OrderNotifier::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStatusHistoryMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Status\History::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderSearchResultMock = $this->getMockBuilder(
            \Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(
            \Magento\Framework\Event\ManagerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderCommentSender = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderService = new \Magento\Sales\Model\Service\OrderService(
            $this->orderRepositoryMock,
            $this->orderStatusHistoryRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->orderNotifierMock,
            $this->eventManagerMock,
            $this->orderCommentSender
        );
    }

    /**
     * test for Order::cancel()
     */
    public function testCancel()
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(123)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('cancel')
            ->willReturn($this->orderMock);
        $this->assertTrue($this->orderService->cancel(123));
    }

    public function testGetCommentsList()
    {
        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('parent_id')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with(123)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->filterMock);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->with([$this->filterMock])
            ->willReturn($this->filterBuilderMock);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->orderStatusHistoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->orderSearchResultMock);
        $this->assertEquals($this->orderSearchResultMock, $this->orderService->getCommentsList(123));
    }

    public function testAddComment()
    {
        $clearComment = "Comment text here...";
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(123)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('addStatusHistory')
            ->with($this->orderStatusHistoryMock)
            ->willReturn($this->orderMock);
        $this->orderStatusHistoryMock->expects($this->once())
            ->method('getComment')
            ->willReturn("<h1>" . $clearComment);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn([]);
        $this->orderCommentSender->expects($this->once())
            ->method('send')
            ->with($this->orderMock, false, $clearComment);
        $this->assertTrue($this->orderService->addComment(123, $this->orderStatusHistoryMock));
    }

    public function testNotify()
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(123)
            ->willReturn($this->orderMock);
        $this->orderNotifierMock->expects($this->once())
            ->method('notify')
            ->with($this->orderMock)
            ->willReturn(true);
        $this->assertTrue($this->orderService->notify(123));
    }

    public function testGetStatus()
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(123)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn('test-status');
        $this->assertEquals('test-status', $this->orderService->getStatus(123));
    }

    public function testHold()
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(123)
            ->willReturn($this->orderMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('hold')
            ->willReturn($this->orderMock);
        $this->assertTrue($this->orderService->hold(123));
    }

    public function testUnHold()
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(123)
            ->willReturn($this->orderMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('unHold')
            ->willReturn($this->orderMock);
        $this->assertTrue($this->orderService->unHold(123));
    }
}
