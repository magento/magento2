<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Plugin;

/**
 * Class PaymentVaultConfigurationProcess
 *
 * Checks if vault group have active vaults.
 */
class PaymentVaultConfigurationProcess
{
    /**
     * @var \Magento\Vault\Api\PaymentMethodListInterface
     */
    private $vaultPaymentList;

    /**
     * @var \Magento\Vault\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Vault\Api\PaymentMethodListInterface $vaultPaymentList
     * @param \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Vault\Api\PaymentMethodListInterface $vaultPaymentList,
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->vaultPaymentList = $vaultPaymentList;
        $this->paymentMethodList = $paymentMethodList;
        $this->storeManager = $storeManager;
    }

    /**
     * Checkout LayoutProcessor before process plugin.
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $processor
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout)
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
