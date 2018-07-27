<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Vault\Model\CustomerTokenManagement;

/**
 * Class ConfigProvider
 * @api
 */
final class TokensConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    private static $vaultCode = 'vault';

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
     * @var PaymentMethodListInterface
     */
    private $vaultPaymentList;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param CustomerTokenManagement $customerTokenManagement
     * @param TokenUiComponentProviderInterface[] $tokenUiComponentProviders
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerTokenManagement $customerTokenManagement,
        array $tokenUiComponentProviders = []
    ) {
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
        $providers = $this->getComponentProviders();

        if (empty($providers)) {
            return $vaultPayments;
        }

        $tokens = $this->customerTokenManagement->getCustomerSessionTokens();

        foreach ($tokens as $i => $token) {
            $paymentCode = $token->getPaymentMethodCode();
            if (!isset($providers[$paymentCode])) {
                continue;
            }

            $componentProvider = $providers[$paymentCode];
            $component = $componentProvider->getComponentForToken($token);
            $config = $component->getConfig();
            $vaultPaymentCode = !empty($config['code']) ? $config['code'] : $paymentCode;
            $vaultPayments[$vaultPaymentCode . '_' . $i] = [
                'config' => $config,
                'component' => $component->getName()
            ];
        }

        return [
            'payment' => [
                self::$vaultCode => $vaultPayments
            ]
        ];
    }

    /**
     * Get list of available vault ui token providers.
     *
     * @return TokenUiComponentProviderInterface[]
     */
    private function getComponentProviders()
    {
        $providers = [];
        $storeId = $this->storeManager->getStore()->getId();
        $vaultPaymentMethods = $this->getVaultPaymentList()->getActiveList($storeId);

        foreach ($vaultPaymentMethods as $method) {
            $providerCode = $method->getProviderCode();
            $componentProvider = $this->getComponentProvider($providerCode);
            if ($componentProvider === null) {
                continue;
            }
            $providers[$providerCode] = $componentProvider;
        }

        return $providers;
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

    /**
     * Get instance of vault payment list instance
     * @return PaymentMethodListInterface
     * @deprecated
     */
    private function getVaultPaymentList()
    {
        if ($this->vaultPaymentList === null) {
            $this->vaultPaymentList = ObjectManager::getInstance()->get(PaymentMethodListInterface::class);
        }
        return $this->vaultPaymentList;
    }
}
