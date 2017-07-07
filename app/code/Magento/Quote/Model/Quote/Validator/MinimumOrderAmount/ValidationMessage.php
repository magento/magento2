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
     */
    private $currency;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currency
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->currency = $currency;
    }

    /**
     * Get validation message.
     *
     * @return \Magento\Framework\Phrase
     * @throws \Zend_Currency_Exception
     */
    public function getMessage()
    {
        $message = $this->scopeConfig->getValue(
            'sales/minimum_order/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$message) {
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $minimumAmount = $this->currency->getCurrency($currencyCode)->toCurrency(
                $this->scopeConfig->getValue(
                    'sales/minimum_order/amount',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            $message = __('Minimum order amount is %1', $minimumAmount);
        } else {
            //Added in order to address the issue: https://github.com/magento/magento2/issues/8287
            $message = __($message);
        }

        return $message;
    }
}
