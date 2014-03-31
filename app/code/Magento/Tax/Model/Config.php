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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration paths storage
 *
 * @category   Magento
 * @package    Magento_Tax
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model;

use Magento\Core\Model\Store;

class Config
{
    // tax classes
    const CONFIG_XML_PATH_SHIPPING_TAX_CLASS = 'tax/classes/shipping_tax_class';

    // tax calculation
    const CONFIG_XML_PATH_PRICE_INCLUDES_TAX = 'tax/calculation/price_includes_tax';

    const CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX = 'tax/calculation/shipping_includes_tax';

    const CONFIG_XML_PATH_BASED_ON = 'tax/calculation/based_on';

    const CONFIG_XML_PATH_APPLY_ON = 'tax/calculation/apply_tax_on';

    const CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT = 'tax/calculation/apply_after_discount';

    const CONFIG_XML_PATH_DISCOUNT_TAX = 'tax/calculation/discount_tax';

    const XML_PATH_ALGORITHM = 'tax/calculation/algorithm';

    // tax defaults
    const CONFIG_XML_PATH_DEFAULT_COUNTRY = 'tax/defaults/country';

    const CONFIG_XML_PATH_DEFAULT_REGION = 'tax/defaults/region';

    const CONFIG_XML_PATH_DEFAULT_POSTCODE = 'tax/defaults/postcode';

    /**
     * Prices display settings
     */
    const CONFIG_XML_PATH_PRICE_DISPLAY_TYPE = 'tax/display/type';

    const CONFIG_XML_PATH_DISPLAY_SHIPPING = 'tax/display/shipping';

    /**
     * Shopping cart display settings
     */
    const XML_PATH_DISPLAY_CART_PRICE = 'tax/cart_display/price';

    const XML_PATH_DISPLAY_CART_SUBTOTAL = 'tax/cart_display/subtotal';

    const XML_PATH_DISPLAY_CART_SHIPPING = 'tax/cart_display/shipping';

    const XML_PATH_DISPLAY_CART_DISCOUNT = 'tax/cart_display/discount';

    const XML_PATH_DISPLAY_CART_GRANDTOTAL = 'tax/cart_display/grandtotal';

    const XML_PATH_DISPLAY_CART_FULL_SUMMARY = 'tax/cart_display/full_summary';

    const XML_PATH_DISPLAY_CART_ZERO_TAX = 'tax/cart_display/zero_tax';

    /**
     * Shopping cart display settings
     */
    const XML_PATH_DISPLAY_SALES_PRICE = 'tax/sales_display/price';

    const XML_PATH_DISPLAY_SALES_SUBTOTAL = 'tax/sales_display/subtotal';

    const XML_PATH_DISPLAY_SALES_SHIPPING = 'tax/sales_display/shipping';

    const XML_PATH_DISPLAY_SALES_DISCOUNT = 'tax/sales_display/discount';

    const XML_PATH_DISPLAY_SALES_GRANDTOTAL = 'tax/sales_display/grandtotal';

    const XML_PATH_DISPLAY_SALES_FULL_SUMMARY = 'tax/sales_display/full_summary';

    const XML_PATH_DISPLAY_SALES_ZERO_TAX = 'tax/sales_display/zero_tax';

    const CALCULATION_STRING_SEPARATOR = '|';

    const DISPLAY_TYPE_EXCLUDING_TAX = 1;

    const DISPLAY_TYPE_INCLUDING_TAX = 2;

    const DISPLAY_TYPE_BOTH = 3;

    /**
     * @var bool|null
     */
    protected $_priceIncludesTax = null;

    /**
     * Flag which notify what we need use shipping prices exclude tax for calculations
     *
     * @var bool
     */
    protected $_needUseShippingExcludeTax = false;

    /**
     * @var $_shippingPriceIncludeTax bool
     */
    protected $_shippingPriceIncludeTax = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     */
    public function __construct(\Magento\Core\Model\Store\Config $coreStoreConfig)
    {
        $this->_coreStoreConfig = $coreStoreConfig;
    }

    /**
     * Check if prices of product in catalog include tax
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function priceIncludesTax($store = null)
    {
        if (null !== $this->_priceIncludesTax) {
            return $this->_priceIncludesTax;
        }
        return (bool)$this->_coreStoreConfig->getConfig(self::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
    }

    /**
     * Override "price includes tax" variable regardless of system configuration of any store
     *
     * @param bool|null $value
     * @return $this
     */
    public function setPriceIncludesTax($value)
    {
        if (null === $value) {
            $this->_priceIncludesTax = null;
        } else {
            $this->_priceIncludesTax = (bool)$value;
        }
        return $this;
    }

