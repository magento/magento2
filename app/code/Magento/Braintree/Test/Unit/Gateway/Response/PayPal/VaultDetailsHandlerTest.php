<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response\PayPal;

use Braintree\Transaction;
use Braintree\Transaction\PayPalDetails;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use OAuthTest\Mocks\Common\Service\Mock;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class VaultDetailsHandlerTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var PaymentTokenInterfaceFactory|MockObject
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

    protected function setUp()
    {
        $this->paymentDataObject = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->paymentInfo = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $this->paymentToken = $this->getMockForAbstractClass(PaymentTokenInterface::class);

        $this->paymentTokenFactory = $this->getMockBuilder(PaymentTokenInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactory->expects(static::once())
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
        $this->paymentExtensionFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->paymentExtension);

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPayment', 'readTransaction'])
            ->getMock();
        
        $this->handler = new VaultDetailsHandler(
            $this->paymentTokenFactory,
            $this->paymentExtensionFactory,
            $this->subjectReader
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler::handle
     */
    public function testHandle()
    {
        $transaction = $this->getTransaction();
        $subject = [
            'payment' => $this->paymentDataObject,
        ];
        $response = [
            'object' => $transaction
        ];

        $this->paymentExtension->expects(self::once())
            ->method('setVaultPaymentToken')
            ->with($this->paymentToken);
        $this->paymentExtension->expects(self::once())
            ->method('getVaultPaymentToken')
            ->willReturn($this->paymentToken);

        $this->subjectReader->expects(static::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($this->paymentDataObject);

        $this->subjectReader->expects(static::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);
        
        $this->paymentDataObject->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentToken->expects(static::once())
            ->method('setGatewayToken')
            ->with($transaction->paypalDetails->token);
        
        $this->handler->handle($subject, $response);
        $extensionAttributes = $this->paymentInfo->getExtensionAttributes();
        static::assertSame($this->paymentToken, $extensionAttributes->getVaultPaymentToken());
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
