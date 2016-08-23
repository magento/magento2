<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Braintree\Transaction\CreditCardDetails;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Response\VaultDetailsHandler;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * VaultDetailsHandler Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TRANSACTION_ID = '432erwwe';

    /**
     * @var \Magento\Braintree\Gateway\Response\PaymentDetailsHandler
     */
    private $paymentHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    /**
     * @var CreditCardTokenFactory|MockObject
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    protected $paymentToken;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtension|MockObject
     */
    private $paymentExtension;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory|MockObject
     */
    private $paymentExtensionFactory;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * @var Config|MockObject
     */
    private $config;

    protected function setUp()
    {
        $this->paymentToken = $this->getMock(PaymentTokenInterface::class);
        $this->paymentTokenFactory = $this->getMockBuilder(CreditCardTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactory->expects(self::once())
            ->method('create')
            ->willReturn($this->paymentToken);

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactory = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->paymentExtensionFactory->expects(self::once())
            ->method('create')
            ->willReturn($this->paymentExtension);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mapperArray = [
            "american-express" => "AE",
            "discover" => "DI",
            "jcb" => "JCB",
            "mastercard" => "MC",
            "master-card" => "MC",
            "visa" => "VI",
            "maestro" => "MI",
            "diners-club" => "DN",
            "unionpay" => "CUP"
        ];

        $this->config = $this->getMockBuilder(Config::class)
            ->setMethods(['getCctypesMapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects(self::once())
            ->method('getCctypesMapper')
            ->willReturn($mapperArray);

        $this->paymentHandler = new VaultDetailsHandler(
            $this->paymentTokenFactory,
            $this->paymentExtensionFactory,
            $this->config,
            $this->subjectReader
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\VaultDetailsHandler::handle
     */
    public function testHandle()
    {
        $this->paymentExtension->expects(self::once())
            ->method('setVaultPaymentToken')
            ->with($this->paymentToken);
        $this->paymentExtension->expects(self::once())
            ->method('getVaultPaymentToken')
            ->willReturn($this->paymentToken);

        $paymentData = $this->getPaymentDataObjectMock();
        $transaction = $this->getBraintreeTransaction();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $transaction];

        $this->subjectReader->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);
        $this->subjectReader->expects(self::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);
        $this->paymentToken->expects(static::once())
            ->method('setGatewayToken')
            ->with('rh3gd4');
        $this->paymentToken->expects(static::once())
            ->method('setExpiresAt')
            ->with('2022-01-01 00:00:00');

        $this->paymentHandler->handle($subject, $response);
        $this->assertSame($this->paymentToken, $this->payment->getExtensionAttributes()->getVaultPaymentToken());
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
            'creditCardDetails' => $this->getCreditCardDetails()
        ];

        $transaction = Transaction::factory($attributes);

        return $transaction;
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
            'expirationYear' => 2021,
            'last4' => 1231
        ];

        $creditCardDetails = new CreditCardDetails($attributes);

        return $creditCardDetails;
    }
}
