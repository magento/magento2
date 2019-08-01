<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Braintree\Transaction\CreditCardDetails;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Braintree\Gateway\Response\VaultDetailsHandler;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Model\PaymentToken;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * VaultDetailsHandler Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends TestCase
{
    private static $transactionId = '432erwwe';

    private static $token = 'rh3gd4';

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
     * @var OrderPaymentExtension|MockObject
     */
    private $paymentExtension;

    /**
     * @var OrderPaymentExtensionInterfaceFactory|MockObject
     */
    private $paymentExtensionFactory;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $paymentToken = $objectManager->getObject(PaymentToken::class);
        $this->paymentTokenFactory = $this->getMockBuilder(PaymentTokenFactoryInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactory->method('create')
            ->with(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD)
            ->willReturn($paymentToken);

        $this->initPaymentExtensionAttributesMock();
        $this->paymentExtension->method('setVaultPaymentToken')
            ->with($paymentToken);
        $this->paymentExtension->method('getVaultPaymentToken')
            ->willReturn($paymentToken);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getExtensionAttributes'])
            ->getMock();

        $this->payment->expects(self::any())->method('getExtensionAttributes')->willReturn($this->paymentExtension);

        $config = $this->getConfigMock();

        $this->paymentHandler = new VaultDetailsHandler(
            $this->paymentTokenFactory,
            $this->paymentExtensionFactory,
            $config,
            new SubjectReader(),
            new Json()
        );
    }

    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $this->getBraintreeTransaction()];

        $this->paymentHandler->handle($subject, $response);
        $paymentToken = $this->payment->getExtensionAttributes()
            ->getVaultPaymentToken();

        self::assertEquals(self::$token, $paymentToken->getGatewayToken());
        self::assertEquals('2022-01-01 00:00:00', $paymentToken->getExpiresAt());

        $details = json_decode($paymentToken->getTokenDetails(), true);
        self::assertEquals(
            [
                'type' => 'AE',
                'maskedCC' => 1231,
                'expirationDate' => '12/2021'
            ],
            $details
        );
    }

    /**
     * Creates mock for payment data object and order payment.
     *
     * @return PaymentDataObject|MockObject
     */
    private function getPaymentDataObjectMock(): PaymentDataObject
    {
        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    /**
     * Creates Braintree transaction.
     *
     * @return Successful
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'id' => self::$transactionId,
            'creditCardDetails' => $this->getCreditCardDetails()
        ];

        $transaction = Transaction::factory($attributes);
        $result = new Successful(['transaction' => $transaction]);

        return $result;
    }

    /**
     * Creates Braintree transaction.
     *
     * @return CreditCardDetails
     */
    private function getCreditCardDetails(): CreditCardDetails
    {
        $attributes = [
            'token' => self::$token,
            'bin' => '5421',
            'cardType' => 'American Express',
            'expirationMonth' => 12,
            'expirationYear' => 2021,
            'last4' => 1231
        ];

        $creditCardDetails = new CreditCardDetails($attributes);

        return $creditCardDetails;
    }

    /**
     * Creates mock of config class.
     *
     * @return Config|MockObject
     */
    private function getConfigMock(): Config
    {
        $mapperArray = [
            'american-express' => 'AE',
            'discover' => 'DI',
            'jcb' => 'JCB',
            'mastercard' => 'MC',
            'master-card' => 'MC',
            'visa' => 'VI',
            'maestro' => 'MI',
            'diners-club' => 'DN',
            'unionpay' => 'CUP'
        ];

        $config = $this->getMockBuilder(Config::class)
            ->setMethods(['getCctypesMapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $config->method('getCctypesMapper')
            ->willReturn($mapperArray);

        return $config;
    }

    /**
     * Initializes payment extension attributes mocks.
     *
     * @return void
     */
    private function initPaymentExtensionAttributesMock()
    {
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
    }
}
