<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Weee\Model;

use Magento\Store\Model\Store;

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

    const XML_PATH_FPT_DISCOUNTED = 'tax/weee/discount';

    const XML_PATH_FPT_TAXABLE = 'tax/weee/apply_vat';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->taxHelper = $taxData;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get weee amount display type on product view page
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_PRODUCT_VIEW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get weee amount display type on product list page
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getListPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_PRODUCT_LIST,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get weee amount display type in sales modules
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getSalesPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_SALES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get weee amount display type in email templates
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getEmailPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_DISPLAY_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if weee amount includes tax already
     * Returns true if weee is taxable and catalog price includes tax
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function isTaxIncluded($store = null)
    {
        $isFPTTaxable = $this->isTaxable($store);

        $isCatalogPriceIncludeTax = $this->taxHelper->priceIncludesTax($store);

        return $isFPTTaxable && $isCatalogPriceIncludeTax;
    }

    /**
     * Check if weee tax amount should be included to subtotal
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function includeInSubtotal($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FPT_INCLUDE_IN_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if weee tax amount should be discounted
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function isDiscounted($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FPT_DISCOUNTED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if weee tax amount should be taxable
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function isTaxable($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FPT_TAXABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if fixed taxes are used in system
     *
     * @param Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FPT_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
