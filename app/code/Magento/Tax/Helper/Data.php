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

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Customer\Model\Address;
use Magento\Tax\Model\Config;
use Magento\Tax\Service\V1\Data\QuoteDetailsBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder as QuoteDetailsItemBuilder;
use Magento\Tax\Service\V1\Data\TaxClassKeyBuilder;
use Magento\Tax\Service\V1\TaxCalculationServiceInterface;
use Magento\Customer\Model\Address\Converter as AddressConverter;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Tax\Service\V1\OrderTaxServiceInterface;

/**
 * Catalog data helper
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
     * @var \Magento\Framework\StoreManagerInterface
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
     * Quote details builder
     *
     * @var QuoteDetailsBuilder
     */
    protected $quoteDetailsBuilder;

    /**
     * Quote details item builder
     *
     * @var QuoteDetailsItemBuilder
     */
    protected $quoteDetailsItemBuilder;

    /**
     * Tax calculation service
     *
     * @var TaxCalculationServiceInterface
     */
    protected $taxCalculationService;

    /**
     * Address converter
     *
     * @var AddressConverter
     */
    protected $addressConverter;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * TaxClassKey builder
     *
     * @var TaxClassKeyBuilder
     */
    protected $taxClassKeyBuilder;

    /**
     * \Magento\Catalog\Helper\Data
     *
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Tax\Service\V1\OrderTaxServiceInterface
     */
    protected $orderTaxService;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $taxConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $taxItemFactory
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param QuoteDetailsBuilder $quoteDetailsBuilder
     * @param QuoteDetailsItemBuilder $quoteDetailsItemBuilder
     * @param TaxClassKeyBuilder $taxClassKeyBuilder
     * @param TaxCalculationServiceInterface $taxCalculationService
     * @param CustomerSession $customerSession
     * @param AddressConverter $addressConverter
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param OrderTaxServiceInterface $orderTaxService
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Config $taxConfig,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $taxItemFactory,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $orderTaxCollectionFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        QuoteDetailsBuilder $quoteDetailsBuilder,
        QuoteDetailsItemBuilder $quoteDetailsItemBuilder,
        TaxClassKeyBuilder $taxClassKeyBuilder,
        TaxCalculationServiceInterface $taxCalculationService,
        CustomerSession $customerSession,
        AddressConverter $addressConverter,
        \Magento\Catalog\Helper\Data $catalogHelper,
        OrderTaxServiceInterface $orderTaxService,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);
        $this->priceCurrency = $priceCurrency;
        $this->_scopeConfig = $scopeConfig;
        $this->_config = $taxConfig;
        $this->_coreData = $coreData;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->_localeFormat = $localeFormat;
        $this->_attributeFactory = $attributeFactory;
        $this->_taxItemFactory = $taxItemFactory;
        $this->_orderTaxCollectionFactory = $orderTaxCollectionFactory;
        $this->_localeResolver = $localeResolver;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->quoteDetailsItemBuilder = $quoteDetailsItemBuilder;
        $this->taxClassKeyBuilder = $taxClassKeyBuilder;
        $this->taxCalculationService = $taxCalculationService;
        $this->customerSession = $customerSession;
        $this->addressConverter = $addressConverter;
        $this->catalogHelper = $catalogHelper;
        $this->orderTaxService = $orderTaxService;
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
     * Get product price including store convertion rate
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|string $format
     * @return float|string
     * @deprecated
     */
    public function getProductPrice($product, $format = null)
    {
        try {
            $value = $product->getPrice();
            $value = $format ? $this->priceCurrency->convertAndFormat($value) : $this->priceCurrency->convert($value);
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
        return $this->_config->needPriceConversion($store);
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
     *      'title'             => $title,
     *      'percent'           => $percent
     *  )
     * )
     *
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Model\Order\Invoice|\Magento\Sales\Model\Order\Creditmemo $source
     * @return array
     */
    public function getCalculatedTaxes($source)
    {
        $taxClassAmount = [];
        if (empty($source)) {
            return $taxClassAmount;
        }
        $current = $source;
        if ($source instanceof \Magento\Sales\Model\Order\Invoice
            || $source instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            $source = $current->getOrder();
        }
        if ($current == $source) {
            $orderTaxDetails = $this->orderTaxService->getOrderTaxDetails($current->getId());
            $appliedTaxes = $orderTaxDetails->getAppliedTaxes();
            foreach ($appliedTaxes as $appliedTax) {
                $taxCode = $appliedTax->getCode();
                $taxClassAmount[$taxCode]['tax_amount'] = $appliedTax->getAmount();
                $taxClassAmount[$taxCode]['base_tax_amount'] = $appliedTax->getBaseAmount();
                $taxClassAmount[$taxCode]['title'] = $appliedTax->getTitle();
                $taxClassAmount[$taxCode]['percent'] = $appliedTax->getPercent();
            }
        } else {
            $orderTaxDetails = $this->orderTaxService->getOrderTaxDetails($source->getId());

            // Apply any taxes for shipping
            $shippingTaxAmount = $current->getShippingTaxAmount();
            $originalShippingTaxAmount = $source->getShippingTaxAmount();
            if ($shippingTaxAmount && $originalShippingTaxAmount &&
                $shippingTaxAmount != 0 && $originalShippingTaxAmount != 0) {
                //An invoice or credit memo can have a different qty than its order
                $shippingRatio = $shippingTaxAmount / $originalShippingTaxAmount;
                $itemTaxDetails = $orderTaxDetails->getItems();
                foreach ($itemTaxDetails as $itemTaxDetail) {
                    //Aggregate taxable items associated with shipping
                    if ($itemTaxDetail->getType() == \Magento\Sales\Model\Quote\Address::TYPE_SHIPPING) {
                        $taxClassAmount = $this->_aggregateTaxes($taxClassAmount, $itemTaxDetail, $shippingRatio);
                    }
                }
            }

            // Apply any taxes for the items
            /** @var $item \Magento\Sales\Model\Order\Invoice\Item|\Magento\Sales\Model\Order\Creditmemo\Item */
            foreach ($current->getItemsCollection() as $item) {
                $orderItem = $item->getOrderItem();
                $orderItemId = $orderItem->getId();
                $orderItemTax = $orderItem->getTaxAmount();
                $itemTax = $item->getTaxAmount();
                if (!$itemTax || !$orderItemTax) {
                    continue;
                }
                //An invoiced item or credit memo item can have a different qty than its order item qty
                $itemRatio = $itemTax / $orderItemTax;
                $itemTaxDetails = $orderTaxDetails->getItems();
                foreach ($itemTaxDetails as $itemTaxDetail) {
                    //Aggregate taxable items associated with an item
                    if ($itemTaxDetail->getItemId() == $orderItemId
                        || $itemTaxDetail->getAssociatedItemId() == $orderItemId) {
                        $taxClassAmount = $this->_aggregateTaxes($taxClassAmount, $itemTaxDetail, $itemRatio);
                    }
                }
            }
        }

        // Finish
        foreach ($taxClassAmount as $key => $tax) {
            if ($tax['tax_amount'] == 0 && $tax['base_tax_amount'] == 0) {
                unset($taxClassAmount[$key]);
            } else {
                $taxClassAmount[$key]['tax_amount'] = $this->priceCurrency->round($tax['tax_amount']);
                $taxClassAmount[$key]['base_tax_amount'] = $this->priceCurrency->round($tax['base_tax_amount']);
            }
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
     * @param array $taxClassAmount
     * @param array $itemTaxDetail
     * @param float $ratio
     * @return array
     */
    private function _aggregateTaxes($taxClassAmount, $itemTaxDetail, $ratio)
    {
        $itemAppliedTaxes = $itemTaxDetail->getAppliedTaxes();
        foreach ($itemAppliedTaxes as $itemAppliedTax) {
            $taxCode = $itemAppliedTax->getCode();
            if (!isset($taxClassAmount[$taxCode])) {
                $taxClassAmount[$taxCode]['title'] = $itemAppliedTax->getTitle();
                $taxClassAmount[$taxCode]['percent'] = $itemAppliedTax->getPercent();
                $taxClassAmount[$taxCode]['tax_amount'] = $itemAppliedTax->getAmount() * $ratio;
                $taxClassAmount[$taxCode]['base_tax_amount'] = $itemAppliedTax->getBaseAmount() * $ratio;
            } else {
                $taxClassAmount[$taxCode]['tax_amount'] += $itemAppliedTax->getAmount() * $ratio;
                $taxClassAmount[$taxCode]['base_tax_amount'] += $itemAppliedTax->getBaseAmount() * $ratio;
            }
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
