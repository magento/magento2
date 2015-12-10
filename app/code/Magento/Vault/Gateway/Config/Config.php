<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Gateway\Config;

/**
 * Class Config
 * @package Magento\Vault\Gateway\Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_VAULT_PAYMENT = 'vault_payment';

    /**
     * Check if Vault is enabled for payment method
     * @param string $paymentMethodCode
     * @return bool
     */
    public function isVaultEnabledForPaymentMethod($paymentMethodCode)
    {
        $isActive = $this->getValue(self::KEY_ACTIVE);
        // TODO: Remove this stub after development will be completed
        $isActive = true;
        $vaultPayment = $this->getValue(self::KEY_VAULT_PAYMENT);
        return ((bool) $isActive) && (!empty($vaultPayment) && $vaultPayment == $paymentMethodCode);
    }
}
