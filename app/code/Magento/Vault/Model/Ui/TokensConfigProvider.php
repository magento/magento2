<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class ConfigProvider
 * @api
 */
final class TokensConfigProvider implements ConfigProviderInterface
{
    /**
     * @var VaultPaymentInterface
     */
    private $vaultPayment;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TokenUiComponentProviderInterface[]
     */
    private $tokenUiComponentProviders;

    /**
     * @var CustomerTokenManagement
     */
    private $customerTokenManagement;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param VaultPaymentInterface $vaultPayment
     * @param CustomerTokenManagement $customerTokenManagement
     * @param TokenUiComponentProviderInterface[] $tokenUiComponentProviders
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        VaultPaymentInterface $vaultPayment,
        CustomerTokenManagement $customerTokenManagement,
        array $tokenUiComponentProviders = []
    ) {
        $this->vaultPayment = $vaultPayment;
        $this->storeManager = $storeManager;
        $this->tokenUiComponentProviders = $tokenUiComponentProviders;
        $this->customerTokenManagement = $customerTokenManagement;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $vaultPayments = [];
        $storeId = $this->storeManager->getStore()->getId();
        if (!$this->vaultPayment->isActive($storeId)) {
            return $vaultPayments;
        }

        $providerCode = $this->vaultPayment->getProviderCode($storeId);
        $componentProvider = $this->getComponentProvider($providerCode);
        if (null === $componentProvider) {
            return $vaultPayments;
        }

        foreach ($this->customerTokenManagement->getCustomerSessionTokens() as $i => $token) {
            $component = $componentProvider->getComponentForToken($token);
            $vaultPayments[VaultPaymentInterface::CODE . '_item_' . $i] = [
                'config' => $component->getConfig(),
                'component' => $component->getName()
            ];
        }

        return [
            'payment' => [
                VaultPaymentInterface::CODE => $vaultPayments
            ]
        ];
    }

    /**
     * @param string $vaultProviderCode
     * @return TokenUiComponentProviderInterface|null
     */
    private function getComponentProvider($vaultProviderCode)
    {
        $componentProvider = isset($this->tokenUiComponentProviders[$vaultProviderCode])
            ? $this->tokenUiComponentProviders[$vaultProviderCode]
            : null;
        return $componentProvider instanceof TokenUiComponentProviderInterface
            ? $componentProvider
            : null;
    }
}
