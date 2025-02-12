<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Service;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\OrderMutex;
use Magento\Sales\Model\OrderNotifier;
use Magento\Sales\Model\Service\OrderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OrderServiceTest extends TestCase
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var MockObject|OrderRepositoryInterface
     */
    protected $orderRepositoryMock;

    /**
     * @var MockObject|OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusHistoryRepositoryMock;

    /**
     * @var MockObject|SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject|SearchCriteria
     */
    protected $searchCriteriaMock;

    /**
     * @var MockObject|FilterBuilder
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject|Filter
     */
    protected $filterMock;

    /**
     * @var MockObject|OrderNotifier
     */
    protected $orderNotifierMock;

    /**
     * @var MockObject|Order
     */
    protected $orderMock;

    /**
     * @var MockObject|History
     */
    protected $orderStatusHistoryMock;

    /**
     * @var MockObject|OrderStatusHistorySearchResultInterface
     */
    protected $orderSearchResultMock;

    /**
     * @var MockObject|ManagerInterface
     */
    protected $eventManagerMock;

    /**
     * @var MockObject|OrderCommentSender
     */
    protected $orderCommentSender;

    /**
     * @var MockObject|AdapterInterface
     */
    private $adapterInterfaceMock;

    /**
     * @var MockObject|ResourceConnection
     */
    private $resourceConnectionMock;

    /**
     * @var MockObject|Config
     */
    private $orderConfigMock;

    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->getMockBuilder(
            OrderRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStatusHistoryRepositoryMock = $this->getMockBuilder(
            OrderStatusHistoryRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(
            SearchCriteriaBuilder::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaMock = $this->getMockBuilder(
            SearchCriteria::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock = $this->getMockBuilder(
            FilterBuilder::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock = $this->getMockBuilder(
            Filter::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderNotifierMock = $this->getMockBuilder(
            OrderNotifier::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(
            Order::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStatusHistoryMock = $this->getMockBuilder(
            History::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderSearchResultMock = $this->getMockBuilder(
            OrderStatusHistorySearchResultInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(
            ManagerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderCommentSender = $this->getMockBuilder(
            OrderCommentSender::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PaymentFailuresInterface|MockObject  $paymentFailures */
        $paymentFailures = $this->getMockForAbstractClass(PaymentFailuresInterface::class);

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->adapterInterfaceMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderService = new OrderService(
            $this->orderRepositoryMock,
            $this->orderStatusHistoryRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->orderNotifierMock,
            $this->eventManagerMock,
            $this->orderCommentSender,
            $paymentFailures,
            $logger,
            new OrderMutex($this->resourceConnectionMock),
            $this->orderConfigMock
        );
    }

    /**
     * test for Order::cancel()
     */
    public function testCancel()
    {
        $orderId = 123;
        $this->mockConnection($orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('cancel')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('canCancel')
            ->willReturn(true);
        $this->assertTrue($this->orderService->cancel(123));
    }

    /**
     * test for Order::cancel() fail case
     */
    public function testCancelFailed()
    {
        $orderId = 123;
        $this->mockConnection($orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->never())
            ->method('cancel')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('canCancel')
            ->willReturn(false);
        $this->assertFalse($this->orderService->cancel(123));
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
        $orderId = 123;
        $clearComment = "Comment text here...";
        $this->mockCommentStatuses($orderId, Order::STATUS_FRAUD);
        $this->orderMock->expects($this->once())
            ->method('setStatus')
            ->willReturn(Order::STATUS_FRAUD);
        $this->orderStatusHistoryMock->expects($this->once())
            ->method('setStatus')
            ->willReturn(Order::STATUS_FRAUD);
        $this->orderMock->expects($this->once())
            ->method('addStatusHistory')
            ->with($this->orderStatusHistoryMock)
            ->willReturn($this->orderMock);
        $this->orderStatusHistoryMock->method('getComment')
            ->willReturn("<h1>" . $clearComment);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn([]);
        $this->orderCommentSender->expects($this->once())
            ->method('send')
            ->with($this->orderMock, false, $clearComment);
        $this->assertTrue($this->orderService->addComment($orderId, $this->orderStatusHistoryMock));
    }

    /**
     * test for add comment with order status change case
     */
    public function testAddCommentWithStatus()
    {
        $orderId = 123;
        $inputException = __(
            'Unable to add comment: The status "%1" is not part of the order status history.',
            Order::STATE_NEW
        );
        $this->mockCommentStatuses($orderId, Order::STATE_NEW);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage((string)$inputException);
        $this->orderService->addComment($orderId, $this->orderStatusHistoryMock);
    }

    /**
     * @param $orderId
     * @param $orderStatusHistory
     */
    private function mockCommentStatuses($orderId, $orderStatusHistory): void
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);
        $this->orderConfigMock->expects($this->once())
            ->method('getStateStatuses')
            ->with(Order::STATE_PROCESSING)
            ->willReturn([
                Order::STATE_PROCESSING => 'Processing',
                Order::STATUS_FRAUD => 'Suspected Fraud',
                'test' => 'Tests'
            ]);
        $this->orderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(Order::STATE_PROCESSING);
        $this->orderStatusHistoryMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($orderStatusHistory);
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

    /**
     * @param int $orderId
     */
    private function mockConnection(int $orderId): void
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with('sales_order', 'entity_id')
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('entity_id = ?', $orderId)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('forUpdate')
            ->with(true)
            ->willReturnSelf();
        $this->adapterInterfaceMock->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterfaceMock);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->willReturnArgument(0);
    }
}
