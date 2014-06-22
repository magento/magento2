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
namespace Magento\Tax\Helper;

use Magento\Store\Model\Store;
use Magento\Customer\Model\Address;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;

/**
 * Catalog data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Price conversion constant for positive
     */
    const PRICE_CONVERSION_PLUS = 1;

    /**
     * Price conversion constant for negative
     */
    const PRICE_CONVERSION_MINUS = 2;

    /**
     * Default tax class for customers
     */
    const CONFIG_DEFAULT_CUSTOMER_TAX_CLASS = 'tax/classes/default_customer_tax_class';

    /**
     * Default tax class for products
     */
    const CONFIG_DEFAULT_PRODUCT_TAX_CLASS = 'tax/classes/default_product_tax_class';

    /**
     * Tax configuration object
     *
     * @var Config
     */
    protected $_config;

    /**
     * Tax calculator
     *
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculation;

    /**
     * Postcode cut to this length when creating search templates
     *
     * @var integer
     */
    protected $_postCodeSubStringLength = 10;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory
     */
    protected $_taxItemFactory;

    /**
     * @var \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory
     */
    protected $_orderTaxCollectionFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $taxConfig
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $taxItemFactory
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Config $taxConfig,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $taxItemFactory,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_config = $taxConfig;
        $this->_coreData = $coreData;
        $this->_coreRegistry = $coreRegistry;
        $this->_calculation = $calculation;
        $this->_storeManager = $storeManager;
        $this->_localeFormat = $localeFormat;
        $this->_attributeFactory = $attributeFactory;
        $this->_taxItemFactory = $taxItemFactory;
        $this->_orderTaxCollectionFactory = $orderTaxCollectionFactory;
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Return max postcode length to create search templates
     *
     * @return int $len
     */
    public function getPostCodeSubStringLength()
    {
        $len = (int) $this->_postCodeSubStringLength;
        if ($len <= 0) {
            $len = 10;
        }
        return $len;
    }

    /**
     * Get tax configuration object
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Get tax calculation object
     *
     * @return \Magento\Tax\Model\Calculation
     */
    public function getCalculator()
    {
        return $this->_calculation;
    }

    /**
     * Get product price including store convertion rate
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|string $format
     * @return float|string
     */
    public function getProductPrice($product, $format = null)
    {
        try {
            $value = $product->getPrice();
            $value = $this->_storeManager->getStore()->convertPrice($value, $format);
        } catch (\Exception $e) {
            $value = $e->getMessage();
        }
        return $value;
    }

    /**
     * Check if product prices inputed include tax
     *
     * @param   null|int|string|Store $store
     * @return  bool
     */
    public function priceIncludesTax($store = null)
    {
        return $this->_config->priceIncludesTax($store) || $this->_config->getNeedUseShippingExcludeTax();
    }

    /**
     * Check what taxes should be applied after discount
     *
     * @param   null|int|string|Store $store
     * @return  bool
     */
    public function applyTaxAfterDiscount($store = null)
    {
        return $this->_config->applyTaxAfterDiscount($store);
    }

    /**
     * Retrieves the "including tax" or "excluding tax" label
     *
     * @param bool $flag
     * @return string
     */
    public function getIncExcText($flag)
    {
        return $flag ? __('Incl. Tax') : __('Excl. Tax');
    }

    /**
     * Get product price display type
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param null|int|string|Store $store
     * @return int
     */
    public function getPriceDisplayType($store = null)
    {
        return $this->_config->getPriceDisplayType($store);
    }

    /**
     * Check if necessary do product price conversion
     * If it necessary will be returned conversion type (minus or plus)
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function needPriceConversion($store = null)
    {
        $res = false;
        if ($this->priceIncludesTax($store)) {
            switch ($this->getPriceDisplayType($store)) {
                case Config::DISPLAY_TYPE_EXCLUDING_TAX:
                case Config::DISPLAY_TYPE_BOTH:
                    return self::PRICE_CONVERSION_MINUS;
                case Config::DISPLAY_TYPE_INCLUDING_TAX:
                    $res = true;
                    break;
                default:
                    break;
            }
        } else {
            switch ($this->getPriceDisplayType($store)) {
                case Config::DISPLAY_TYPE_INCLUDING_TAX:
                case Config::DISPLAY_TYPE_BOTH:
                    return self::PRICE_CONVERSION_PLUS;
                case Config::DISPLAY_TYPE_EXCLUDING_TAX:
                    $res = false;
                    break;
                default:
                    break;
            }
        }

        if ($res === false) {
            $res = $this->displayTaxColumn();
        }
        return $res;
    }

    /**
     * Check if need display full tax summary information in totals block
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displayFullSummary($store = null)
    {
        return $this->_config->displayCartFullSummary($store);
    }

    /**
     * Check if need display zero tax in subtotal
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displayZeroTax($store = null)
    {
        return $this->_config->displayCartZeroTax($store);
    }

    /**
     * Check if need display cart prices included tax
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displayCartPriceInclTax($store = null)
    {
        return $this->_config->displayCartPricesInclTax($store);
    }

    /**
     * Check if need display cart prices excluding price
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displayCartPriceExclTax($store = null)
    {
        return $this->_config->displayCartPricesExclTax($store);
    }

    /**
     * Check if need display cart prices excluding and including tax
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displayCartBothPrices($store = null)
    {
        return $this->_config->displayCartPricesBoth($store);
    }

    /**
     * Check if need display order prices included tax
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displaySalesPriceInclTax($store = null)
    {
        return $this->_config->displaySalesPricesInclTax($store);
    }

    /**
     * Check if need display order prices excluding price
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displaySalesPriceExclTax($store = null)
    {
        return $this->_config->displaySalesPricesExclTax($store);
    }

    /**
     * Check if need display order prices excluding and including tax
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displaySalesBothPrices($store = null)
    {
        return $this->_config->displaySalesPricesBoth($store);
    }

    /**
     * Check if we need display price include and exclude tax for order/invoice subtotal
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displaySalesSubtotalBoth($store = null)
    {
        return $this->_config->displaySalesSubtotalBoth($store);
    }

    /**
     * Check if we need display price include tax for order/invoice subtotal
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displaySalesSubtotalInclTax($store = null)
    {
        return $this->_config->displaySalesSubtotalInclTax($store);
    }

    /**
     * Check if we need display price exclude tax for order/invoice subtotal
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function displaySalesSubtotalExclTax($store = null)
    {
        return $this->_config->displaySalesSubtotalExclTax($store);
    }

    /**
     * Check if need display tax column in for shopping cart/order items
     *
     * @return bool
     */
    public function displayTaxColumn()
    {
        return $this->_config->displayCartPricesBoth();
    }

    /**
     * Get prices javascript format json
     *
     * @param null|int|string|Store $store
     * @return string
     */
    public function getPriceFormat($store = null)
    {
        $this->_localeResolver->emulate($store);
        $priceFormat = $this->_localeFormat->getPriceFormat();
        $this->_localeResolver->revert();
        if ($store) {
            $priceFormat['pattern'] = $this->_storeManager->getStore($store)->getCurrentCurrency()->getOutputFormat();
        }
        return $this->_coreData->jsonEncode($priceFormat);
    }

    /**
     * Get all tax rates JSON for all product tax classes of specific store
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getAllRatesByProductClass($store = null)
    {
        return $this->_getAllRatesByProductClass($store);
    }

    /**
     * Get all tax rates JSON for all product tax classes of specific store
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    protected function _getAllRatesByProductClass($store = null)
    {
        $result = array();
        $defaultRate = $this->_calculation->getDefaultRateRequest($store);
        $rates = $this->_calculation->getRatesForAllProductTaxClasses($defaultRate);
        foreach ($rates as $class => $rate) {
            $result["value_{$class}"] = $rate;
        }
        return $this->_coreData->jsonEncode($result);
    }

    /**
     * Get unrounded product price
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   float $price inputed product price
     * @param   bool $includingTax return price include tax flag
     * @param   null|Address $shippingAddress
     * @param   null|Address $billingAddress
     * @param   null|int $ctc customer tax class
     * @param   null|string|bool|int|Store $store
     * @param   bool $priceIncludesTax flag what price parameter contain tax
     * @return  float
     */
    public function getPriceUnrounded(
        $product,
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $billingAddress = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null
    ) {
        return $this->getPrice(
            $product,
            $price,
            $includingTax,
            $shippingAddress,
            $billingAddress,
            $ctc,
            $store,
            $priceIncludesTax,
            false
        );
    }

    /**
     * Get product price with all tax settings processing
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   float $price inputed product price
     * @param   bool $includingTax return price include tax flag
     * @param   null|Address $shippingAddress
     * @param   null|Address $billingAddress
     * @param   null|int $ctc customer tax class
     * @param   null|string|bool|int|Store $store
     * @param   bool $priceIncludesTax flag what price parameter contain tax
     * @param   bool $roundPrice
     * @return  float
     */
    public function getPrice(
        $product,
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $billingAddress = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null,
        $roundPrice = true
    ) {
        if (!$price) {
            return $price;
        }
        $store = $this->_storeManager->getStore($store);
        if (!$this->needPriceConversion($store)) {
            return $store->roundPrice($price);
        }
        if (is_null($priceIncludesTax)) {
            $priceIncludesTax = $this->priceIncludesTax($store);
        }

        $percent = $product->getTaxPercent();
        $includingPercent = null;

        $taxClassId = $product->getTaxClassId();
        if (is_null($percent)) {
            if ($taxClassId) {
                $request = $this->_calculation->getRateRequest($shippingAddress, $billingAddress, $ctc, $store);
                $percent = $this->_calculation->getRate($request->setProductClassId($taxClassId));
            }
        }
        if ($taxClassId && $priceIncludesTax) {
            if ($this->isCrossBorderTradeEnabled($store)) {
                $includingPercent = $percent;
            } else {
                $request = $this->_calculation->getRateOriginRequest($store);
                $includingPercent = $this->_calculation->getRate($request->setProductClassId($taxClassId));
            }
        }

        if ($percent === false || is_null($percent)) {
            if ($priceIncludesTax && !$includingPercent) {
                return $price;
            }
        }

        $product->setTaxPercent($percent);
        if ($product->getAppliedRates() == null) {
            $request = $this->_calculation->getRateRequest($shippingAddress, $billingAddress, $ctc, $store);
            $request->setProductClassId($taxClassId);
            $appliedRates = $this->_calculation->getAppliedRates($request);
            $product->setAppliedRates($appliedRates);
        }

        if (!is_null($includingTax)) {
            if ($priceIncludesTax) {
                if ($includingTax) {
                    /**
                     * Recalculate price include tax in case of different rates.  Otherwise price remains the same.
                     */
                    if ($includingPercent != $percent) {
                        // determine the customer's price that includes tax
                        $price = $this->_calculatePriceInclTax($price, $includingPercent, $percent, $store);
                    }
                } else {
                    $price = $this->_calculatePrice($price, $includingPercent, false);
                }
            } else {
                if ($includingTax) {
                    $appliedRates = $product->getAppliedRates();
                    if (count($appliedRates) > 1) {
                        $price = $this->_calculatePriceInclTaxWithMultipleRates($price, $appliedRates);
                    } else {
                        $price = $this->_calculatePrice($price, $percent, true);
                    }
                }
            }
        } else {
            if ($priceIncludesTax) {
                switch ($this->getPriceDisplayType($store)) {
                    case Config::DISPLAY_TYPE_EXCLUDING_TAX:
                    case Config::DISPLAY_TYPE_BOTH:
                        if ($includingPercent != $percent) {
                            // determine the customer's price that includes tax
                            $taxablePrice = $this->_calculatePriceInclTax($price, $includingPercent, $percent, $store);
                            // determine the customer's tax amount,
                            // round tax unless $roundPrice is set explicitly to false
                            $tax = $this->_calculation->calcTaxAmount($taxablePrice, $percent, true, $roundPrice);
                            // determine the customer's price without taxes
                            $price = $taxablePrice - $tax;
                        } else {
                            //round tax first unless $roundPrice is set to false explicitly
                            $price = $this->_calculatePrice($price, $includingPercent, false, $roundPrice);
                        }
                        break;
                    case Config::DISPLAY_TYPE_INCLUDING_TAX:
                        $price = $this->_calculatePrice($price, $includingPercent, false);
                        $price = $this->_calculatePrice($price, $percent, true);
                        break;
                    default:
                        break;
                }
            } else {
                switch ($this->getPriceDisplayType($store)) {
                    case Config::DISPLAY_TYPE_INCLUDING_TAX:
                        $appliedRates = $product->getAppliedRates();
                        if (count($appliedRates) > 1) {
                            $price = $this->_calculatePriceInclTaxWithMultipleRates($price, $appliedRates);
                        } else {
                            $price = $this->_calculatePrice($price, $percent, true);
                        }
                        break;
                    case Config::DISPLAY_TYPE_BOTH:
                    case Config::DISPLAY_TYPE_EXCLUDING_TAX:
                        break;
                    default:
                        break;
                }
            }
        }
        if ($roundPrice) {
            return $store->roundPrice($price);
        } else {
            return $price;
        }
    }

    /**
     * Given a store price that includes tax at the store rate, this function will back out the store's tax, and add in
     * the customer's tax.  Returns this new price which is the customer's price including tax.
     *
     * @param float $storePriceInclTax
     * @param float $storePercent
     * @param float $customerPercent
     * @param null|int|string|Store $store
     * @return float
     */
    protected function _calculatePriceInclTax($storePriceInclTax, $storePercent, $customerPercent, $store)
    {
        $priceExclTax         = $this->_calculatePrice($storePriceInclTax, $storePercent, false, false);
        $customerTax          = $this->_calculation->calcTaxAmount($priceExclTax, $customerPercent, false, false);
        $customerPriceInclTax = $store->roundPrice($priceExclTax + $customerTax);
        return $customerPriceInclTax;
    }

    /**
     * Check if we have display in catalog prices including tax
     *
     * @return bool
     */
    public function displayPriceIncludingTax()
    {
        return $this->getPriceDisplayType() == Config::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * Check if we have display in catalog prices excluding tax
     *
     * @return bool
     */
    public function displayPriceExcludingTax()
    {
        return $this->getPriceDisplayType() == Config::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Check if we have display in catalog prices including and excluding tax
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displayBothPrices($store = null)
    {
        return $this->getPriceDisplayType($store) == Config::DISPLAY_TYPE_BOTH;
    }

    /**
     * Calculate price including/excluding tax based on tax rate percent
     *
     * @param   float $price
     * @param   float $percent
     * @param   bool  $type - true to calculate the price including tax or false if calculating price to exclude tax
     * @param   bool  $roundTaxFirst
     * @return  float
     */
    protected function _calculatePrice($price, $percent, $type, $roundTaxFirst = false)
    {
        if ($type) {
            $taxAmount = $this->_calculation->calcTaxAmount($price, $percent, false, $roundTaxFirst);
            return $price + $taxAmount;
        } else {
            $taxAmount = $this->_calculation->calcTaxAmount($price, $percent, true, $roundTaxFirst);
            return $price - $taxAmount;
        }
    }

    /**
     * Calculate price including tax when multiple taxes is applied and rounded independently.
     *
     * @param  float $price
     * @param  array $appliedRates
     * @return float
     */
    protected function _calculatePriceInclTaxWithMultipleRates($price, $appliedRates)
    {
        $tax = 0;
        foreach ($appliedRates as $appliedRate) {
            $taxRate = $appliedRate['percent'];
            $tax += $this->_calculation->round($price * $taxRate / 100);
        }
        return $tax + $price;
    }

    /**
     * Returns the include / exclude tax label
     *
     * @param  bool $flag
     * @return string
     */
    public function getIncExcTaxLabel($flag)
    {
        $text = $this->getIncExcText($flag);
        return $text ? ' <span class="tax-flag">(' . $text . ')</span>' : '';
    }

    /**
     * Check if shipping prices include tax
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function shippingPriceIncludesTax($store = null)
    {
        return $this->_config->shippingPriceIncludesTax($store);
    }

    /**
     * Get shipping price display type
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getShippingPriceDisplayType($store = null)
    {
        return $this->_config->getShippingPriceDisplayType($store);
    }

    /**
     * Returns whether the shipping price should display with taxes included
     *
     * @return bool
     */
    public function displayShippingPriceIncludingTax()
    {
        return $this->getShippingPriceDisplayType() == Config::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * Returns whether the shipping price should display without taxes
     *
     * @return bool
     */
    public function displayShippingPriceExcludingTax()
    {
        return $this->getShippingPriceDisplayType() == Config::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Returns whether the shipping price should display both with and without taxes
     *
     * @return bool
     */
    public function displayShippingBothPrices()
    {
        return $this->getShippingPriceDisplayType() == Config::DISPLAY_TYPE_BOTH;
    }

    /**
     * Get tax class id specified for shipping tax estimation
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getShippingTaxClass($store)
    {
        return $this->_config->getShippingTaxClass($store);
    }

    /**
     * Get shipping price
     *
     * @param float $price
     * @param bool|null $includingTax
     * @param Address|null $shippingAddress
     * @param int|null $ctc
     * @param null|string|bool|int|Store $store
     * @return float
     */
    public function getShippingPrice($price, $includingTax = null, $shippingAddress = null, $ctc = null, $store = null)
    {
        $pseudoProduct = new \Magento\Framework\Object();
        $pseudoProduct->setTaxClassId($this->getShippingTaxClass($store));

        $billingAddress = false;
        if ($shippingAddress && $shippingAddress->getQuote() && $shippingAddress->getQuote()->getBillingAddress()) {
            $billingAddress = $shippingAddress->getQuote()->getBillingAddress();
        }

        $price = $this->getPrice(
            $pseudoProduct,
            $price,
            $includingTax,
            $shippingAddress,
            $billingAddress,
            $ctc,
            $store,
            $this->shippingPriceIncludesTax($store)
        );
        return $price;
    }

    /**
     * Returns the SQL for the price tax
     *
     * @param string $priceField
     * @param string $taxClassField
     * @return string
     */
    public function getPriceTaxSql($priceField, $taxClassField)
    {
        if (!$this->priceIncludesTax() && $this->displayPriceExcludingTax()) {
            return '';
        }

        $request = $this->_calculation->getDefaultRateRequest();
        $defaultTaxes = $this->_calculation->getRatesForAllProductTaxClasses($request);

        $request = $this->_calculation->getRateRequest();
        $currentTaxes = $this->_calculation->getRatesForAllProductTaxClasses($request);

        $defaultTaxString = $currentTaxString = '';

        $rateToVariable = array(
            'defaultTaxString' => 'defaultTaxes',
            'currentTaxString' => 'currentTaxes',
        );
        foreach ($rateToVariable as $rateVariable => $rateArray) {
            if (${$rateArray} && is_array(${$rateArray})) {
                ${$rateVariable} = '';
                foreach (${$rateArray} as $classId => $rate) {
                    if ($rate) {
                        ${$rateVariable} .= sprintf("WHEN %d THEN %12.4F ", $classId, $rate / 100);
                    }
                }
                if (${$rateVariable}) {
                    ${$rateVariable} = "CASE {$taxClassField} {${$rateVariable}} ELSE 0 END";
                }
            }
        }

        $result = '';

        if ($this->priceIncludesTax()) {
            if ($defaultTaxString) {
                $result = "-({$priceField}/(1+({$defaultTaxString}))*{$defaultTaxString})";
            }
            if (!$this->displayPriceExcludingTax() && $currentTaxString) {
                $result .= "+(({$priceField}{$result})*{$currentTaxString})";
            }
        } else {
            if ($this->displayPriceIncludingTax()) {
                if ($currentTaxString) {
                    $result .= "+({$priceField}*{$currentTaxString})";
                }
            }
        }
        return $result;
    }

    /**
     * Join tax class
     * @param \Magento\Framework\DB\Select $select
     * @param int $storeId
     * @param string $priceTable
     * @return $this
     */
    public function joinTaxClass($select, $storeId, $priceTable = 'main_table')
    {
        /** @var $taxClassAttribute \Magento\Eav\Model\Entity\Attribute */
        $taxClassAttribute = $this->_attributeFactory->create();
        $taxClassAttribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'tax_class_id');
        $joinConditionD = implode(
            ' AND ',
            array(
                "tax_class_d.entity_id = {$priceTable}.entity_id",
                $select->getAdapter()->quoteInto('tax_class_d.attribute_id = ?', (int) $taxClassAttribute->getId()),
                'tax_class_d.store_id = 0'
            )
        );
        $joinConditionC = implode(
            ' AND ',
            array(
                "tax_class_c.entity_id = {$priceTable}.entity_id",
                $select->getAdapter()->quoteInto('tax_class_c.attribute_id = ?', (int) $taxClassAttribute->getId()),
                $select->getAdapter()->quoteInto('tax_class_c.store_id = ?', (int) $storeId)
            )
        );
        $select
            ->joinLeft(
                array('tax_class_d' => $taxClassAttribute->getBackend()->getTable()),
                $joinConditionD,
                array()
            )->joinLeft(
                array('tax_class_c' => $taxClassAttribute->getBackend()->getTable()),
                $joinConditionC,
                array()
            );

        return $this;
    }

    /**
     * Get configuration setting "Apply Discount On Prices Including Tax" value
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function discountTax($store = null)
    {
        return $this->_config->discountTax($store);
    }

    /**
     * Get value of "Apply Tax On" custom/original price configuration settings
     *
     * @param null|string|bool|int|Store $store
     * @return string|null
     */
    public function getTaxBasedOn($store = null)
    {
        return $this->_scopeConfig->getValue(
            Config::CONFIG_XML_PATH_BASED_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if tax can be applied to custom price
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function applyTaxOnCustomPrice($store = null)
    {
        return (int)$this->_scopeConfig->getValue(
            Config::CONFIG_XML_PATH_APPLY_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == 0;
    }

    /**
     * Check if tax should be applied just to original price
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function applyTaxOnOriginalPrice($store = null)
    {
        return (int)$this->_scopeConfig->getValue(
            Config::CONFIG_XML_PATH_APPLY_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == 1;
    }

    /**
     * Get taxes/discounts calculation sequence
     *
     * This sequence depends on "Catalog price include tax", "Apply Tax After Discount"
     * and "Apply Discount On Prices Including Tax" configuration options.
     *
     * @param null|int|string|Store $store
     * @return string
     */
    public function getCalculationSequence($store = null)
    {
        return $this->_config->getCalculationSequence($store);
    }

    /**
     * Get tax calculation algorithm code
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCalculationAgorithm($store = null)
    {
        return $this->_config->getAlgorithm($store);
    }

    /**
     * Get calculated taxes for each tax class
     *
     * This method returns array with format:
     * array(
     *  $index => array(
     *      'tax_amount'        => $taxAmount,
     *      'base_tax_amount'   => $baseTaxAmount,
     *      'hidden_tax_amount' => $hiddenTaxAmount,
     *      'title'             => $title,
     *      'percent'           => $percent
     *  )
     * )
     *
     * @param \Magento\Sales\Model\Order $source
     * @return array
     */
    public function getCalculatedTaxes($source)
    {
        if ($this->_coreRegistry->registry('current_invoice')) {
            $current = $this->_coreRegistry->registry('current_invoice');
        } elseif ($this->_coreRegistry->registry('current_creditmemo')) {
            $current = $this->_coreRegistry->registry('current_creditmemo');
        } else {
            $current = $source;
        }

        $taxClassAmount = array();
        if ($current && $source) {
            if ($current == $source) {
                // use the actuals
                $rates = $this->_getTaxRateSubtotals($source);
                foreach ($rates['items'] as $rate) {
                    $taxClassId = $rate['tax_id'];
                    $taxClassAmount[$taxClassId]['tax_amount'] = $rate['amount'];
                    $taxClassAmount[$taxClassId]['base_tax_amount'] = $rate['base_amount'];
                    $taxClassAmount[$taxClassId]['title'] = $rate['title'];
                    $taxClassAmount[$taxClassId]['percent'] = $rate['percent'];
                }
            } else {
                // regenerate tax subtotals
                // Calculate taxes for shipping
                $shippingTaxAmount = $current->getShippingTaxAmount();
                if ($shippingTaxAmount) {
                    $shippingTax    = $this->getShippingTax($current);
                    $taxClassAmount = array_merge($taxClassAmount, $shippingTax);
                }

                /** @var $item \Magento\Sales\Model\Order\Item */
                foreach ($current->getItemsCollection() as $item) {
                    /** @var $taxCollection \Magento\Tax\Model\Resource\Sales\Order\Tax\Item */
                    $taxCollection = $this->_taxItemFactory->create();
                    $taxCollection->getTaxItemsByItemId(
                        $item->getOrderItemId() ? $item->getOrderItemId() : $item->getItemId()
                    );

                    foreach ($taxCollection as $tax) {
                        $taxClassId = $tax['tax_id'];
                        $percent = $tax['tax_percent'];

                        $price = $item->getRowTotal();
                        $basePrice = $item->getBaseRowTotal();
                        if ($this->applyTaxAfterDiscount($item->getStoreId())) {
                            $price = $price - $item->getDiscountAmount() + $item->getHiddenTaxAmount();
                            $basePrice = $basePrice - $item->getBaseDiscountAmount() + $item->getBaseHiddenTaxAmount();
                        }
                        $taxAmount = $price * $percent / 100;
                        $baseTaxAmount = $basePrice * $percent / 100;

                        if (isset($taxClassAmount[$taxClassId])) {
                            $taxClassAmount[$taxClassId]['tax_amount'] += $taxAmount;
                            $taxClassAmount[$taxClassId]['base_tax_amount'] += $baseTaxAmount;
                        } else {
                            $taxClassAmount[$taxClassId]['tax_amount'] = $taxAmount;
                            $taxClassAmount[$taxClassId]['base_tax_amount'] = $baseTaxAmount;
                            $taxClassAmount[$taxClassId]['title'] = $tax['title'];
                            $taxClassAmount[$taxClassId]['percent'] = $tax['percent'];
                        }
                    }
                }
            }

            foreach ($taxClassAmount as $key => $tax) {
                if ($tax['tax_amount'] == 0 && $tax['base_tax_amount'] == 0) {
                    unset($taxClassAmount[$key]);
                }
            }

            $taxClassAmount = array_values($taxClassAmount);
        }

        return $taxClassAmount;
    }

    /**
     * Returns the array of tax rates for the order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _getTaxRateSubtotals($order)
    {
        return $this->_orderTaxCollectionFactory->create()->loadByOrder($order)->toArray();
    }

    /**
     * Get calculated Shipping & Handling Tax
     *
     * This method returns array with format:
     * array(
     *  $index => array(
     *      'tax_amount'        => $taxAmount,
     *      'base_tax_amount'   => $baseTaxAmount,
     *      'hidden_tax_amount' => $hiddenTaxAmount
     *      'title'             => $title
     *      'percent'           => $percent
     *  )
     * )
     *
     * @param \Magento\Sales\Model\Order $source
     * @return array
     */
    public function getShippingTax($source)
    {
        if ($this->_coreRegistry->registry('current_invoice')) {
            $current = $this->_coreRegistry->registry('current_invoice');
        } elseif ($this->_coreRegistry->registry('current_creditmemo')) {
            $current = $this->_coreRegistry->registry('current_creditmemo');
        } else {
            $current = $source;
        }

        $taxClassAmount = array();
        if ($current && $source) {
            if ($current->getShippingTaxAmount() != 0 && $current->getBaseShippingTaxAmount() != 0) {
                $taxClassAmount[0]['tax_amount'] = $current->getShippingTaxAmount();
                $taxClassAmount[0]['base_tax_amount'] = $current->getBaseShippingTaxAmount();
                if ($current->getShippingHiddenTaxAmount() > 0) {
                    $taxClassAmount[0]['hidden_tax_amount'] = $current->getShippingHiddenTaxAmount();
                }
                $taxClassAmount[0]['title'] = __('Shipping & Handling Tax');
                $taxClassAmount[0]['percent'] = null;
            }
        }
        return $taxClassAmount;
    }

    /**
     * Retrieve default customer tax class from config
     *
     * @return string|null
     */
    public function getDefaultCustomerTaxClass()
    {
        return $this->_scopeConfig->getValue(
            self::CONFIG_DEFAULT_CUSTOMER_TAX_CLASS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve default product tax class from config
     *
     * @return string|null
     */
    public function getDefaultProductTaxClass()
    {
        return $this->_scopeConfig->getValue(
            self::CONFIG_DEFAULT_PRODUCT_TAX_CLASS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return whether cross border trade is enabled or not
     *
     * @param   null|int|string|Store $store
     * @return  bool
     */
    public function isCrossBorderTradeEnabled($store = null)
    {
        return (bool)$this->_config->crossBorderTradeEnabled($store);
    }
}
