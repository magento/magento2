<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Validator\MinimumOrderAmount;

class ValidationMessage
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     * @deprecated 101.0.3 since 101.0.0
     * @see no alternatives
     */
    private $currency;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        ?\Magento\Framework\Pricing\Helper\Data $priceHelper = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->currency = $currency;
        $this->priceHelper = $priceHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Pricing\Helper\Data::class);
    }

    /**
     * Get validation message.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getMessage()
    {
        $message = $this->scopeConfig->getValue(
            'sales/minimum_order/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$message) {
            $minimumAmount =  $this->priceHelper->currency($this->scopeConfig->getValue(
                'sales/minimum_order/amount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ), true, false);

            $message = __('Minimum order amount is %1', $minimumAmount);
        } else {
            //Added in order to address the issue: https://github.com/magento/magento2/issues/8287
            $message = __($message);
        }

        return $message;
    }
}
