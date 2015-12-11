<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree_Result_Successful;
use Braintree_Transaction;
use Magento\BraintreeTwo\Gateway\Response\CardDetailsHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use Magento\BraintreeTwo\Gateway\Config\Config;

/**
 * Class CardDetailsHandlerTest
 * @package Magento\BraintreeTwo\Test\Unit\Gateway\Response
 */
class CardDetailsHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\BraintreeTwo\Gateway\Response\CardDetailsHandler
     */
    private $cardHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var \Magento\BraintreeTwo\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    protected function setUp()
    {
        $this->initConfigMock();

        $helper = new ObjectManager($this);
        $this->cardHandler = $helper->getObject(CardDetailsHandler::class, ['config' => $this->config]);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\CardDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $response = [
            'object' => $this->getBraintreeTransaction()
        ];

        $this->payment->expects(static::once())
            ->method('setCcLast4');
        $this->payment->expects(static::once())
            ->method('setCcExpMonth');
        $this->payment->expects(static::once())
            ->method('setCcExpYear');
        $this->payment->expects(static::once())
            ->method('setCcType');
        $this->payment->expects(static::exactly(2))
            ->method('setAdditionalInformation');

        $this->cardHandler->handle($subject, $response);
    }

    /**
     * Create mock for gateway config
     */
    private function initConfigMock()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCctypesMapper'])
            ->getMock();

        $this->config->expects(static::once())
            ->method('getCctypesMapper')
            ->willReturn([
                'american-express' => 'AE',
                'discover' => 'DI',
                'jcb' => 'JCB',
                'mastercard' => 'MC',
                'master-card' => 'MC',
                'visa' => 'VI'
            ]);
    }

    /**
     * Create mock for payment data object and order payment
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcLast4',
                'setCcExpMonth',
                'setCcExpYear',
                'setCcType',
                'setAdditionalInformation',
            ])
            ->getMock();

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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'creditCard' => [
                'bin' => '5421',
                'cardType' => 'American Express',
                'expirationMonth' => 12,
                'expirationYear' => 21,
                'last4' => 1231
            ]
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
}
