<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\Unit\Observer;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Math\Random;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Observer\AfterPaymentSaveObserver;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for payment observer.
 */
class AfterPaymentSaveObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer|MockObject
     */
    protected $eventObserverArgMock;

    /**
     * @var \Magento\Vault\Observer\AfterPaymentSaveObserver
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptorModel;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtension|MockObject paymentExtension
     */
    protected $paymentExtension;

    /**
     * @var \Magento\Vault\Model\PaymentTokenManagement|MockObject paymentTokenManagementMock
     */
    protected $paymentTokenManagementMock;

    /**
     * @var \Magento\Vault\Model\PaymentToken|MockObject paymentTokenMock
     */
    protected $paymentTokenMock;

    /**
     * @var \Magento\Sales\Model\Order|MockObject salesOrderMock
     */
    protected $salesOrderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject salesOrderPaymentMock
     */
    protected $salesOrderPaymentMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var Random|MockObject $encryptorRandomGenerator */
        $encryptorRandomGenerator = $this->createMock(Random::class);
        /** @var DeploymentConfig|MockObject $deploymentConfigMock */
        $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->willReturn('g9mY9KLrcuAVJfsmVUSRkKFLDdUPVkaZ');
        $this->encryptorModel = new Encryptor($encryptorRandomGenerator, $deploymentConfigMock);

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenManagementMock = $this->getMockBuilder(PaymentTokenManagement::class)
            ->setMethods(['saveTokenWithPaymentLink'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentExtension->setVaultPaymentToken($this->paymentTokenMock);

        // Sales Order Model
        $this->salesOrderMock = $this->createMock(Order::class);

        // Sales Order Payment Model
        $this->salesOrderPaymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['getAdditionalInformation'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesOrderPaymentMock->setOrder($this->salesOrderMock);
        $this->salesOrderPaymentMock->setExtensionAttributes($this->paymentExtension);

        // Arguments to observer container
        $this->eventObserverArgMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getDataByKey'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventObserverArgMock->expects($this->any())
            ->method('getDataByKey')
            ->with(AfterPaymentSaveObserver::PAYMENT_OBJECT_DATA_KEY)
            ->willReturn($this->salesOrderPaymentMock);

        // Observer class
        $this->observer = new AfterPaymentSaveObserver(
            $this->paymentTokenManagementMock,
            $this->encryptorModel
        );
    }

    /**
     * Case when payment successfully made.
     *
     * @param int $customerId
     * @param string $createdAt
     * @param string $token
     * @param bool $isActive
     * @param string $method
     * @param array $additionalInfo
     * @dataProvider positiveCaseDataProvider
     */
    public function testPositiveCase($customerId, $createdAt, $token, $isActive, $method, $additionalInfo)
    {
        $this->paymentTokenMock->setGatewayToken($token);
        $this->paymentTokenMock->setCustomerId($customerId);
        $this->paymentTokenMock->setCreatedAt($createdAt);
        $this->paymentTokenMock->setPaymentMethodCode($method);
        $this->paymentTokenMock->setIsActive($isActive);

        $this->paymentExtension->expects($this->exactly(2))
            ->method('getVaultPaymentToken')
            ->willReturn($this->paymentTokenMock);

        $this->salesOrderPaymentMock->method('getAdditionalInformation')->willReturn($additionalInfo);

        if (!empty($token)) {
            $this->paymentTokenManagementMock->expects($this->once())
                ->method('saveTokenWithPaymentLink')
                ->willReturn(true);
            $this->paymentExtension->expects($this->once())
                ->method('setVaultPaymentToken')
                ->with($this->paymentTokenMock);
        } else {
            $this->paymentTokenManagementMock->expects($this->never())
                ->method('saveTokenWithPaymentLink');
            $this->paymentExtension->expects($this->never())
                ->method('setVaultPaymentToken')
                ->with($this->paymentTokenMock);
        }

        static::assertSame($this->observer, $this->observer->execute($this->eventObserverArgMock));

        $paymentToken = $this->salesOrderPaymentMock->getExtensionAttributes()->getVaultPaymentToken();
        static::assertSame($paymentToken, $this->paymentTokenMock);
        static::assertEquals($token, $paymentToken->getGatewayToken());
        static::assertEquals($isActive, $paymentToken->getIsActive());
        static::assertEquals($createdAt, $paymentToken->getCreatedAt());
        static::assertEquals(
            $additionalInfo[VaultConfigProvider::IS_ACTIVE_CODE] ?? false,
            $paymentToken->getIsVisible()
        );
    }

    /**
     * Data for positiveCase test.
     *
     * @return array
     */
    public function positiveCaseDataProvider()
    {
        return [
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'paypal',
                [],
            ],
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'paypal',
                [VaultConfigProvider::IS_ACTIVE_CODE => true],
            ],
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'paypal',
                [VaultConfigProvider::IS_ACTIVE_CODE => false],
            ],
            [
                null,
                null,
                null,
                false,
                null,
                [],
            ],
        ];
    }
}
