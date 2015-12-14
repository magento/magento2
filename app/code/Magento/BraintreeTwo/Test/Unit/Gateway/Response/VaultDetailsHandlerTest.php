<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Braintree\Transaction\CreditCardDetails;
use Magento\BraintreeTwo\Gateway\Response\VaultDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Gateway\Config\Config;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * VaultDetailsHandler Test
 *
 * @see \Magento\BraintreeTwo\Gateway\Response\VaultDetailsHandler
 */
class VaultDetailsHandlerTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

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
        $this->paymentTokenFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->paymentTokenMock);

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactoryMock = $this
            ->getMockBuilder(OrderPaymentExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactoryMock->expects(self::once())
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
                'getMethod',
                'getOrder'
            ])
            ->getMock();

        $this->payment->expects(self::once())
            ->method('getOrder')
            ->willReturn($this->salesOrderMock);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentHandler = new VaultDetailsHandler(
            $this->configMock,
            $this->paymentTokenFactoryMock,
            $this->paymentExtensionFactoryMock
        );
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\VaultDetailsHandler::handle
     */
    public function testHandle()
    {
        $this->configMock->expects(self::at(0))
            ->method('getValue')
            ->with(Config::KEY_ACTIVE)
            ->willReturn(1);

        $this->configMock->expects(self::at(1))
            ->method('getValue')
            ->with(Config::KEY_VAULT_PAYMENT)
            ->willReturn('braintreetwo');

        $this->paymentExtension->expects(self::once())
            ->method('setVaultPaymentToken')
            ->with($this->paymentTokenMock);
        $this->paymentExtension->expects(self::once())
            ->method('getVaultPaymentToken')
            ->willReturn($this->paymentTokenMock);

        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $response = [
            'object' => $this->getBraintreeTransaction()
        ];

        $this->salesOrderMock->setCustomerId(10);

        $this->paymentHandler->handle($subject, $response);

        self::assertEquals('rh3gd4', $this->paymentTokenMock->getGatewayToken());
        self::assertEquals('10', $this->paymentTokenMock->getCustomerId());
        self::assertSame($this->paymentTokenMock, $this->payment->getExtensionAttributes()->getVaultPaymentToken());
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

        $mock->expects(self::once())
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
            'creditCardDetails' => $this->getCreditCardDetails()
        ];

        $transaction = Transaction::factory($attributes);

        $mock = $this->getMockBuilder(Successful::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $mock->expects(self::once())
            ->method('__get')
            ->with('transaction')
            ->willReturn($transaction);

        return $mock;
    }

    /**
     * Create Braintree transaction
     * @return \Braintree\Transaction\CreditCardDetails
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

        $creditCardDetails = new CreditCardDetails($attributes);
        return $creditCardDetails;
    }
}
