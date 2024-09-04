<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Model\Method;

use Magento\Framework\Serialize\Serializer\Json;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MethodInterface|MockObject
     */
    private $vaultProvider;

    /**
     * @var Json|MockObject
     */
    private $jsonSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->vaultProvider = $this->getMockForAbstractClass(MethodInterface::class);
        $this->jsonSerializer = $this->createMock(Json::class);
    }

    public function testAuthorizeNotOrderPayment()
    {
        $this->expectException('DomainException');
        $this->expectExceptionMessage('Not implemented');
        $paymentModel = $this->getMockForAbstractClass(InfoInterface::class);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(Vault::class);
        $model->authorize($paymentModel, 0);
    }

    /**
     * @param array $additionalInfo
     * @dataProvider additionalInfoDataProvider
     */
    public function testAuthorizeNoTokenMetadata(array $additionalInfo)
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Public hash should be defined');
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
    public static function additionalInfoDataProvider()
    {
        return [
            ['additionalInfo' => []],
            ['additionalInfo' => ['customer_id' => 1]],
            ['additionalInfo' => ['public_hash' => null]],
        ];
    }

    public function testAuthorizeNoToken()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('No token found');
        $customerId = 1;
        $publicHash = 'token_public_hash';

        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManagement = $this->getMockForAbstractClass(PaymentTokenManagementInterface::class);

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
            ->addMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->getMockForAbstractClass();

        $commandManagerPool = $this->getMockForAbstractClass(CommandManagerPoolInterface::class);
        $commandManager = $this->getMockForAbstractClass(CommandManagerInterface::class);

        $tokenManagement = $this->getMockForAbstractClass(PaymentTokenManagementInterface::class);
        $token = $this->getMockForAbstractClass(PaymentTokenInterface::class);

        $tokenDetails = [
            'cc_last4' => '1111',
            'cc_type' => 'VI',
            'cc_exp_year' => '2020',
            'cc_exp_month' => '01',
        ];

        $extensionAttributes->method('getVaultPaymentToken')
            ->willReturn($token);

        $this->jsonSerializer->method('unserialize')
            ->willReturn($tokenDetails);

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
        $paymentModel->method('getExtensionAttributes')
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
                'vaultProvider' => $this->vaultProvider,
                'jsonSerializer' => $this->jsonSerializer,
            ]
        );
        $model->authorize($paymentModel, $amount);
    }

    public function testCaptureNotOrderPayment()
    {
        $this->expectException('DomainException');
        $this->expectExceptionMessage('Not implemented');
        $paymentModel = $this->getMockForAbstractClass(InfoInterface::class);

        /** @var Vault $model */
        $model = $this->objectManager->getObject(Vault::class);
        $model->capture($paymentModel, 0);
    }

    public function testCapture()
    {
        $this->expectException('DomainException');
        $this->expectExceptionMessage('Capture can not be performed through vault');
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authorizationTransaction = $this->getMockForAbstractClass(TransactionInterface::class);
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
    public static function isAvailableDataProvider()
    {
        return [
            ['isAvailableProvider' => true, 'isActive' => false, 'expected' => false],
            ['isAvailableProvider' => false, 'isActive' => false, 'expected' => false],
            ['isAvailableProvider' => false, 'isActive' => true, 'expected' => false],
            ['isAvailableProvider' => true, 'isActive' => true, 'expected' => true],
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
        $handlerPool = $this->getMockForAbstractClass(ValueHandlerPoolInterface::class);
        $handler = $this->getMockForAbstractClass(ValueHandlerInterface::class);

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
    public static function internalUsageDataProvider()
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
