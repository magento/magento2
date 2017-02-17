<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Plugin;

/**
 * Class PaymentConfigurationProcess
 *
 * Removes inactive payment methods and group from checkout configuration.
 */
class PaymentConfigurationProcess
{
    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
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
        $getCodeFunc = function ($method) {
            return $method->getCode();
        };
        $activePaymentMethodCodes = array_map($getCodeFunc, $activePaymentMethodList);

        foreach ($configuration as $paymentGroup => $groupConfig) {
            $notActivePaymentMethodCodes = array_diff(array_keys($groupConfig['methods']), $activePaymentMethodCodes);
            foreach ($notActivePaymentMethodCodes as $notActivePaymentMethodCode) {
                unset($configuration[$paymentGroup]['methods'][$notActivePaymentMethodCode]);
            }
            if (empty($configuration[$paymentGroup]['methods'])) {
                unset($configuration[$paymentGroup]);
            }
        }

        return [$jsLayout];
    }
}
