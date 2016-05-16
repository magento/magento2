<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui;

use Magento\Customer\Model\Session;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\Ui\TokensConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProviderTest
 *
 * @see \Magento\Vault\Model\Ui\TokensConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class TokensConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var VaultPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultPayment;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var CustomerTokenManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerTokenManagement;

    protected function setUp()
    {
        $this->vaultPayment = $this->getMock(VaultPaymentInterface::class);
        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->store = $this->getMock(StoreInterface::class);
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
                VaultPaymentInterface::CODE => [
                    VaultPaymentInterface::CODE . '_item_' . '0' => [
                        'config' => ['token_code' => 'code'],
                        'component' => 'Vendor_Module/js/vault_component'
                    ]
                ]
            ]
        ];

        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();
        $tokenUiComponentProvider = $this->getMock(TokenUiComponentProviderInterface::class);
        $tokenUiComponent = $this->getMock(TokenUiComponentInterface::class);

        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->with($storeId)
            ->willReturn($vaultProviderCode);

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->with(null)
            ->willReturn($this->store);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn(true);

        $this->customerTokenManagement->expects(self::once())
            ->method('getCustomerSessionTokens')
            ->willReturn([$tokenMock]);

        $tokenUiComponentProvider->expects(static::once())
            ->method('getComponentForToken')
            ->with($tokenMock)
            ->willReturn($tokenUiComponent);
        $tokenUiComponent->expects(static::once())
            ->method('getConfig')
            ->willReturn(['token_code' => 'code']);
        $tokenUiComponent->expects(static::once())
            ->method('getName')
            ->willReturn('Vendor_Module/js/vault_component');

        $configProvider = new TokensConfigProvider(
            $this->storeManager,
            $this->vaultPayment,
            $this->customerTokenManagement,
            [
                $vaultProviderCode => $tokenUiComponentProvider
            ]
        );

        static::assertEquals(
            $expectedConfig,
            $configProvider->getConfig()
        );
    }
}
