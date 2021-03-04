<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response\PayPal;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Braintree\Transaction\PayPalDetails;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentToken;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends TestCase
{
    private static $transactionId = '1n2suy';

    private static $token = 'rc39al';

    private static $payerEmail = 'john.doe@example.com';

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentInfoMock;

    /**
     * @var PaymentTokenFactoryInterface|MockObject
     */
    private $paymentTokenFactoryMock;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    protected $paymentTokenMock;

    /**
     * @var OrderPaymentExtension|MockObject
     */
    private $paymentExtensionMock;

    /**
     * @var OrderPaymentExtensionInterfaceFactory|MockObject
     */
    private $paymentExtensionFactoryMock;

    /**
     * @var VaultDetailsHandler
     */
    private $handler;

    /**
     * @var DateTimeFactory|MockObject
     */
    private $dateTimeFactoryMock;

    /**
     * @var array
     */
    private $subject = [];

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->paymentDataObjectMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->paymentInfoMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getExtensionAttributes'])
            ->getMock();

        $this->paymentTokenMock = $objectManager->getObject(PaymentToken::class);

        $this->paymentTokenFactoryMock = $this->getMockBuilder(PaymentTokenFactoryInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentExtensionMock = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->paymentExtensionFactoryMock = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->paymentInfoMock->expects(self::any())
            ->method('getExtensionAttributes')
            ->willReturn($this->paymentExtensionMock);

        $this->subject = [
            'payment' => $this->paymentDataObjectMock,
        ];

        $this->dateTimeFactoryMock = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        
        $this->handler = new VaultDetailsHandler(
            $this->paymentTokenFactoryMock,
            $this->paymentExtensionFactoryMock,
            new SubjectReader(),
            $this->dateTimeFactoryMock
        );
    }

    public function testHandle()
    {
        $transaction = $this->getTransaction();
        $response = [
            'object' => $transaction
        ];

        $this->paymentExtensionMock->method('setVaultPaymentToken')
            ->with($this->paymentTokenMock);
        $this->paymentExtensionMock->method('getVaultPaymentToken')
            ->willReturn($this->paymentTokenMock);
        
        $this->paymentDataObjectMock->method('getPayment')
            ->willReturn($this->paymentInfoMock);

        $this->paymentTokenFactoryMock->method('create')
            ->with(PaymentTokenFactoryInterface::TOKEN_TYPE_ACCOUNT)
            ->willReturn($this->paymentTokenMock);

        $this->paymentExtensionFactoryMock->method('create')
            ->willReturn($this->paymentExtensionMock);

        $dateTime = new \DateTime('2016-07-05 00:00:00', new \DateTimeZone('UTC'));
        $expirationDate = '2017-07-05 00:00:00';
        $this->dateTimeFactoryMock->method('create')
            ->willReturn($dateTime);
        
        $this->handler->handle($this->subject, $response);

        $extensionAttributes = $this->paymentInfoMock->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        self::assertNotNull($paymentToken);

        $tokenDetails = json_decode($paymentToken->getTokenDetails(), true);

        self::assertSame($this->paymentTokenMock, $paymentToken);
        self::assertEquals(self::$token, $paymentToken->getGatewayToken());
        self::assertEquals(self::$payerEmail, $tokenDetails['payerEmail']);
        self::assertEquals($expirationDate, $paymentToken->getExpiresAt());
    }

    public function testHandleWithoutToken()
    {
        $transaction = $this->getTransaction();
        $transaction->transaction->paypalDetails->token = null;

        $response = [
            'object' => $transaction
        ];

        $this->paymentDataObjectMock->method('getPayment')
            ->willReturn($this->paymentInfoMock);

        $this->paymentTokenFactoryMock->expects(self::never())
            ->method('create');

        $this->dateTimeFactoryMock->expects(self::never())
            ->method('create');

        $this->handler->handle($this->subject, $response);
        self::assertNotNull($this->paymentInfoMock->getExtensionAttributes());
    }

    /**
     * Creates Braintree transaction.
     *
     * @return Successful
     */
    private function getTransaction(): Successful
    {
        $attributes = [
            'id' => self::$transactionId,
            'paypalDetails' => $this->getPayPalDetails()
        ];

        $transaction = Transaction::factory($attributes);
        $result = new Successful(['transaction' => $transaction]);

        return $result;
    }

    /**
     * Gets PayPal transaction details.
     *
     * @return PayPalDetails
     */
    private function getPayPalDetails(): PayPalDetails
    {
        $attributes = [
            'token' => self::$token,
            'payerEmail' => self::$payerEmail
        ];

        $details = new PayPalDetails($attributes);

        return $details;
    }
}
