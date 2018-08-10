<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\OrderFactory;

/**
 * Class InvoiceTest
 *
 * @package Magento\Sales\Model\Order
 */
class InvoiceTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order
     */
    protected $orderMock;

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
        $this->orderMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order'
        )->disableOriginalConstructor()->setMethods(
            [
                'getPayment', '__wakeup', 'load', 'setHistoryEntityName', 'getStore', 'getBillingAddress',
                'getShippingAddress'
            ]
        )->getMock();
        $this->orderMock->expects($this->any())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->will($this->returnSelf());

        $this->paymentMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment'
        )->disableOriginalConstructor()->setMethods(
            ['canVoid', '__wakeup', 'canCapture', 'capture', 'pay']
        )->getMock();

        $this->orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);

        $this->eventManagerMock = $this->getMock('\Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $contextMock = $this->getMock('\Magento\Framework\Model\Context', [], [], '', false);
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $arguments = [
            'context' => $contextMock,
            'orderFactory' => $this->orderFactory,
            'orderResourceFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\OrderFactory',
                [],
                [],
                '',
                false
            ),
            'calculatorFactory' => $this->getMock(
                    'Magento\Framework\Math\CalculatorFactory',
                    [],
                    [],
                    '',
                    false
                ),
            'invoiceItemCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory',
                [],
                [],
                '',
                false
            ),
            'invoiceCommentFactory' => $this->getMock(
                'Magento\Sales\Model\Order\Invoice\CommentFactory',
                [],
                [],
                '',
                false
            ),
            'commentCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory',
                [],
                [],
                '',
                false
            ),
        ];
        $this->model = $this->helperManager->getObject('Magento\Sales\Model\Order\Invoice', $arguments);
        $this->model->setOrder($this->orderMock);
        $this->modelWithoutOrder = $this->helperManager->getObject('Magento\Sales\Model\Order\Invoice', $arguments);
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testCanVoid($canVoid)
    {
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
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

    /**
     * @return array
     */
    public function canVoidDataProvider()
    {
        return [[true], [false]];
    }

    public function testGetOrder()
    {
        $this->orderMock->expects($this->once())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->will($this->returnSelf());

        $this->assertEquals($this->orderMock, $this->model->getOrder());
    }

    public function testGetOrderLoadedById()
    {
        $orderId = 100000041;
        $this->modelWithoutOrder->setOrderId($orderId);
        $this->orderMock->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->willReturnSelf();
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->orderMock);

        $this->assertEquals($this->orderMock, $this->modelWithoutOrder->getOrder());
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
        $this->orderMock->setId($orderId);
        $this->orderMock->setStoreId($storeId);
        $this->assertNull($this->model->getOrderId());
        $this->assertNull($this->model->getStoreId());

        $this->assertEquals($this->model, $this->model->setOrder($this->orderMock));
        $this->assertEquals($this->orderMock, $this->model->getOrder());
        $this->assertEquals($orderId, $this->model->getOrderId());
        $this->assertEquals($storeId, $this->model->getStoreId());
    }

    public function testGetStore()
    {
        $store = $this->helperManager->getObject('\Magento\Store\Model\Store', []);
        $this->orderMock->expects($this->once())->method('getStore')->willReturn($store);
        $this->assertEquals($store, $this->model->getStore());

    }

    public function testGetShippingAddress()
    {
        $address = $this->helperManager->getObject('\Magento\Sales\Model\Order\Address', []);
        $this->orderMock->expects($this->once())->method('getShippingAddress')->willReturn($address);
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
            $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
            $this->paymentMock->expects($this->once())->method('canCapture')->willReturn($canPaymentCapture);
        } else {
            $this->orderMock->expects($this->never())->method('getPayment');
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
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('capture')->with($this->model)->willReturnSelf();
        $this->paymentMock->expects($this->never())->method('pay');
        $this->assertEquals($this->model, $this->model->capture());
    }

    public function testCapturePaid()
    {
        $this->model->setIsPaid(true);
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->any())->method('capture')->with($this->model)->willReturnSelf();
        $this->mockPay();
        $this->assertEquals($this->model, $this->model->capture());
    }

    public function mockPay()
    {
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())->method('pay')->with($this->model)->willReturnSelf();
        $this->eventManagerMock
            ->expects($this->once())
            ->method('dispatch')
            ->with('sales_order_invoice_pay');
    }

    /**
     * @dataProvider payDataProvider
     * @param float $orderTotalPaid
     * @param float $orderBaseTotalPaid
     * @param float $grandTotal
     * @param float $baseGrandTotal
     * @param float $expectedState
     */
    public function testPay(
        $orderTotalPaid,
        $orderBaseTotalPaid,
        $grandTotal,
        $baseGrandTotal,
        $expectedState
    ) {
        $this->mockPay();
        $this->model->setGrandTotal($grandTotal);
        $this->model->setBaseGrandTotal($baseGrandTotal);
        $this->orderMock->setTotalPaid($orderTotalPaid);
        $this->orderMock->setBaseTotalPaid($orderBaseTotalPaid);
        $this->assertFalse($this->model->wasPayCalled());
        $this->assertEquals($this->model, $this->model->pay());
        $this->assertTrue($this->model->wasPayCalled());
        $this->assertEquals($expectedState, $this->model->getState());
        #second call of pay() method must do nothing
        $this->model->pay();
    }

    /**
     * @return array
     */
    public function payDataProvider()
    {
        //ToDo: fill data provider and uncomment assertings totals in testPay
        return [
            [10.99, 1.00, 10.99, 1.00, Invoice::STATE_PAID]
        ];
    }
}
