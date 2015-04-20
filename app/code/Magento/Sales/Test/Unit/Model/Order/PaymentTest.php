<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentTest
 *
 * @package Magento\Sales\Model\Order
 */
class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /** @var Payment */
    private $payment;

    /** @var \Magento\Payment\Helper\Data | \PHPUnit_Framework_MockObject_MockObject */
    private $helperMock;

    /** @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject */
    private $eventManagerMock;

    /** @var \Magento\Directory\Model\PriceCurrency | \PHPUnit_Framework_MockObject_MockObject */
    private $priceCurrencyMock;

    /** @var \Magento\Sales\Model\Order | \PHPUnit_Framework_MockObject_MockObject $orderMock */
    private $orderMock;

    /** @var \Magento\Payment\Model\Method\AbstractMethod | \PHPUnit_Framework_MockObject_MockObject $orderMock */
    private $paymentMethodMock;

    /** @var \Magento\Sales\Model\Order\Invoice | \PHPUnit_Framework_MockObject_MockObject $orderMock */
    private $invoiceMock;

    private $transactionId;

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
                'setStore',
                'acceptPayment',
                'denyPayment',
                'fetchTransactionInfo',
            ])
            ->getMock();

        $this->invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods(['getTransactionId', 'load', 'getId', 'pay', 'getBaseGrandTotal', 'cancel'])
            ->getMock();

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
                'getInvoiceCollection',
                'addRelatedObject',
                'getState',
                'addStatusHistoryComment',
                'registerCancellation',
            ])
            ->getMock();

        $this->payment = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Sales\Model\Order\Payment',
            [
                'context'       => $context,
                'paymentData'   => $this->helperMock,
                'priceCurrency' => $this->priceCurrencyMock,
            ]
        );

        $this->payment->setMethod('any');
        $this->payment->setOrder($this->orderMock);

        $this->transactionId = 100;
    }

    protected function tearDown()
    {
        unset($this->payment);
    }

    public function testCancel()
    {
        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));
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

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

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

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

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

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

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

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

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

    public function testAcceptApprovePaymentTrue()
    {
        $baseGrandTotal = 300.00;
        $message = sprintf('Approved the payment online. Transaction ID: "%s"', $this->transactionId);
        $acceptPayment = true;

        $this->payment->setLastTransId($this->transactionId);

        $this->mockInvoice($this->transactionId);

        $this->mockResultTrueMethods($this->transactionId, $baseGrandTotal, $message);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $this->payment->accept();
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    public function acceptPaymentFalseProvider()
    {
        return [
            'Fraud = 1' => [
                true,
                Order::STATUS_FRAUD
            ],
            'Fraud = 0' => [
                false,
                false
            ],
        ];
    }

    /**
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     * @param bool $status
     */
    public function testAcceptApprovePaymentFalse($isFraudDetected, $status)
    {
        $message = sprintf('There is no need to approve this payment. Transaction ID: "%s"', $this->transactionId);
        $acceptPayment = false;
        $orderState = 'random_state';

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);


        $this->mockInvoice($this->transactionId);

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PAYMENT_REVIEW, $status, $message);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $this->payment->accept();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    /**
     *
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     */
    public function testAcceptApprovePaymentFalseOrderState($isFraudDetected)
    {
        $message = sprintf('There is no need to approve this payment. Transaction ID: "%s"', $this->transactionId);
        $acceptPayment = false;
        $orderState = Order::STATE_PAYMENT_REVIEW;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);


        $this->mockInvoice($this->transactionId);

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->never())
            ->method('setState');
        $this->orderMock->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $this->payment->accept();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    public function testDenyPaymentFalse()
    {
        $denyPayment = true;
        $message = sprintf('Denied the payment online Transaction ID: "%s"', $this->transactionId);

        $this->payment->setLastTransId($this->transactionId);

        $this->mockInvoice($this->transactionId);
        $this->mockResultFalseMethods($message);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $this->payment->deny();
    }

    /**
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     * @param bool $status
     */
    public function testDenyPaymentNegative($isFraudDetected, $status)
    {
        $denyPayment = false;
        $message = sprintf('There is no need to deny this payment. Transaction ID: "%s"', $this->transactionId);

        $orderState = 'random_state';

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);

        $this->mockInvoice($this->transactionId);

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PAYMENT_REVIEW, $status, $message);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $this->payment->deny();
    }

    public function testDenyPaymentNegativeStateReview()
    {
        $denyPayment = false;
        $message = sprintf('There is no need to deny this payment. Transaction ID: "%s"', $this->transactionId);

        $orderState = Order::STATE_PAYMENT_REVIEW;

        $this->payment->setLastTransId($this->transactionId);

        $this->mockInvoice($this->transactionId);

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->never())
            ->method('setState');
        $this->orderMock->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $this->payment->deny();
    }

    /**
     * @param int $transactionId
     */
    protected function mockInvoice($transactionId)
    {
        $this->invoiceMock->expects($this->once())
            ->method('getTransactionId')
            ->willReturn($transactionId);
        $this->invoiceMock->expects($this->once())
            ->method('load')
            ->with($transactionId);
        $this->invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($transactionId);
        $this->orderMock->expects($this->once())
            ->method('getInvoiceCollection')
            ->willReturn([$this->invoiceMock]);
    }

    public function testUpdateOnlineTransactionApproved()
    {
        $message = sprintf('Registered update about approved payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;
        $baseGrandTotal = 299.99;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_approved', true);

        $this->mockInvoice($this->transactionId);
        $this->mockResultTrueMethods($this->transactionId, $baseGrandTotal, $message);


        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));
        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethodMock);
        $this->paymentMethodMock->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    public function testUpdateOnlineTransactionDenied()
    {
        $message = sprintf('Registered update about denied payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_denied', true);

        $this->mockInvoice($this->transactionId);
        $this->mockResultFalseMethods($message);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));
        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethodMock);
        $this->paymentMethodMock->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
    }

    /**
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     * @param bool $status
     */
    public function testUpdateOnlineTransactionDeniedFalse($isFraudDetected, $status)
    {
        $message = sprintf('There is no update for the payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;
        $orderState = 'random_state';

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_denied', false);
        $this->payment->setData('is_transaction_approved', false);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);

        $this->mockInvoice($this->transactionId);

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PAYMENT_REVIEW, $status, $message);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));
        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethodMock);
        $this->paymentMethodMock->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    public function testUpdateOnlineTransactionDeniedFalseHistoryComment()
    {
        $message = sprintf('There is no update for the payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;
        $orderState = Order::STATE_PAYMENT_REVIEW;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_denied', false);
        $this->payment->setData('is_transaction_approved', false);

        $this->mockInvoice($this->transactionId);

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->never())
            ->method('setState');

        $this->orderMock->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));
        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethodMock);
        $this->paymentMethodMock->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    /**
     * @param int $transactionId
     * @param float $baseGrandTotal
     * @param string $message
     */
    protected function mockResultTrueMethods($transactionId, $baseGrandTotal, $message)
    {
        $this->invoiceMock->expects($this->once())
            ->method('pay')
            ->willReturn($transactionId);
        $this->invoiceMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);

        //acceptPayment = true
        $this->orderMock->expects($this->once())
            ->method('addRelatedObject')
            ->with($this->invoiceMock);
        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING, true, $message);
    }

    /**
     * @param $message
     */
    protected function mockResultFalseMethods($message)
    {
        $this->invoiceMock->expects($this->once())
            ->method('cancel');
        $this->orderMock->expects($this->once())
            ->method('addRelatedObject')
            ->with($this->invoiceMock);
        $this->orderMock->expects($this->once())
            ->method('registerCancellation')
            ->with($message, false);
    }

    public function testAcceptWithoutInvoiceResultTrue()
    {
        $baseGrandTotal = null;
        $acceptPayment = true;

        $this->payment->setData('transaction_id', $this->transactionId);

        $this->invoiceMock->expects($this->never())
            ->method('pay');

        $this->orderMock->expects($this->any())
            ->method('getInvoiceCollection')
            ->willReturn([]);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $this->payment->accept();
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    public function testDenyWithoutInvoiceResultFalse()
    {
        $baseGrandTotal = null;
        $denyPayment = true;

        $this->payment->setData('transaction_id', $this->transactionId);

        $this->invoiceMock->expects($this->never())
            ->method('cancel');

        $this->orderMock->expects($this->any())
            ->method('getInvoiceCollection')
            ->willReturn([]);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->paymentMethodMock->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethodMock->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $this->payment->deny();
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }
}
