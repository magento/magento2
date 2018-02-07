<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Braintree\Transaction\CreditCardDetails;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Braintree\Gateway\Response\VaultDetailsHandler;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * VaultDetailsHandler Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends TestCase
{
    const TRANSACTION_ID = '432erwwe';

    /**
     * @var PaymentDetailsHandler
     */
    private $paymentHandler;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var PaymentTokenFactoryInterface|MockObject
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    private $paymentToken;

    /**
     * @var OrderPaymentExtension|MockObject
     */
    private $paymentExtension;

    /**
     * @var OrderPaymentExtensionInterfaceFactory|MockObject
     */
    private $paymentExtensionFactory;

    /**
     * @var Config|MockObject
     */
    private $config;

    protected function setUp()
    {
        $this->paymentToken = $this->createMock(PaymentTokenInterface::class);
        $this->paymentTokenFactory = $this->getMockBuilder(PaymentTokenFactoryInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactory->method('create')
            ->willReturn($this->paymentToken);

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactory = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->paymentExtensionFactory->method('create')
            ->willReturn($this->paymentExtension);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
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

        $this->config->method('getCctypesMapper')
            ->willReturn($mapperArray);

        $this->paymentHandler = new VaultDetailsHandler(
            $this->paymentTokenFactory,
            $this->paymentExtensionFactory,
            $this->config,
            new SubjectReader(),
            new Json()
        );
    }

    public function testHandle()
    {
        $this->paymentExtension->method('setVaultPaymentToken')
            ->with($this->paymentToken);
        $this->paymentExtension->method('getVaultPaymentToken')
            ->willReturn($this->paymentToken);

        $paymentData = $this->getPaymentDataObjectMock();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $this->getBraintreeTransaction()];

        $this->paymentToken->method('setGatewayToken')
            ->with('rh3gd4');
        $this->paymentToken->method('setExpiresAt')
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
     * Creates Braintree transaction
     * @return \stdClass
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'id' => self::TRANSACTION_ID,
            'creditCardDetails' => $this->getCreditCardDetails()
        ];

        $transaction = Transaction::factory($attributes);
        $obj = new \stdClass();
        $obj->transaction = $transaction;

        return $obj;
    }

    /**
     * Create Braintree transaction
     * @return CreditCardDetails
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
