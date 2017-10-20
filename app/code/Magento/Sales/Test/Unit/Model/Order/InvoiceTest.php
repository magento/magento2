<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\ResourceModel\OrderFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class InvoiceTest
 *
 * @package Magento\Sales\Model\Order
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $model;

    /**
     * Same as $model but Order was not set
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $modelWithoutOrder;

    /**
     * @var string
     */
    protected $entityType = 'invoice';

    /**
     * @var OrderFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Payment
     */
    protected $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\ManagerInterface
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helperManager;

    protected function setUp()
    {
        $this->helperManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getPayment', '__wakeup', 'load', 'setHistoryEntityName', 'getStore', 'getBillingAddress',
                    'getShippingAddress', 'getConfig',
                ]
            )
            ->getMock();
        $this->order->method('setHistoryEntityName')
            ->with($this->entityType)
            ->willReturnSelf();

        $this->paymentMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->setMethods(
            ['canVoid', '__wakeup', 'canCapture', 'capture', 'pay', 'cancelInvoice']
        )->getMock();

        $this->orderFactory = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);

        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $arguments = [
            'context' => $contextMock,
            'orderFactory' => $this->orderFactory,
            'calculatorFactory' => $this->createMock(\Magento\Framework\Math\CalculatorFactory::class),
            'invoiceItemCollectionFactory' => $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory::class),
            'invoiceCommentFactory' => $this->createMock(\Magento\Sales\Model\Order\Invoice\CommentFactory::class),
            'commentCollectionFactory' => $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory::class),
        ];
        $this->model = $this->helperManager->getObject(\Magento\Sales\Model\Order\Invoice::class, $arguments);
        $this->model->setOrder($this->order);
        $this->modelWithoutOrder = $this->helperManager->getObject(
            \Magento\Sales\Model\Order\Invoice::class,
            $arguments
        );
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testCanVoid($canVoid)
    {
        $this->order->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())
            ->method('canVoid', '__wakeup')
            ->willReturn($canVoid);

        $this->model->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
        $this->assertEquals($canVoid, $this->model->canVoid());
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testDefaultCanVoid($canVoid)
    {
        $this->model->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
        $this->model->setCanVoidFlag($canVoid);

        $this->assertEquals($canVoid, $this->model->canVoid());
    }

    public function canVoidDataProvider()
    {
        return [[true], [false]];
    }

    public function testGetOrder()
    {
        $this->order->expects($this->once())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->will($this->returnSelf());

        $this->assertEquals($this->order, $this->model->getOrder());
    }

    public function testGetOrderLoadedById()
    {
        $orderId = 100000041;
        $this->modelWithoutOrder->setOrderId($orderId);
        $this->order->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->willReturnSelf();
        $this->order->expects($this->once())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->willReturnSelf();
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->order);

        $this->assertEquals($this->order, $this->modelWithoutOrder->getOrder());
    }

    public function testGetEntityType()
    {
        $this->assertEquals($this->entityType, $this->model->getEntityType());
    }

    public function testGetIncrementId()
    {
        $this->model->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->model->getIncrementId());
    }

    public function testSetOrder()
    {
        $orderId = 1111;
        $storeId = 2221;
        $this->order->setId($orderId);
        $this->order->setStoreId($storeId);
        $this->assertNull($this->model->getOrderId());
        $this->assertNull($this->model->getStoreId());

        $this->assertEquals($this->model, $this->model->setOrder($this->order));
        $this->assertEquals($this->order, $this->model->getOrder());
        $this->assertEquals($orderId, $this->model->getOrderId());
        $this->assertEquals($storeId, $this->model->getStoreId());
    }

    public function testGetStore()
    {
        $store = $this->helperManager->getObject(\Magento\Store\Model\Store::class, []);
        $this->order->expects($this->once())->method('getStore')->willReturn($store);
        $this->assertEquals($store, $this->model->getStore());

    }

    public function testGetShippingAddress()
    {
        $address = $this->helperManager->getObject(\Magento\Sales\Model\Order\Address::class, []);
        $this->order->expects($this->once())->method('getShippingAddress')->willReturn($address);
        $this->assertEquals($address, $this->model->getShippingAddress());

    }

    /**
     * @dataProvider canCaptureDataProvider
     * @param string $state
     * @param bool|null $canPaymentCapture
     * @param bool $expectedResult
     */
    public function testCanCapture($state, $canPaymentCapture, $expectedResult)
    {
        $this->model->setState($state);
        if (null !== $canPaymentCapture) {
            $this->order->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
            $this->paymentMock->expects($this->once())->method('canCapture')->willReturn($canPaymentCapture);
        } else {
            $this->order->expects($this->never())->method('getPayment');
            $this->paymentMock->expects($this->never())->method('canCapture');
        }
        $this->assertEquals($expectedResult, $this->model->canCapture());
    }

    /**
     * Data provider for testCanCapture
     *
     * @return array
     */
    public function canCaptureDataProvider()
    {
        return [
            [Invoice::STATE_OPEN, true, true],
            [Invoice::STATE_OPEN, false, false],
            [Invoice::STATE_CANCELED, null, false],
            [Invoice::STATE_CANCELED, null, false],
            [Invoice::STATE_PAID, null, false],
            [Invoice::STATE_PAID, null, false]
        ];
    }

    /**
     * @dataProvider canCancelDataProvider
     * @param string $state
     * @param bool $expectedResult
     */
    public function testCanCancel($state, $expectedResult)
    {
        $this->model->setState($state);
        $this->assertEquals($expectedResult, $this->model->canCancel());
    }

    /**
     * Data provider for testCanCancel
     *
     * @return array
     */
    public function canCancelDataProvider()
    {
        return [
            [Invoice::STATE_OPEN, true],
            [Invoice::STATE_CANCELED, false],
            [Invoice::STATE_PAID, false]
        ];
    }

    /**
     * @dataProvider canRefundDataProvider
     * @param string $state
     * @param float $baseGrandTotal
     * @param float $baseTotalRefunded
     * @param bool $expectedResult
     */
    public function testCanRefund($state, $baseGrandTotal, $baseTotalRefunded, $expectedResult)
    {
        $this->model->setState($state);
        $this->model->setBaseGrandTotal($baseGrandTotal);
        $this->model->setBaseTotalRefunded($baseTotalRefunded);
        $this->assertEquals($expectedResult, $this->model->canRefund());
    }

    /**
     * Data provider for testCanRefund
     *
     * @return array
     */
    public function canRefundDataProvider()
    {
        return [
            [Invoice::STATE_OPEN, 0.00, 0.00, false],
            [Invoice::STATE_CANCELED, 1.00, 0.01, false],
            [Invoice::STATE_PAID, 1.00, 0.00, true],
            //[Invoice::STATE_PAID, 1.00, 1.00, false]
            [Invoice::STATE_PAID, 1.000101, 1.0000, true],
            [Invoice::STATE_PAID, 1.0001, 1.00, false],
            [Invoice::STATE_PAID, 1.00, 1.0001, false],
        ];
    }

    public function testCaptureNotPaid()
    {
        $this->model->setIsPaid(false);
        $this->order->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('capture')->with($this->model)->willReturnSelf();
        $this->paymentMock->expects($this->never())->method('pay');
        $this->assertEquals($this->model, $this->model->capture());
    }

    public function testCapturePaid()
    {
        $collection = $this->getOrderInvoiceCollection();
        $collection->method('getItems')
            ->willReturn([$this->model]);

        $this->model->setIsPaid(true);
        $this->order->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->paymentMock->method('capture')
            ->with($this->model)
            ->willReturnSelf();
        $this->mockPay();

        self::assertEquals($this->model, $this->model->capture());
    }

    public function mockPay()
    {
        $this->order->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('pay')->with($this->model)->willReturnSelf();
        $this->eventManagerMock
            ->expects($this->once())
            ->method('dispatch')
            ->with('sales_order_invoice_pay');
    }

    /**
     * @dataProvider payDataProvider
     * @param float $totalPaid
     * @param float $baseTotalPaid
     * @param float $expectedTotal
     * @param float $expectedBaseTotal
     * @param float $expectedState
     * @param array $items
     */
    public function testPay(
        $totalPaid,
        $baseTotalPaid,
        $expectedTotal,
        $expectedBaseTotal,
        $expectedState,
        array $items
    ) {
        $this->mockPay();
        $this->model->setGrandTotal($totalPaid);
        $this->model->setBaseGrandTotal($baseTotalPaid);
        $this->order->setTotalPaid($totalPaid);
        $this->order->setBaseTotalPaid($baseTotalPaid);
        $collection = $this->getOrderInvoiceCollection();
        $collection->method('getItems')
            ->willReturn($items);

        self::assertFalse($this->model->wasPayCalled());
        self::assertEquals($this->model, $this->model->pay());
        self::assertTrue($this->model->wasPayCalled());
        self::assertEquals($expectedState, $this->model->getState());

        #second call of pay() method must do nothing
        $this->model->pay();

        self::assertEquals($expectedBaseTotal, $this->order->getBaseTotalPaid());
        self::assertEquals($expectedTotal, $this->order->getTotalPaid());
    }

    public function payDataProvider()
    {
        return [
            [10.99, 1.00, 10.99, 1.00, Invoice::STATE_PAID, ['item1']],
            [11.00, 1.00, 22.00, 2.00, Invoice::STATE_PAID, ['item1', 'item2']],
        ];
    }

    /**
     * Creates collection of invoices for order.
     *
     * @return InvoiceCollection|MockObject
     */
    private function getOrderInvoiceCollection()
    {
        $collection = $this->getMockBuilder(InvoiceCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $refObject = new \ReflectionClass($this->order);
        $refProperty = $refObject->getProperty('_invoices');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->order, $collection);

        return $collection;
    }

    /**
     * Assert open invoice can be canceled, and its status changes
     */
    public function testCancelOpenInvoice()
    {
        $orderConfigMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->disableOriginalConstructor()->setMethods(
                ['getStateDefaultStatus']
            )->getMock();

        $orderConfigMock->expects($this->once())->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn(Order::STATE_PROCESSING);

        $this->order->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->order->expects($this->once())->method('getConfig')->willReturn($orderConfigMock);

        $this->paymentMock->expects($this->once())
            ->method('cancelInvoice')
            ->willReturn($this->paymentMock);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('sales_order_invoice_cancel');

        $this->model->setData(InvoiceInterface::ITEMS, []);
        $this->model->setState(Invoice::STATE_OPEN);
        $this->model->cancel();

        self::assertEquals(Invoice::STATE_CANCELED, $this->model->getState());
    }

    /**
     * Assert open invoice can be canceled, and its status changes
     *
     * @param $initialInvoiceStatus
     * @param $finalInvoiceStatus
     * @dataProvider getNotOpenedInvoiceStatuses
     */
    public function testCannotCancelNotOpenedInvoice($initialInvoiceStatus, $finalInvoiceStatus)
    {
        $this->order->expects($this->never())->method('getPayment');
        $this->paymentMock->expects($this->never())->method('cancelInvoice');
        $this->eventManagerMock->expects($this->never())
            ->method('dispatch')
            ->with('sales_order_invoice_cancel');

        $this->model->setState($initialInvoiceStatus);
        $this->model->cancel();

        self::assertEquals($finalInvoiceStatus, $this->model->getState());
    }

    /**
     * @return array
     */
    public function getNotOpenedInvoiceStatuses()
    {
        return [
            [Invoice::STATE_PAID, Invoice::STATE_PAID],
            [Invoice::STATE_CANCELED, Invoice::STATE_CANCELED],
        ];
    }
}
