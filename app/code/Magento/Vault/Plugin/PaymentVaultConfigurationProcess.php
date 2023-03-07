<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentMethodListInterface as VaultApiPaymentMethodListInterface;

/**
 * Class PaymentVaultConfigurationProcess
 *
 * Checks if vault group have active vaults.
 */
class PaymentVaultConfigurationProcess
{
    /**
     * @param VaultApiPaymentMethodListInterface $vaultPaymentList
     * @param PaymentMethodListInterface $paymentMethodList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly VaultApiPaymentMethodListInterface $vaultPaymentList,
        private readonly PaymentMethodListInterface $paymentMethodList,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Checkout LayoutProcessor before process plugin.
     *
     * @param LayoutProcessor $processor
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(LayoutProcessor $processor, $jsLayout)
    {
        $configuration = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['renders']['children'];

        if (!isset($configuration)) {
            return [$jsLayout];
        }

        $storeId = $this->storeManager->getStore()->getId();
        $activePaymentMethodList = $this->paymentMethodList->getActiveList($storeId);
        $activeVaultList = $this->vaultPaymentList->getActiveList($storeId);
        $getCodeFunc = function ($method) {
            return $method->getCode();
        };
        $getProviderCodeFunc = function ($method) {
            return $method->getProviderCode();
        };
        $activePaymentMethodCodes = array_map($getCodeFunc, $activePaymentMethodList);
        $activeVaultProviderCodes = array_map($getProviderCodeFunc, $activeVaultList);
        $activePaymentMethodCodes = array_merge(
            $activePaymentMethodCodes,
            $activeVaultProviderCodes
        );

        foreach ($configuration as $paymentGroup => $groupConfig) {
            $notActivePaymentMethodCodes = array_diff(array_keys($groupConfig['methods']), $activePaymentMethodCodes);
            foreach ($notActivePaymentMethodCodes as $notActivePaymentMethodCode) {
                unset($configuration[$paymentGroup]['methods'][$notActivePaymentMethodCode]);
            }
            if ($paymentGroup === 'vault' && !empty($activeVaultProviderCodes)) {
                continue;
            }
            if (empty($configuration[$paymentGroup]['methods'])) {
                unset($configuration[$paymentGroup]);
            }
        }

        return [$jsLayout];
    }
}
