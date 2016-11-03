<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $activeVaultCodes = array_map($getCodeFunc, $activeVaultList);
        $activeVaultProviderCodes = array_map($getProviderCodeFunc, $activeVaultList);
        $activePaymentMethodCodes = array_merge(
            $activePaymentMethodCodes,
            $activeVaultCodes,
            $activeVaultProviderCodes
        );

        foreach ($configuration as $paymentGroup => $groupConfig) {
            foreach (array_keys($groupConfig['methods']) as $paymentCode) {
                if (!in_array($paymentCode, $activePaymentMethodCodes)) {
                    unset($configuration[$paymentGroup]['methods'][$paymentCode]);
                }
            }
            if ($paymentGroup === 'vault' && !empty($activeVaultCodes)) {
                continue;
            }
            if (empty($configuration[$paymentGroup]['methods'])) {
                unset($configuration[$paymentGroup]);
            }
        }

        return [$jsLayout];
    }
}
