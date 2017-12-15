<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Customer\Model\Address;
use Magento\Tax\Model\Config;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use Magento\Sales\Model\EntityInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Tax helper
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
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
     * Postcode cut to this length when creating search templates
     *
     * @var integer
     */
    protected $_postCodeSubStringLength = 10;

    /**
     * Json Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory
     */
    protected $_orderTaxCollectionFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var OrderTaxManagementInterface
     */
    protected $orderTaxManagement;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Config $taxConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param OrderTaxManagementInterface $orderTaxManagement
     * @param PriceCurrencyInterface $priceCurrency
     * @param Json $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Config $taxConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Catalog\Helper\Data $catalogHelper,
        OrderTaxManagementInterface $orderTaxManagement,
        PriceCurrencyInterface $priceCurrency,
        Json $serializer = null
    ) {
        parent::__construct($context);
        $this->priceCurrency = $priceCurrency;
        $this->_config = $taxConfig;
        $this->jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
        $this->_localeFormat = $localeFormat;
        $this->_orderTaxCollectionFactory = $orderTaxCollectionFactory;
        $this->_localeResolver = $localeResolver;
        $this->catalogHelper = $catalogHelper;
        $this->orderTaxManagement = $orderTaxManagement;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
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
     * Check if product prices inputed include tax
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function priceIncludesTax($store = null)
    {
        return $this->_config->priceIncludesTax($store) || $this->_config->getNeedUseShippingExcludeTax();
    }

    /**
     * Check what taxes should be applied after discount
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function applyTaxAfterDiscount($store = null)
    {
        return $this->_config->applyTaxAfterDiscount($store);
    }

    /**
     * Get product price display type
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param  null|int|string|Store $store
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
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function needPriceConversion($store = null)
    {
        return $this->_config->needPriceConversion($store);
    }

    /**
     * Check if need display full tax summary information in totals block
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displayFullSummary($store = null)
    {
        return $this->_config->displayCartFullSummary($store);
    }

    /**
     * Check if need display zero tax in subtotal
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displayZeroTax($store = null)
    {
        return $this->_config->displayCartZeroTax($store);
    }

    /**
     * Check if need display cart prices included tax
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displayCartPriceInclTax($store = null)
    {
        return $this->_config->displayCartPricesInclTax($store);
    }

    /**
     * Check if need display cart prices excluding price
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displayCartPriceExclTax($store = null)
    {
        return $this->_config->displayCartPricesExclTax($store);
    }

    /**
     * Check if need display cart prices excluding and including tax
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displayCartBothPrices($store = null)
    {
        return $this->_config->displayCartPricesBoth($store);
    }

    /**
     * Check if need display order prices included tax
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displaySalesPriceInclTax($store = null)
    {
        return $this->_config->displaySalesPricesInclTax($store);
    }

    /**
     * Check if need display order prices excluding price
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displaySalesPriceExclTax($store = null)
    {
        return $this->_config->displaySalesPricesExclTax($store);
    }

    /**
     * Check if need display order prices excluding and including tax
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displaySalesBothPrices($store = null)
    {
        return $this->_config->displaySalesPricesBoth($store);
    }

    /**
     * Check if we need display price include and exclude tax for order/invoice subtotal
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displaySalesSubtotalBoth($store = null)
    {
        return $this->_config->displaySalesSubtotalBoth($store);
    }

    /**
     * Check if we need display price include tax for order/invoice subtotal
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displaySalesSubtotalInclTax($store = null)
    {
        return $this->_config->displaySalesSubtotalInclTax($store);
    }

    /**
     * Check if we need display price exclude tax for order/invoice subtotal
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function displaySalesSubtotalExclTax($store = null)
    {
        return $this->_config->displaySalesSubtotalExclTax($store);
    }

    /**
     * Get prices javascript format json
     *
     * @param  null|int|string|Store $store
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

        return $this->jsonHelper->jsonEncode($priceFormat);
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
     * Check if shipping prices include tax
     *
     * @param  null|string|bool|int|Store $store
     * @return bool
     */
    public function shippingPriceIncludesTax($store = null)
    {
        return $this->_config->shippingPriceIncludesTax($store);
    }

    /**
     * Get shipping price display type
     *
     * @param  null|string|bool|int|Store $store
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
     * @param  null|string|bool|int|Store $store
     * @return int
     */
    public function getShippingTaxClass($store)
    {
        return $this->_config->getShippingTaxClass($store);
    }

    /**
     * Get shipping price
     *
     * @param  float                      $price
     * @param  bool|null                  $includingTax
     * @param  Address|null               $shippingAddress
     * @param  int|null                   $ctc
     * @param  null|string|bool|int|Store $store
     * @return float
     */
    public function getShippingPrice($price, $includingTax = null, $shippingAddress = null, $ctc = null, $store = null)
    {
        $pseudoProduct = new \Magento\Framework\DataObject();
        $pseudoProduct->setTaxClassId($this->getShippingTaxClass($store));

        $billingAddress = false;
        if ($shippingAddress && $shippingAddress->getQuote() && $shippingAddress->getQuote()->getBillingAddress()) {
            $billingAddress = $shippingAddress->getQuote()->getBillingAddress();
        }

        $price = $this->catalogHelper->getTaxPrice(
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
     * Get configuration setting "Apply Discount On Prices Including Tax" value
     *
     * @param  null|string|bool|int|Store $store
     * @return bool
     */
    public function discountTax($store = null)
    {
        return $this->_config->discountTax($store);
    }

    /**
     * Get value of "Apply Tax On" custom/original price configuration settings
     *
     * @param  null|string|bool|int|Store $store
     * @return string|null
     */
    public function getTaxBasedOn($store = null)
    {
        return $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_BASED_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if tax can be applied to custom price
     *
     * @param  null|string|bool|int|Store $store
     * @return bool
     */
    public function applyTaxOnCustomPrice($store = null)
    {
        return (int) $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_APPLY_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == 0;
    }

    /**
     * Check if tax should be applied just to original price
     *
     * @param  null|string|bool|int|Store $store
     * @return bool
     */
    public function applyTaxOnOriginalPrice($store = null)
    {
        return (int) $this->scopeConfig->getValue(
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
     * @param  null|int|string|Store $store
     * @return string
     */
    public function getCalculationSequence($store = null)
    {
        return $this->_config->getCalculationSequence($store);
    }

    /**
     * Get tax calculation algorithm code
     *
     * @param  null|string|bool|int|Store $store
     * @return string
     */
    public function getCalculationAlgorithm($store = null)
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
     *      'title'             => $title,
     *      'percent'           => $percent
     *  )
     * )
     *
     * @param  \Magento\Sales\Model\Order|\Magento\Sales\Model\Order\Invoice|\Magento\Sales\Model\Order\Creditmemo $source
     * @return array
     */
    public function getCalculatedTaxes($source)
    {
        $taxClassAmount = [];
        if (empty($source)) {
            return $taxClassAmount;
        }
        $current = $source;
        if ($source instanceof Invoice || $source instanceof Creditmemo) {
            $source = $current->getOrder();
        }
        if ($current == $source) {
            $taxClassAmount = $this->calculateTaxForOrder($current);
        } else {
            $taxClassAmount = $this->calculateTaxForItems($source, $current);
        }

        foreach ($taxClassAmount as $key => $tax) {
            $taxClassAmount[$key]['tax_amount'] = $this->priceCurrency->round($tax['tax_amount']);
            $taxClassAmount[$key]['base_tax_amount'] = $this->priceCurrency->round($tax['base_tax_amount']);
        }

        return array_values($taxClassAmount);
    }

    /**
     * Accumulates the pre-calculated taxes for each tax class
     *
     * This method accepts and returns the 'taxClassAmount' array with format:
     * array(
     *  $index => array(
     *      'tax_amount'        => $taxAmount,
     *      'base_tax_amount'   => $baseTaxAmount,
     *      'title'             => $title,
     *      'percent'           => $percent
     *  )
     * )
     *
     * @param  array                        $taxClassAmount
     * @param  OrderTaxDetailsItemInterface $itemTaxDetail
     * @param  float                        $ratio
     * @return array
     */
    private function _aggregateTaxes($taxClassAmount, OrderTaxDetailsItemInterface $itemTaxDetail, $ratio)
    {
        $itemAppliedTaxes = $itemTaxDetail->getAppliedTaxes();
        foreach ($itemAppliedTaxes as $itemAppliedTax) {
            $taxAmount = $itemAppliedTax->getAmount() * $ratio;
            $baseTaxAmount = $itemAppliedTax->getBaseAmount() * $ratio;

            if (0 == $taxAmount && 0 == $baseTaxAmount) {
                continue;
            }
            $taxCode = $itemAppliedTax->getCode();
            if (!isset($taxClassAmount[$taxCode])) {
                $taxClassAmount[$taxCode]['title'] = $itemAppliedTax->getTitle();
                $taxClassAmount[$taxCode]['percent'] = $itemAppliedTax->getPercent();
                $taxClassAmount[$taxCode]['tax_amount'] = $taxAmount;
                $taxClassAmount[$taxCode]['base_tax_amount'] = $baseTaxAmount;
            } else {
                $taxClassAmount[$taxCode]['tax_amount'] += $taxAmount;
                $taxClassAmount[$taxCode]['base_tax_amount'] += $baseTaxAmount;
            }
        }

        return $taxClassAmount;
    }

    /**
     * Returns the array of tax rates for the order
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _getTaxRateSubtotals($order)
    {
        return $this->_orderTaxCollectionFactory->create()->loadByOrder($order)->toArray();
    }

    /**
     * Retrieve default customer tax class from config
     *
     * @return string|null
     */
    public function getDefaultCustomerTaxClass()
    {
        return $this->scopeConfig->getValue(
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
        return $this->scopeConfig->getValue(
            self::CONFIG_DEFAULT_PRODUCT_TAX_CLASS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return whether cross border trade is enabled or not
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function isCrossBorderTradeEnabled($store = null)
    {
        return (bool) $this->_config->crossBorderTradeEnabled($store);
    }

    /**
     * @param  EntityInterface $current
     * @return array
     */
    protected function calculateTaxForOrder(EntityInterface $current)
    {
        $taxClassAmount = [];

        $orderTaxDetails = $this->orderTaxManagement->getOrderTaxDetails($current->getId());
        $appliedTaxes = $orderTaxDetails->getAppliedTaxes();
        foreach ($appliedTaxes as $appliedTax) {
            $taxCode = $appliedTax->getCode();
            $taxClassAmount[$taxCode]['tax_amount'] = $appliedTax->getAmount();
            $taxClassAmount[$taxCode]['base_tax_amount'] = $appliedTax->getBaseAmount();
            $taxClassAmount[$taxCode]['title'] = $appliedTax->getTitle();
            $taxClassAmount[$taxCode]['percent'] = $appliedTax->getPercent();
        }

        return $taxClassAmount;
    }

    /**
     * @param  EntityInterface $order
     * @param  EntityInterface $salesItem
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function calculateTaxForItems(EntityInterface $order, EntityInterface $salesItem)
    {
        $taxClassAmount = [];

        $orderTaxDetails = $this->orderTaxManagement->getOrderTaxDetails($order->getId());

        // Apply any taxes for the items
        /** @var $item \Magento\Sales\Model\Order\Invoice\Item|\Magento\Sales\Model\Order\Creditmemo\Item */
        foreach ($salesItem->getItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemId = $orderItem->getId();
            $orderItemTax = $orderItem->getTaxAmount();
            $itemTax = $item->getTaxAmount();
            if (!$itemTax || !floatval($orderItemTax)) {
                continue;
            }
            //An invoiced item or credit memo item can have a different qty than its order item qty
            $itemRatio = $itemTax / $orderItemTax;
            $itemTaxDetails = $orderTaxDetails->getItems();
            foreach ($itemTaxDetails as $itemTaxDetail) {
                //Aggregate taxable items associated with an item
                if ($itemTaxDetail->getItemId() == $orderItemId) {
                    $taxClassAmount = $this->_aggregateTaxes($taxClassAmount, $itemTaxDetail, $itemRatio);
                } elseif ($itemTaxDetail->getAssociatedItemId() == $orderItemId) {
                    $taxableItemType = $itemTaxDetail->getType();
                    $ratio = $itemRatio;
                    if ($item->getTaxRatio()) {
                        $taxRatio = $this->serializer->unserialize($item->getTaxRatio());
                        if (isset($taxRatio[$taxableItemType])) {
                            $ratio = $taxRatio[$taxableItemType];
                        }
                    }
                    $taxClassAmount = $this->_aggregateTaxes($taxClassAmount, $itemTaxDetail, $ratio);
                }
            }
        }

        // Apply any taxes for shipping
        $shippingTaxAmount = $salesItem->getShippingTaxAmount();
        $originalShippingTaxAmount = $order->getShippingTaxAmount();
        if ($shippingTaxAmount && $originalShippingTaxAmount &&
            $shippingTaxAmount != 0 && floatval($originalShippingTaxAmount)
        ) {
            //An invoice or credit memo can have a different qty than its order
            $shippingRatio = $shippingTaxAmount / $originalShippingTaxAmount;
            $itemTaxDetails = $orderTaxDetails->getItems();
            foreach ($itemTaxDetails as $itemTaxDetail) {
                //Aggregate taxable items associated with shipping
                if ($itemTaxDetail->getType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
                    $taxClassAmount = $this->_aggregateTaxes($taxClassAmount, $itemTaxDetail, $shippingRatio);
                }
            }
        }

        return $taxClassAmount;
    }

    /**
     * Check whether display price is affected by different tax rates
     *
     * @param null|int|string|Store $store
     * @return bool
     */
    public function isCatalogPriceDisplayAffectedByTax($store = null)
    {
        if ($this->displayBothPrices($store)) {
            return true;
        }

        $priceInclTax = $this->priceIncludesTax($store);
        if ($priceInclTax) {
            return ($this->isCrossBorderTradeEnabled($store) xor $this->displayPriceIncludingTax());
        } else {
            return $this->displayPriceIncludingTax();
        }
    }
}
