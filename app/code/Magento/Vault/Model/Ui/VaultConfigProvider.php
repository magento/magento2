<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;

class VaultConfigProvider implements ConfigProviderInterface
{
    const IS_ACTIVE_CODE = 'is_active_payment_token_enabler';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var VaultPaymentInterface
     */
    private $vault;

    /**
     * @var Session
     */
    private $session;

    /**
     * VaultConfigProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param VaultPaymentInterface $vault
     * @param Session $session
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        VaultPaymentInterface $vault,
        Session $session
    ) {
        $this->storeManager = $storeManager;
        $this->vault = $vault;
        $this->session = $session;
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
                'is_enabled' => $this->session->getCustomerId() !== null && $this->vault->isActive($storeId)
            ]
        ];
    }
}
