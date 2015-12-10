<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Braintree_Transaction;
use Braintree_Result_Successful;
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

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTransactionId',
                'setCcTransId',
                'setLastTransId',
                'setAdditionalInformation',
                'setIsTransactionClosed',
                'getMethod'
            ])
            ->getMock();

        $this->paymentHandler = new PaymentDetailsHandler();
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

        $this->paymentHandler->handle($subject, $response);
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

        $mock->expects($this->once())
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

        $mock->expects($this->once())
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
