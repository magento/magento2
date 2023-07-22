<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Tax\Helper\Data;

/**
 * WEEE config model
 */
class Config
{
    /**
     * Enabled config path
     */
    const XML_PATH_FPT_ENABLED = 'tax/weee/enable';

    // display settings
    const XML_PATH_FPT_DISPLAY_PRODUCT_VIEW = 'tax/weee/display';

    const XML_PATH_FPT_DISPLAY_PRODUCT_LIST = 'tax/weee/display_list';

    const XML_PATH_FPT_DISPLAY_SALES = 'tax/weee/display_sales';

    const XML_PATH_FPT_DISPLAY_EMAIL = 'tax/weee/display_email';

    // misc
    const XML_PATH_FPT_INCLUDE_IN_SUBTOTAL = 'tax/weee/include_in_subtotal';

    const XML_PATH_FPT_TAXABLE = 'tax/weee/apply_vat';

    /**
     * @param Data $taxData
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        private Data $taxData
    ) {
    }

    /**
     * Get weee amount display type on product view page
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_PRODUCT_VIEW,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get weee amount display type on product list page
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getListPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_PRODUCT_LIST,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get weee amount display type in sales modules
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getSalesPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_SALES,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get weee amount display type in email templates
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getEmailPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if weee tax amount should be included to subtotal
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function includeInSubtotal($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FPT_INCLUDE_IN_SUBTOTAL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if weee tax amount should be taxable
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isTaxable($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FPT_TAXABLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if fixed taxes are used in system
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
