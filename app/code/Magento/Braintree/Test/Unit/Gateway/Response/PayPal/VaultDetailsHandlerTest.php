<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response\PayPal;

use Braintree\Transaction;
use Braintree\Transaction\PayPalDetails;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Vault\Model\AccountPaymentTokenFactory;
use Magento\Vault\Model\PaymentToken;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class VaultDetailsHandlerTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{
    private static $transactionId = '1n2suy';

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObject;

    /**
     * @var Payment|MockObject
     */
    private $paymentInfo;

    /**
     * @var AccountPaymentTokenFactory|MockObject
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    protected $paymentToken;

    /**
     * @var OrderPaymentExtension|MockObject
     */
    private $paymentExtension;

    /**
     * @var OrderPaymentExtensionInterfaceFactory|MockObject
     */
    private $paymentExtensionFactory;

    /**
     * @var VaultDetailsHandler
     */
    private $handler;

    /**
     * @var DateTimeFactory|MockObject
     */
    private $dateTimeFactory;

    /**
     * @var array
     */
    private $subject = [];

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->paymentDataObject = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->paymentInfo = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $this->paymentToken = $objectManager->getObject(PaymentToken::class);

        $this->paymentTokenFactory = $this->getMockBuilder(AccountPaymentTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactory = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->subject = [
            'payment' => $this->paymentDataObject,
        ];
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPayment', 'readTransaction'])
            ->getMock();
        $this->subjectReader->expects(static::once())
            ->method('readPayment')
            ->with($this->subject)
            ->willReturn($this->paymentDataObject);

        $this->dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        
        $this->handler = new VaultDetailsHandler(
            $this->paymentTokenFactory,
            $this->paymentExtensionFactory,
            $this->subjectReader,
            $this->dateTimeFactory
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler::handle
     */
    public function testHandle()
    {
        /** @var Transaction $transaction */
        $transaction = $this->getTransaction();
        $response = [
            'object' => $transaction
        ];

        $this->paymentExtension->expects(static::once())
            ->method('setVaultPaymentToken')
            ->with($this->paymentToken);
        $this->paymentExtension->expects(static::once())
            ->method('getVaultPaymentToken')
            ->willReturn($this->paymentToken);

        $this->subjectReader->expects(static::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);
        
        $this->paymentDataObject->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentTokenFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->paymentToken);

        $this->paymentExtensionFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->paymentExtension);

        $dateTime = new \DateTime('2016-07-05 00:00:00', new \DateTimeZone('UTC'));
        $expirationDate = '2017-07-05 00:00:00';
        $this->dateTimeFactory->expects(static::once())
            ->method('create')
            ->willReturn($dateTime);
        
        $this->handler->handle($this->subject, $response);

        $extensionAttributes = $this->paymentInfo->getExtensionAttributes();
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        static::assertNotNull($paymentToken);

        $tokenDetails = json_decode($paymentToken->getTokenDetails(), true);

        static::assertSame($this->paymentToken, $paymentToken);
        static::assertEquals($transaction->paypalDetails->token, $paymentToken->getGatewayToken());
        static::assertEquals($transaction->paypalDetails->payerEmail, $tokenDetails['payerEmail']);
        static::assertEquals($expirationDate, $paymentToken->getExpiresAt());
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler::handle
     */
    public function testHandleWithoutToken()
    {
        $transaction = $this->getTransaction();
        $transaction->paypalDetails->token = null;

        $response = [
            'object' => $transaction
        ];

        $this->subjectReader->expects(static::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $this->paymentDataObject->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentTokenFactory->expects(static::never())
            ->method('create');

        $this->dateTimeFactory->expects(static::never())
            ->method('create');

        $this->handler->handle($this->subject, $response);
        static::assertNull($this->paymentInfo->getExtensionAttributes());
    }

    /**
     * Create Braintree transaction
     * @return Transaction
     */
    private function getTransaction()
    {
        $attributes = [
            'id' => self::$transactionId,
            'paypalDetails' => $this->getPayPalDetails()
        ];

        $transaction = Transaction::factory($attributes);

        return $transaction;
    }

    /**
     * Get PayPal transaction details
     * @return PayPalDetails
     */
    private function getPayPalDetails()
    {
        $attributes = [
            'token' => 'rc39al',
            'payerEmail' => 'john.doe@example.com'
        ];

        $details = new PayPalDetails($attributes);

        return $details;
    }
}
