<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use \Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentTest
 *
 * @package Magento\Sales\Model\Order
 */
class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var \Magento\Payment\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Directory\Model\PriceCurrency | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \Magento\Sales\Model\Order | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionCollectionFactory;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManagerMock));

        $this->helperMock = $this->getMockBuilder('Magento\Payment\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $this->priceCurrencyMock = $this->getMockBuilder('Magento\Directory\Model\PriceCurrency')
            ->disableOriginalConstructor()
            ->setMethods(['format'])
            ->getMock();

        $this->priceCurrencyMock->expects($this->any())
            ->method('format')
            ->willReturnCallback(
                function ($value) {
                    return $value;
                }
            );

        $this->paymentMethodMock = $this->getMockBuilder('Magento\Payment\Model\Method\AbstractMethod')
            ->disableOriginalConstructor()
            ->setMethods([
                'canVoid',
                'authorize',
                'getConfigData',
                'getConfigPaymentAction',
                'validate',
                'canCapture',
                'canRefund'
            ])
            ->getMock();

        $this->helperMock->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConfig',
                'setState',
                'getStoreId',
                'getBaseGrandTotal',
                'getBaseCurrency',
                'getBaseCurrencyCode',
                'getTotalDue',
                'getBaseTotalDue',
            ])
            ->getMock();

        $this->transactionFactory = $this->getMock(
            'Magento\Sales\Model\Order\Payment\TransactionFactory',
            [],
            [],
            '',
            false
        );
        $this->transactionCollectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory',
            [],
            [],
            '',
            false
        );

        $this->payment = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Sales\Model\Order\Payment',
            [
                'context'       => $context,
                'paymentData'   => $this->helperMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'transactionFactory' => $this->transactionFactory,
                'transactionCollectionFactory' => $this->transactionCollectionFactory
            ]
        );

        $this->payment->setMethod('any');
        $this->payment->setOrder($this->orderMock);
    }

    protected function tearDown()
    {
        unset($this->payment);
    }

    public function testCancel()
    {
        // check fix for partial refunds in Payflow Pro
        $this->paymentMethodMock->expects($this->once())
            ->method('canVoid')
            ->with($this->payment)
            ->willReturn(false);

        $this->assertEquals($this->payment, $this->payment->cancel());
    }

    public function testPlace()
    {
        $newOrderStatus = 'new_status';

        /** @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject $orderConfigMock */
        $orderConfigMock = $this->getMockBuilder('Magento\Sales\Model\Order\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getStateStatuses', 'getStateDefaultStatus'])
            ->getMock();

        $orderConfigMock->expects($this->once())
            ->method('getStateStatuses')
            ->with(\Magento\Sales\Model\Order::STATE_NEW)
            ->will($this->returnValue(['firstStatus', 'secondStatus']));

        $orderConfigMock->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(\Magento\Sales\Model\Order::STATE_NEW)
            ->will($this->returnValue($newOrderStatus));

        $this->orderMock->expects($this->exactly(2))
            ->method('getConfig')
            ->will($this->returnValue($orderConfigMock));

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(\Magento\Sales\Model\Order::STATE_NEW, $newOrderStatus);

        $this->paymentMethodMock->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(null);

        $this->eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('sales_order_payment_place_start', ['payment' => $this->payment]);

        $this->eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('sales_order_payment_place_end', ['payment' => $this->payment]);

        $this->assertEquals($this->payment, $this->payment->place());
    }

    public function testAuthorize()
    {
        $storeID = 1;
        $amount = 10;

        $baseCurrencyMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->setMethods(['formatTxt'])
            ->getMock();

        $baseCurrencyMock->expects($this->once())
            ->method('formatTxt')
            ->willReturnCallback(
                function ($value) {
                    return $value;
                }
            );

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeID);

        $this->orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($amount);

        $this->orderMock->expects($this->once())
            ->method('getBaseCurrency')
            ->willReturn($baseCurrencyMock);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(\Magento\Sales\Model\Order::STATE_PROCESSING, true, 'Authorized amount of ' . $amount);

        $this->paymentMethodMock->expects($this->once())
            ->method('authorize')
            ->with($this->payment)
            ->willReturnSelf();

        $paymentResult = $this->payment->authorize(true, $amount);

        $this->assertInstanceOf('Magento\Sales\Model\Order\Payment', $paymentResult);
        $this->assertEquals($amount, $paymentResult->getBaseAmountAuthorized());
    }

    public function testAuthorizeFraudDetected()
    {
        $storeID = 1;
        $amount = 10;

        $baseCurrencyMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->setMethods(['formatTxt'])
            ->getMock();

        $baseCurrencyMock->expects($this->once())
            ->method('formatTxt')
            ->willReturnCallback(
                function ($value) {
                    return $value;
                }
            );

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeID);

        $this->orderMock->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn("USD");

        $this->orderMock->expects($this->once())
            ->method('getBaseCurrency')
            ->willReturn($baseCurrencyMock);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(
                \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
                \Magento\Sales\Model\Order::STATUS_FRAUD,
                "Order is suspended as its authorizing amount $amount is suspected to be fraudulent."
            );

        $this->paymentMethodMock->expects($this->once())
            ->method('authorize')
            ->with($this->payment)
            ->willReturnSelf();

        $this->payment->setCurrencyCode('GBP');

        $paymentResult = $this->payment->authorize(true, $amount);

        $this->assertInstanceOf('Magento\Sales\Model\Order\Payment', $paymentResult);
        $this->assertEquals($amount, $paymentResult->getBaseAmountAuthorized());
        $this->assertTrue($paymentResult->getIsFraudDetected());
    }

    public function testAuthorizeTransactionPending()
    {
        $storeID = 1;
        $amount = 10;

        $baseCurrencyMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->setMethods(['formatTxt'])
            ->getMock();

        $baseCurrencyMock->expects($this->once())
            ->method('formatTxt')
            ->willReturnCallback(
                function ($value) {
                    return $value;
                }
            );

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeID);

        $this->orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($amount);

        $this->orderMock->expects($this->once())
            ->method('getBaseCurrency')
            ->willReturn($baseCurrencyMock);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(
                \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
                true,
                "We will authorize $amount after the payment is approved at the payment gateway."
            );

        $this->paymentMethodMock->expects($this->once())
            ->method('authorize')
            ->with($this->payment)
            ->willReturnSelf();

        $this->payment->setIsTransactionPending(true);

        $paymentResult = $this->payment->authorize(true, $amount);

        $this->assertInstanceOf('Magento\Sales\Model\Order\Payment', $paymentResult);
        $this->assertEquals($amount, $paymentResult->getBaseAmountAuthorized());
        $this->assertTrue($paymentResult->getIsTransactionPending());
    }

    public function testCanCaptureNoAuthorizationTransaction()
    {
        $this->paymentMethodMock->expects($this->once())
            ->method('canCapture')
            ->willReturn(true);
        $this->assertTrue($this->payment->canCapture());
    }

    public function testCanCaptureCreateTransaction()
    {
        $this->paymentMethodMock->expects($this->once())
            ->method('canCapture')
            ->willReturn(true);

        $parentTransactionId = 1;
        $this->payment->setParentTransactionId($parentTransactionId);

        $transaction = $this->getMock('Magento\Sales\Model\Order\Payment\Transaction', [], [], '', false);
        $transaction->expects($this->once())
            ->method('setOrderPaymentObject')
            ->willReturnSelf();
        $transaction->expects($this->once())
            ->method('loadByTxnId')
            ->willReturnSelf();
        $transaction->expects($this->once())
            ->method('getId')
            ->willReturn($parentTransactionId);

        $transaction->expects($this->once())
            ->method('getIsClosed')
            ->willReturn(false);

        $this->transactionFactory->expects($this->once())
            ->method('create')
            ->willReturn($transaction);

        $this->assertTrue($this->payment->canCapture());
    }

    public function testCanCaptureAuthorizationTransaction()
    {
        $paymentId = 1;
        $this->payment->setId($paymentId);

        $this->paymentMethodMock->expects($this->once())
            ->method('canCapture')
            ->willReturn(true);

        $transaction = $this->getMock('Magento\Sales\Model\Order\Payment\Transaction', [], [], '', false);
        $collection = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Transaction\Collection',
            [],
            [],
            '',
            false
        );
        $this->transactionCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $collection->expects($this->once())
            ->method('setOrderFilter')
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addPaymentIdFilter')
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addTxnTypeFilter')
            ->willReturnSelf();
        $collection->method('setOrder')
            ->willReturnMap(
                [
                    ['created_at', \Magento\Framework\Data\Collection::SORT_ORDER_DESC, $collection],
                    ['transaction_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC, [$transaction]]
                ]
            );

        $this->assertTrue($this->payment->canCapture());
    }

    /**
     * @dataProvider boolProvider
     */
    public function testCanRefund($canRefund)
    {
        $this->paymentMethodMock->expects($this->once())
            ->method('canRefund')
            ->willReturn($canRefund);
        $this->assertEquals($canRefund, $this->payment->canRefund());
    }

    public function boolProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
