<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Response\CaptureDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CaptureDetailsHandlerTest
 */
class CaptureDetailsHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\BraintreeTwo\Gateway\Response\CaptureDetailsHandler
     */
    private $captureHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setIsTransactionClosed'
            ])
            ->getMock();

        $this->captureHandler = new CaptureDetailsHandler();
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\CaptureDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->payment->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(false);

        $response = [
            'object' => [
                'success' => true
            ]
        ];

        $this->captureHandler->handle($subject, $response);
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

    private function getTransactionDetails()
    {
        $attrs = [
            ''
        ];
    }
}
