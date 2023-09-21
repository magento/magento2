<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Observer;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Math\Random;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\Store;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Observer\AfterPaymentSaveObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for payment observer.
 */
class AfterPaymentSaveObserverTest extends TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer|MockObject
     */
    protected $eventObserverArgMock;

    /**
     * @var AfterPaymentSaveObserver
     */
    protected $observer;

    /**
     * @var Encryptor
     */
    protected $encryptorModel;

    /**
     * @var OrderPaymentExtension|MockObject paymentExtension
     */
    protected $paymentExtension;

    /**
     * @var PaymentTokenManagement|MockObject paymentTokenManagementMock
     */
    protected $paymentTokenManagementMock;

    /**
     * @var PaymentToken|MockObject paymentTokenMock
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
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->setMethods(['getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();

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
    public function testPositiveCase($customerId, $createdAt, $token, $isActive, $method, $websiteId, $additionalInfo)
    {
        $this->paymentTokenMock->setGatewayToken($token);
        $this->paymentTokenMock->setCustomerId($customerId);
        $this->paymentTokenMock->setCreatedAt($createdAt);
        $this->paymentTokenMock->setPaymentMethodCode($method);
        $this->paymentTokenMock->setIsActive($isActive);
        $this->paymentTokenMock->setWebsiteId($websiteId);

        $this->paymentExtension->expects($this->exactly(2))
            ->method('getVaultPaymentToken')
            ->willReturn($this->paymentTokenMock);

        $this->salesOrderMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

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
                1,
                [],
            ],
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'paypal',
                1,
                [VaultConfigProvider::IS_ACTIVE_CODE => true],
            ],
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'paypal',
                1,
                [VaultConfigProvider::IS_ACTIVE_CODE => false],
            ],
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'braintree_cc_vault',
                2,
                [VaultConfigProvider::IS_ACTIVE_CODE => true],
            ],
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'braintree_cc_vault',
                1,
                [VaultConfigProvider::IS_ACTIVE_CODE => true],
            ],
            [
                null,
                null,
                null,
                false,
                null,
                1,
                [],
            ],
        ];
    }
}
