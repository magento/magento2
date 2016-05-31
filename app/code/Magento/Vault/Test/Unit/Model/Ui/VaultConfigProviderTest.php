<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui;

use Magento\Customer\Model\Session;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Model\VaultPaymentInterface;

class VaultConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int $id
     * @param bool $isVaultEnabled
     *
     * @dataProvider customerIdProvider
     */
    public function testGetConfig($customerId, $vaultEnabled)
    {
        $storeId = 1;
        $paymentProviderCode = 'concrete_vault_provider';

        $expectedConfiguration = [
            VaultPaymentInterface::CODE => [
                'vault_provider_code' => $paymentProviderCode,
                'is_enabled' => $vaultEnabled
            ]
        ];

        $vaultMethod = $this->getMock(VaultPaymentInterface::class);
        $storeManager = $this->getMock(StoreManagerInterface::class);
        $store = $this->getMock(StoreInterface::class);

        $session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $storeManager->expects(static::once())
            ->method('getStore')
            ->with(null)
            ->willReturn($store);
        $store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        $vaultMethod->expects($customerId !== null ? static::once() : static::never())
            ->method('isActive')
            ->with($storeId)
            ->willReturn($vaultEnabled);
        $vaultMethod->expects(static::once())
            ->method('getProviderCode')
            ->with($storeId)
            ->willReturn($paymentProviderCode);

        $vaultCards = new VaultConfigProvider($storeManager, $vaultMethod, $session);

        static::assertEquals(
            $expectedConfiguration,
            $vaultCards->getConfig()
        );
    }

    /**
     * @return array
     */
    public function customerIdProvider()
    {
        return [
            [
                'id' => 1,
                'vault_enabled' => true
            ],
            [
                'id' => null,
                'vault_enabled' => false
            ]
        ];
    }
}
