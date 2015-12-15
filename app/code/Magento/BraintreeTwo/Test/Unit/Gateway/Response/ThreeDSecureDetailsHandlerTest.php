<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Response\ThreeDSecureDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ThreeDSecureDetailsHandlerTest
 */
class ThreeDSecureDetailsHandlerTest extends \PHPUnit_Framework_TestCase
{

    const TRANSACTION_ID = '432er5ww3e';

    /**
     * @var \Magento\BraintreeTwo\Gateway\Response\ThreeDSecureDetailsHandler
     */
    private $handler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'unsAdditionalInformation',
                'hasAdditionalInformation',
                'setAdditionalInformation',
            ])
            ->getMock();

        $this->handler = new ThreeDSecureDetailsHandler();
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\ThreeDSecureDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $response = [
            'object' => $this->getBraintreeTransaction()
        ];

        $this->payment->expects(static::at(1))
            ->method('setAdditionalInformation')
            ->with('liabilityShifted', true);
        $this->payment->expects(static::at(2))
            ->method('setAdditionalInformation')
            ->with('liabilityShiftPossible', true);

        $this->handler->handle($subject, $response);
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
            'threeDSecureInfo' => $this->getThreeDSecureInfo()
        ];

        $transaction = Transaction::factory($attributes);

        $mock = $this->getMockBuilder(Successful::class)
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
