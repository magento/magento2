<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Model\Ui;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\Ui\TokensConfigProvider;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigProviderTest
 *
 * @see \Magento\Vault\Model\Ui\TokensConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class TokensConfigProviderTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var VaultPaymentInterface|MockObject
     */
    private $vaultPayment;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var CustomerTokenManagement|MockObject
     */
    private $customerTokenManagement;

    /**
     * @var PaymentMethodListInterface|MockObject
     */
    private $vaultPaymentList;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->vaultPaymentList = $this->getMockForAbstractClass(PaymentMethodListInterface::class);
        $this->vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->store = $this->getMockForAbstractClass(StoreInterface::class);

        $this->customerTokenManagement = $this->getMockBuilder(CustomerTokenManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetConfig()
    {
        $storeId = 1;
        $vaultProviderCode = 'vault_provider_code';

        $expectedConfig = [
            'payment' => [
                'vault' => [
                    $vaultProviderCode . '_' . '0' => [
                        'config' => ['token_code' => 'code'],
                        'component' => 'Vendor_Module/js/vault_component'
                    ]
                ]
            ]
        ];

        $token = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $tokenUiComponentProvider = $this->getMockForAbstractClass(TokenUiComponentProviderInterface::class);
        $tokenUiComponent = $this->getMockForAbstractClass(TokenUiComponentInterface::class);

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $this->vaultPaymentList->expects(static::once())
            ->method('getActiveList')
            ->with($storeId)
            ->willReturn([$this->vaultPayment]);

        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn($vaultProviderCode);

        $this->customerTokenManagement->expects(static::once())
            ->method('getCustomerSessionTokens')
            ->willReturn([$token]);

        $token->expects(static::once())
            ->method('getPaymentMethodCode')
            ->willReturn($vaultProviderCode);

        $tokenUiComponentProvider->expects(static::once())
            ->method('getComponentForToken')
            ->with($token)
            ->willReturn($tokenUiComponent);
        $tokenUiComponent->expects(static::once())
            ->method('getConfig')
            ->willReturn(['token_code' => 'code']);
        $tokenUiComponent->expects(static::once())
            ->method('getName')
            ->willReturn('Vendor_Module/js/vault_component');

        $configProvider = new TokensConfigProvider(
            $this->storeManager,
            $this->customerTokenManagement,
            [
                $vaultProviderCode => $tokenUiComponentProvider
            ]
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $configProvider,
            'vaultPaymentList',
            $this->vaultPaymentList
        );

        static::assertEquals($expectedConfig, $configProvider->getConfig());
    }
}
