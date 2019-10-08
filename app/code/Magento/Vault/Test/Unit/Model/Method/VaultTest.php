<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Model\Method;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\Method\Vault;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class VaultTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MethodInterface|MockObject
     */
    private $vaultProvider;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->vaultProvider = $this->createMock(MethodInterface::class);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Not implemented
     */
    public function testAuthorizeNotOrderPayment()
    {
        $paymentModel = $this->createMock(InfoInterface::class);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(Vault::class);
        $model->authorize($paymentModel, 0);
    }

    /**
     * @param array $additionalInfo
     * @expectedException \LogicException
     * @expectedExceptionMessage Public hash should be defined
     * @dataProvider additionalInfoDataProvider
     */
    public function testAuthorizeNoTokenMetadata(array $additionalInfo)
    {
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentModel->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInfo);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(Vault::class);
        $model->authorize($paymentModel, 0);
    }

    /**
     * Get list of additional information variations
     * @return array
     */
    public function additionalInfoDataProvider()
    {
        return [
            ['additionalInfo' => []],
            ['additionalInfo' => ['customer_id' => 1]],
            ['additionalInfo' => ['public_hash' => null]],
        ];
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage No token found
     */
    public function testAuthorizeNoToken()
    {
        $customerId = 1;
        $publicHash = 'token_public_hash';

        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManagement = $this->createMock(PaymentTokenManagementInterface::class);

        $paymentModel->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(
                [
                    PaymentTokenInterface::CUSTOMER_ID => $customerId,
                    PaymentTokenInterface::PUBLIC_HASH => $publicHash
                ]
            );
        $tokenManagement->expects(static::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn(null);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(
            Vault::class,
            [
                'tokenManagement' => $tokenManagement
            ]
        );
        $model->authorize($paymentModel, 0);
    }

    public function testAuthorize()
    {
        $customerId = 1;
        $publicHash = 'token_public_hash';
        $vaultProviderCode = 'vault_provider_code';
        $amount = 10.01;

        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributes = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->getMockForAbstractClass();

        $commandManagerPool = $this->createMock(CommandManagerPoolInterface::class);
        $commandManager = $this->createMock(CommandManagerInterface::class);

        $tokenManagement = $this->createMock(PaymentTokenManagementInterface::class);
        $token = $this->createMock(PaymentTokenInterface::class);

        $paymentModel->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(
                [
                    PaymentTokenInterface::CUSTOMER_ID => $customerId,
                    PaymentTokenInterface::PUBLIC_HASH => $publicHash
                ]
            );
        $tokenManagement->expects(static::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($token);
        $paymentModel->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects(static::once())
            ->method('setVaultPaymentToken')
            ->with($token);

        $this->vaultProvider->expects(static::atLeastOnce())
            ->method('getCode')
            ->willReturn($vaultProviderCode);
        $commandManagerPool->expects(static::once())
            ->method('get')
            ->with($vaultProviderCode)
            ->willReturn($commandManager);
        $commandManager->expects(static::once())
            ->method('executeByCode')
            ->with(
                VaultPaymentInterface::VAULT_AUTHORIZE_COMMAND,
                $paymentModel,
                [
                    'amount' => $amount
                ]
            );

        $paymentModel->expects(static::once())
            ->method('setMethod')
            ->with($vaultProviderCode);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(
            Vault::class,
            [
                'tokenManagement' => $tokenManagement,
                'commandManagerPool' => $commandManagerPool,
                'vaultProvider' => $this->vaultProvider
            ]
        );
        $model->authorize($paymentModel, $amount);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Not implemented
     */
    public function testCaptureNotOrderPayment()
    {
        $paymentModel = $this->createMock(InfoInterface::class);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(Vault::class);
        $model->capture($paymentModel, 0);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Capture can not be performed through vault
     */
    public function testCapture()
    {
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authorizationTransaction = $this->createMock(TransactionInterface::class);
        $paymentModel->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn($authorizationTransaction);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(Vault::class);
        $model->capture($paymentModel, 0);
    }

    /**
     * @covers       \Magento\Vault\Model\Method\Vault::isAvailable
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable($isAvailableProvider, $isActive, $expected)
    {
        $storeId = 1;
        $quote = $this->getMockForAbstractClass(CartInterface::class);
        $config = $this->getMockForAbstractClass(ConfigInterface::class);

        $this->vaultProvider->expects(static::once())
            ->method('isAvailable')
            ->with($quote)
            ->willReturn($isAvailableProvider);

        $config->expects(static::any())
            ->method('getValue')
            ->with(
                'active',
                $storeId
            )
            ->willReturn($isActive);

        $quote->expects(static::any())
            ->method('getStoreId')
            ->willReturn($storeId);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(
            Vault::class,
            [
                'config' => $config,
                'vaultProvider' => $this->vaultProvider
            ]
        );
        $actual = $model->isAvailable($quote);
        static::assertEquals($expected, $actual);
    }

    /**
     * List of variations for testing isAvailable method
     * @return array
     */
    public function isAvailableDataProvider()
    {
        return [
            ['isAvailableProvider' => true, 'isActiveVault' => false, 'expected' => false],
            ['isAvailableProvider' => false, 'isActiveVault' => false, 'expected' => false],
            ['isAvailableProvider' => false, 'isActiveVault' => true, 'expected' => false],
            ['isAvailableProvider' => true, 'isActiveVault' => true, 'expected' => true],
        ];
    }

    /**
     * @covers \Magento\Vault\Model\Method\Vault::isAvailable
     */
    public function testIsAvailableWithoutQuote()
    {
        $quote = null;
        $config = $this->getMockForAbstractClass(ConfigInterface::class);

        $this->vaultProvider->expects(static::once())
            ->method('isAvailable')
            ->with($quote)
            ->willReturn(true);

        $config->expects(static::once())
            ->method('getValue')
            ->with(
                'active',
                $quote
            )
            ->willReturn(false);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(
            Vault::class,
            [
                'config' => $config,
                'vaultProvider' => $this->vaultProvider
            ]
        );
        static::assertFalse($model->isAvailable($quote));
    }

    /**
     * @covers       \Magento\Vault\Model\Method\Vault::canUseInternal
     * @param bool|null $configValue
     * @param bool|null $paymentValue
     * @param bool $expected
     * @dataProvider internalUsageDataProvider
     */
    public function testCanUseInternal($configValue, $paymentValue, $expected)
    {
        $handlerPool = $this->createMock(ValueHandlerPoolInterface::class);
        $handler = $this->createMock(ValueHandlerInterface::class);

        $handlerPool->expects(static::once())
            ->method('get')
            ->with('can_use_internal')
            ->willReturn($handler);

        $handler->expects(static::once())
            ->method('handle')
            ->with(
                ['field' => 'can_use_internal'],
                null
            )
            ->willReturn($configValue);

        $this->vaultProvider->expects(static::any())
            ->method('canUseInternal')
            ->willReturn($paymentValue);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(
            Vault::class,
            [
                'vaultProvider' => $this->vaultProvider,
                'valueHandlerPool' => $handlerPool,
            ]
        );
        static::assertEquals($expected, $model->canUseInternal());
    }

    /**
     * Get list of variations for testing canUseInternal method
     * @return array
     */
    public function internalUsageDataProvider()
    {
        return [
            ['configValue' => true, 'paymentValue' => true, 'expected' => true],
            ['configValue' => true, 'paymentValue' => null, 'expected' => true],
            ['configValue' => true, 'paymentValue' => false, 'expected' => true],
            ['configValue' => false, 'paymentValue' => true, 'expected' => false],
            ['configValue' => false, 'paymentValue' => false, 'expected' => false],
            ['configValue' => null, 'paymentValue' => true, 'expected' => true],
            ['configValue' => null, 'paymentValue' => false, 'expected' => false],
            ['configValue' => null, 'paymentValue' => null, 'expected' => false],
        ];
    }
}
