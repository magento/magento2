<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Braintree_Transaction;
use Braintree_Result_Successful;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;


/**
 * Class PaymentDetailsHandlerTest
 * @package Magento\BraintreeTwo\Test\Unit\Gateway\Response
 */
class PaymentDetailsHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TRANSACTION_ID = '432erwwe';

    /**
     * @var \Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler
     */
    private $paymentHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    /**
     * @var \Magento\Vault\Model\PaymentTokenFactory|MockObject paymentTokenFactoryMock
     */
    protected $paymentTokenFactoryMock;

    /**
     * @var \Magento\Vault\Model\PaymentToken|MockObject paymentTokenMock
     */
    protected $paymentTokenMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtension|MockObject paymentExtension
     */
    protected $paymentExtension;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionFactory|MockObject paymentExtensionFactoryMock
     */
    protected $paymentExtensionFactoryMock;

    /**
     * @var \Magento\Sales\Model\Order|MockObject salesOrderMock
     */
    protected $salesOrderMock;

    protected function setUp()
    {
        $this->paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactoryMock = $this->getMockBuilder(PaymentTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->paymentTokenMock);

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactoryMock = $this
            ->getMockBuilder(OrderPaymentExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->paymentExtension);

        // Sales Order Model
        $this->salesOrderMock = $this->getMockBuilder(Order::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTransactionId',
                'setCcTransId',
                'setLastTransId',
                'setAdditionalInformation',
                'setIsTransactionClosed',
                'getMethod',
                'getOrder'
            ])
            ->getMock();

        $this->payment->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->salesOrderMock);

        $this->paymentHandler = new PaymentDetailsHandler(
            $this->paymentTokenFactoryMock,
            $this->paymentExtensionFactoryMock
        );
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->payment->expects(static::once())
            ->method('setTransactionId');
        $this->payment->expects(static::once())
            ->method('setCcTransId');
        $this->payment->expects(static::once())
            ->method('setLastTransId');
        $this->payment->expects(static::once())
            ->method('setIsTransactionClosed');
        $this->payment->expects(static::exactly(6))
            ->method('setAdditionalInformation');

        $response = [
            'object' => $this->getBraintreeTransaction()
        ];

        $this->salesOrderMock->setCustomerId(10);

        $this->paymentHandler->handle($subject, $response);

        $this->assertEquals('rh3gd4', $this->paymentTokenMock->getGatewayToken());
        $this->assertEquals('10', $this->paymentTokenMock->getCustomerId());
        $this->assertSame($this->paymentTokenMock, $this->payment->getExtensionAttributes()->getVaultPaymentToken());
    }

    /**
     * Create mock for payment data object and order payment
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    /**
     * Create Braintree transaction
     * @return MockObject
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'id' => self::TRANSACTION_ID,
            'avsPostalCodeResponseCode' => 'M',
            'avsStreetAddressResponseCode' => 'M',
            'cvvResponseCode' => 'M',
            'processorAuthorizationCode' => 'W1V8XK',
            'processorResponseCode' => '1000',
            'processorResponseText' => 'Approved',
            'creditCardDetails' => $this->getCreditCardDetails()
        ];

        $transaction = Braintree_Transaction::factory($attributes);

        $mock = $this->getMockBuilder(Braintree_Result_Successful::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $mock->expects(static::once())
            ->method('__get')
            ->with('transaction')
            ->willReturn($transaction);

        return $mock;
    }

    /**
     * Create Braintree transaction
     * @return \Braintree_Transaction_CreditCardDetails
     */
    private function getCreditCardDetails()
    {
        $attributes = [
            'token' => 'rh3gd4',
            'bin' => '5421',
            'cardType' => 'American Express',
            'expirationMonth' => 12,
            'expirationYear' => 21,
            'last4' => 1231
        ];

        $creditCardDetails = new \Braintree_Transaction_CreditCardDetails($attributes);
        return $creditCardDetails;
    }
}
