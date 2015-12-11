<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;

class VaultConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $storeId = 1;
        $vaultEnabled = true;
        $paymentProviderCode = 'concrete_vault_provider';

        $expectedConfiguration = [
            VaultPaymentInterface::CODE => [
                'vault_provider_code' => $paymentProviderCode,
                'is_enabled' => $vaultEnabled
            ]
        ];

        $vaultMethod = $this->getMock(VaultPaymentInterface::class);
        $storeManager = $this->getMock(StoreManagerInterface::class);
        $store = $this->getMock(\Magento\Store\Api\Data\StoreInterface::class);

        $storeManager->expects(static::once())
            ->method('getStore')
            ->with(null)
            ->willReturn($store);
        $store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        $vaultMethod->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn($vaultEnabled);
        $vaultMethod->expects(static::once())
            ->method('getProviderCode')
            ->with($storeId)
            ->willReturn($paymentProviderCode);

        $vaultCards = new \Magento\Vault\Model\Ui\VaultConfigProvider($storeManager, $vaultMethod);

        static::assertEquals(
            $expectedConfiguration,
            $vaultCards->getConfig()
        );
    }
}