    /**
     * Check what taxes should be applied after discount
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function applyTaxAfterDiscount($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $store);
    }

    /**
     * Get product price display type
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getPriceDisplayType($store = null)
    {
        return (int)$this->_coreStoreConfig->getConfig(self::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, $store);
    }

    /**
     * Get configuration setting "Apply Discount On Prices Including Tax" value
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function discountTax($store = null)
    {
        return (int)$this->_coreStoreConfig->getConfig(self::CONFIG_XML_PATH_DISCOUNT_TAX, $store) == 1;
    }

    /**
     * Get taxes/discounts calculation sequence.
     * This sequence depends on "Apply Customer Tax" and "Apply Discount On Prices" configuration options.
     *
     * @param   null|int|string|Store $store
     * @return  string
     */
    public function getCalculationSequence($store = null)
    {
        if ($this->applyTaxAfterDiscount($store)) {
            if ($this->discountTax($store)) {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL;
            } else {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL;
            }
        } else {
            if ($this->discountTax($store)) {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL;
            } else {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL;
            }
        }
        return $seq;
    }

    /**
     * Specify flag what we need use shipping price exclude tax
     *
     * @param   bool $flag
     * @return  \Magento\Tax\Model\Config
     */
    public function setNeedUseShippingExcludeTax($flag)
    {
        $this->_needUseShippingExcludeTax = $flag;
        return $this;
    }

    /**
     * Get flag what we need use shipping price exclude tax
     *
     * @return bool $flag
     */
    public function getNeedUseShippingExcludeTax()
    {
        return $this->_needUseShippingExcludeTax;
    }

    /**
     * Get defined tax calculation agorithm
     *
     * @param   null|string|bool|int|Store $store
     * @return  string
     */
    public function getAlgorithm($store = null)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_ALGORITHM, $store);
    }

    /**
     * Get tax class id specified for shipping tax estimation
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getShippingTaxClass($store = null)
    {
        return (int)$this->_coreStoreConfig->getConfig(self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);
    }

    /**
     * Get shipping methods prices display type
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getShippingPriceDisplayType($store = null)
    {
        return (int)$this->_coreStoreConfig->getConfig(self::CONFIG_XML_PATH_DISPLAY_SHIPPING, $store);
    }

    /**
     * Check if shipping prices include tax
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function shippingPriceIncludesTax($store = null)
    {
        if ($this->_shippingPriceIncludeTax === null) {
            $this->_shippingPriceIncludeTax = (bool)$this->_coreStoreConfig->getConfig(
                self::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX,
                $store
            );
        }
        return $this->_shippingPriceIncludeTax;
    }

    /**
     * Declare shipping prices type
     *
     * @param bool $flag
     * @return $this
     */
    public function setShippingPriceIncludeTax($flag)
    {
        $this->_shippingPriceIncludeTax = $flag;
        return $this;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartPricesInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_PRICE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartPricesExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_PRICE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartPricesBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_PRICE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartSubtotalInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_SUBTOTAL,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartSubtotalExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_SUBTOTAL,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartSubtotalBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_SUBTOTAL,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartShippingInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_SHIPPING,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartShippingExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_SHIPPING,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartShippingBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_SHIPPING,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartDiscountInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_DISCOUNT,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartDiscountExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_DISCOUNT,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartDiscountBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_CART_DISCOUNT,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartTaxWithGrandTotal($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_DISPLAY_CART_GRANDTOTAL, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartFullSummary($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_DISPLAY_CART_FULL_SUMMARY, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartZeroTax($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_DISPLAY_CART_ZERO_TAX, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesPricesInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_PRICE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesPricesExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_PRICE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesPricesBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_PRICE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesSubtotalInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_SUBTOTAL,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesSubtotalExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_SUBTOTAL,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesSubtotalBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_SUBTOTAL,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesShippingInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_SHIPPING,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesShippingExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_SHIPPING,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesShippingBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_SHIPPING,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesDiscountInclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_DISCOUNT,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalestDiscountExclTax($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_DISCOUNT,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesDiscountBoth($store = null)
    {
        return $this->_coreStoreConfig->getConfig(
            self::XML_PATH_DISPLAY_SALES_DISCOUNT,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesTaxWithGrandTotal($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_DISPLAY_SALES_GRANDTOTAL, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesFullSummary($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_DISPLAY_SALES_FULL_SUMMARY, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesZeroTax($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_DISPLAY_SALES_ZERO_TAX, $store);
    }
}
