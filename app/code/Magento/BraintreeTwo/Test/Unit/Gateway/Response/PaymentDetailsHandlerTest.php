<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
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
                'setIsTransactionClosed'
            ])
            ->getMock();

        $this->payment->expects(static::once())
            ->method('setTransactionId');
        $this->payment->expects(static::once())
            ->method('setCcTransId');
        $this->payment->expects(static::once())
            ->method('setLastTransId');
        $this->payment->expects(static::once())
            ->method('setIsTransactionClosed');
        $this->payment->expects(static::any())
            ->method('setAdditionalInformation');

        $this->paymentHandler = new PaymentDetailsHandler();
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $response = [
            'object' => $this->getBraintreeTransaction()
        ];

        $this->paymentHandler->handle($subject, $response);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler::process3DSecure
     */
    public function testProcess3DSecure()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $response = [
            'object' => $this->getBraintreeTransaction()
        ];

        $this->payment->expects(static::at(4))
            ->method('setAdditionalInformation')
            ->with('liabilityShifted', true);
        $this->payment->expects(static::at(5))
            ->method('setAdditionalInformation')
            ->with('liabilityShiftPossible', true);

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
            'threeDSecureInfo' => $this->getThreeDSecureInfo()
        ];

        $transaction = \Braintree\Transaction::factory($attributes);

        $mock = $this->getMockBuilder(\Braintree\Result\Successful::class)
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
     * Get 3d secure details
     * @return array
     */
    private function getThreeDSecureInfo()
    {
        $attributes = [
            'liabilityShifted' => true,
            'liabilityShiftPossible' => true
        ];
        return $attributes;
    }
}
