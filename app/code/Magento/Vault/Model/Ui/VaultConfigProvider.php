<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;

class VaultConfigProvider implements ConfigProviderInterface
{
    const IS_ACTIVE_CODE = 'is_active_payment_token_enabler';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Vault\Model\VaultPaymentInterface
     */
    private $vault;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Vault\Model\VaultPaymentInterface $vault
    ) {
        $this->storeManager = $storeManager;
        $this->vault = $vault;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->storeManager->getStore()->getId();

        return [
            VaultPaymentInterface::CODE => [
                'vault_provider_code' => $this->vault->getProviderCode($storeId),
                'is_enabled' => $this->vault->isActive($storeId)
            ]
        ];
    }
}
