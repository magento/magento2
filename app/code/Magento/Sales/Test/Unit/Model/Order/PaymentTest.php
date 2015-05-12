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
     * @var \Magento\Directory\Model\Currency | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var \Magento\Sales\Model\Order | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodMock;

    /** @var \Magento\Sales\Model\Order\Invoice | \PHPUnit_Framework_MockObject_MockObject $orderMock */
    private $invoiceMock;

    private $transactionId;

    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Service\OrderFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceOrderFactory;

    /**
     * @var \Magento\Sales\Model\Service\Order | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceOrder;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditMemoMock;

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
        $this->currencyMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->setMethods(['formatTxt'])
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
            ->setMethods(
                [
                    'canVoid',
                    'authorize',
                    'getConfigData',
                    'getConfigPaymentAction',
                    'validate',
                    'setStore',
                    'acceptPayment',
                    'denyPayment',
                    'fetchTransactionInfo',
                    'canCapture',
                    'canRefund'
                ]
            )
            ->getMock();

        $this->invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getTransactionId',
                    'load',
                    'getId',
                    'pay',
                    'cancel',
                    'getGrandTotal',
                    'getBaseGrandTotal',
                    'getShippingAmount',
                    'getBaseShippingAmount'
                ]
            )
            ->getMock();
        $this->helperMock->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(
                [
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
                ]
            )
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
        $this->serviceOrderFactory = $this->getMock(
            'Magento\Sales\Model\Service\OrderFactory',
            [],
            [],
            '',
            false
        );
        $this->serviceOrder = $this->getMock(
            'Magento\Sales\Model\Service\Order',
            [],
            [],
            '',
            false
        );
        $this->creditMemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            [
                'setPaymentRefundDisallowed',
                'getItemsCollection',
                'getItems',
                'setAutomaticallyCreated',
                'register',
                'addComment',
                'save'
            ],
            [],
            '',
            false
        );

        $this->initPayment($context);
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
     * @param int $countCall
     */
    protected function mockInvoice($transactionId, $countCall = 1)
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
        $this->orderMock->expects($this->exactly($countCall))
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

    public function testPay()
    {
        $expects = [
            'amount_paid' => 10,
            'base_amount_paid' => 10,
            'shipping_captured' => 5,
            'base_shipping_captured' => 5,
        ];
        $this->assertNull($this->payment->getData('amount_paid'));
        $this->invoiceMock->expects($this->once())->method('getGrandTotal')->willReturn($expects['amount_paid']);
        $this->invoiceMock->expects($this->once())->method('getBaseGrandTotal')->willReturn(
            $expects['base_amount_paid']
        );
        $this->invoiceMock->expects($this->once())->method('getShippingAmount')->willReturn(
            $expects['shipping_captured']
        );
        $this->invoiceMock->expects($this->once())->method('getBaseShippingAmount')->willReturn(
            $expects['base_shipping_captured']
        );
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with(
            'sales_order_payment_pay',
            ['payment' => $this->payment, 'invoice' => $this->invoiceMock]
        );
        $this->assertSame($this->payment, $this->payment->pay($this->invoiceMock));
        $this->assertEquals($expects['amount_paid'], $this->payment->getData('amount_paid'));
        $this->assertEquals($expects['base_amount_paid'], $this->payment->getData('base_amount_paid'));
        $this->assertEquals($expects['shipping_captured'], $this->payment->getData('shipping_captured'));
        $this->assertEquals($expects['base_shipping_captured'], $this->payment->getData('base_shipping_captured'));
    }


    public function testCancelInvoice()
    {
        $expects = [
            'amount_paid' => 10,
            'base_amount_paid' => 10,
            'shipping_captured' => 5,
            'base_shipping_captured' => 5,
        ];
        $this->assertNull($this->payment->getData('amount_paid'));
        $this->invoiceMock->expects($this->once())->method('getGrandTotal')->willReturn($expects['amount_paid']);
        $this->invoiceMock->expects($this->once())->method('getBaseGrandTotal')->willReturn(
            $expects['base_amount_paid']
        );
        $this->invoiceMock->expects($this->once())->method('getShippingAmount')->willReturn(
            $expects['shipping_captured']
        );
        $this->invoiceMock->expects($this->once())->method('getBaseShippingAmount')->willReturn(
            $expects['base_shipping_captured']
        );
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with(
            'sales_order_payment_cancel_invoice',
            ['payment' => $this->payment, 'invoice' => $this->invoiceMock]
        );
        $this->assertSame($this->payment, $this->payment->cancelInvoice($this->invoiceMock));
        $this->assertEquals(-1 * $expects['amount_paid'], $this->payment->getData('amount_paid'));
        $this->assertEquals(-1 * $expects['base_amount_paid'], $this->payment->getData('base_amount_paid'));
        $this->assertEquals(-1 * $expects['shipping_captured'], $this->payment->getData('shipping_captured'));
        $this->assertEquals(
            -1 * $expects['base_shipping_captured'],
            $this->payment->getData('base_shipping_captured')
        );
    }

    public function testRegisterRefundNotificationTransactionExists()
    {
        $amount = 10;
        $this->payment->setParentTransactionId($this->transactionId);
        $transaction = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Transaction',
            ['setOrderPaymentObject', 'loadByTxnId', 'getId'],
            [],
            '',
            false
        );
        $this->transactionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($transaction);
        $transaction->expects($this->atLeastOnce())
            ->method('setOrderPaymentObject')
            ->with($this->payment)
            ->willReturnSelf();
        $transaction->expects($this->exactly(2))
            ->method('loadByTxnId')
            ->withConsecutive(
                [$this->transactionId],
                [$this->transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND]
            )->willReturnSelf();
        $transaction->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturnOnConsecutiveCalls(
                $this->transactionId,
                $this->transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND
            );
        $this->assertSame($this->payment, $this->payment->registerRefundNotification($amount));
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

    /**
     * @param $context
     */
    protected function initPayment($context)
    {
        $this->payment = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Sales\Model\Order\Payment',
            [
                'context' => $context,
                'serviceOrderFactory' => $this->serviceOrderFactory,
                'paymentData' => $this->helperMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'transactionFactory' => $this->transactionFactory,
                'transactionCollectionFactory' => $this->transactionCollectionFactory
            ]
        );

        $this->payment->setMethod('any');
        $this->payment->setOrder($this->orderMock);

        $this->transactionId = 100;
    }
}
