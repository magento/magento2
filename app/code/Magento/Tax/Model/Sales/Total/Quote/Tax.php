<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Customer\Api\Data\AddressDataBuilder as CustomerAddressBuilder;
use Magento\Customer\Api\Data\RegionDataBuilder as CustomerAddressRegionBuilder;
use Magento\Sales\Model\Quote\Address;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Calculation;

/**
 * Tax totals calculation model
 */
class Tax extends CommonTaxCollector
{
    /**
     * Counter
     *
     * @var int
     */
    protected $counter = 0;

    /**
     * Tax module helper
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * Hidden taxes array
     *
     * @var array
     */
    protected $_hiddenTaxes = [];

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Magento\Tax\Api\Data\QuoteDetailsDataBuilder $quoteDetailsBuilder
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $quoteDetailsItemBuilder
     * @param \Magento\Tax\Api\Data\TaxClassKeyDataBuilder $taxClassKeyBuilder
     * @param CustomerAddressBuilder $customerAddressBuilder
     * @param CustomerAddressRegionBuilder $customerAddressRegionBuilder
     * @param \Magento\Tax\Helper\Data $taxData
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsDataBuilder $quoteDetailsBuilder,
        \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $quoteDetailsItemBuilder,
        \Magento\Tax\Api\Data\TaxClassKeyDataBuilder $taxClassKeyBuilder,
        CustomerAddressBuilder $customerAddressBuilder,
        CustomerAddressRegionBuilder $customerAddressRegionBuilder,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->setCode('tax');
        $this->_taxData = $taxData;
        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsBuilder,
            $quoteDetailsItemBuilder,
            $taxClassKeyBuilder,
            $customerAddressBuilder,
            $customerAddressRegionBuilder
        );
    }

    /**
     * Collect tax totals for quote address
     *
     * @param   Address $address
     * @return  $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $this->clearValues($address);
        $items = $this->_getAddressItems($address);
        if (!$items) {
            return $this;
        }

        $baseTaxDetails = $this->getQuoteTaxDetails($address, true);
        $taxDetails = $this->getQuoteTaxDetails($address, false);

        //Populate address and items with tax calculation results
        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);
        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($address, $itemsByType[self::ITEM_TYPE_PRODUCT]);
        }

        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
            $baseShippingTaxDetails =
                $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
            $this->processShippingTaxInfo($address, $shippingTaxDetails, $baseShippingTaxDetails);
        }

        //Process taxable items that are not product or shipping
        $this->processExtraTaxables($address, $itemsByType);

        //Save applied taxes for each item and the quote in aggregation
        $this->processAppliedTaxes($address, $itemsByType);

        if ($this->includeExtraTax()) {
            $this->_addAmount($address->getExtraTaxAmount());
            $this->_addBaseAmount($address->getBaseExtraTaxAmount());
        }

        return $this;
    }

    /**
     * Clear tax related total values in address
     *
     * @param Address $address
     * @return void
     */
    protected function clearValues(Address $address)
    {
        $address->setTotalAmount('subtotal', 0);
        $address->setBaseTotalAmount('subtotal', 0);
        $address->setTotalAmount('tax', 0);
        $address->setBaseTotalAmount('tax', 0);
        $address->setTotalAmount('hidden_tax', 0);
        $address->setBaseTotalAmount('hidden_tax', 0);
        $address->setTotalAmount('shipping_hidden_tax', 0);
        $address->setBaseTotalAmount('shipping_hidden_tax', 0);
        $address->setSubtotalInclTax(0);
        $address->setBaseSubtotalInclTax(0);
    }

    /**
     * Call tax calculation service to get tax details on the quote and items
     *
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface
     */
    protected function getQuoteTaxDetails($address, $useBaseCurrency)
    {
        //Setup taxable items
        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());
        $itemDataObjects = $this->mapItems($address, $priceIncludesTax, $useBaseCurrency);

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($address, $useBaseCurrency);
        if ($shippingDataObject != null) {
            $itemDataObjects[] = $shippingDataObject;
        }

        //process extra taxable items associated only with quote
        $quoteExtraTaxables = $this->mapQuoteExtraTaxables(
            $this->quoteDetailsItemBuilder,
            $address,
            $useBaseCurrency
        );
        if (!empty($quoteExtraTaxables)) {
            $itemDataObjects = array_merge($itemDataObjects, $quoteExtraTaxables);
        }

        //Preparation for calling taxCalculationService
        $quoteDetails = $this->prepareQuoteDetails($address, $itemDataObjects);

        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        return $taxDetails;
    }

    /**
     * Map extra taxables associated with quote
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $itemBuilder
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapQuoteExtraTaxables(
        \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $itemBuilder,
        Address $address,
        $useBaseCurrency
    ) {
        $itemDataObjects = [];
        $extraTaxables = $address->getAssociatedTaxables();
        if (!$extraTaxables) {
            return [];
        }

        foreach ($extraTaxables as $extraTaxable) {
            $itemBuilder->setCode($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_CODE]);
            $itemBuilder->setType($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TYPE]);
            $itemBuilder->setQuantity($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_QUANTITY]);
            $itemBuilder->setTaxClassKey(
                $this->taxClassKeyBuilder->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID])
                    ->create()
            );
            if ($useBaseCurrency) {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE];
            } else {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE];
            }
            $itemBuilder->setUnitPrice($unitPrice);
            $itemBuilder->setTaxIncluded($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX]);
            $itemBuilder->setAssociatedItemCode($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE]);
            $itemDataObjects[] = $itemBuilder->create();
        }

        return $itemDataObjects;
    }

    /**
     * Process everything other than product or shipping, save the result in quote
     *
     * @param Address $address
     * @param array $itemsByType
     * @return $this
     */
    protected function processExtraTaxables(Address $address, Array $itemsByType)
    {
        $extraTaxableDetails = [];
        foreach ($itemsByType as $itemType => $itemTaxDetails) {
            if ($itemType != self::ITEM_TYPE_PRODUCT and $itemType != self::ITEM_TYPE_SHIPPING) {
                foreach ($itemTaxDetails as $itemCode => $itemTaxDetail) {
                    /** @var \Magento\Tax\Api\Data\TaxDetailsInterface $taxDetails */
                    $taxDetails = $itemTaxDetail[self::KEY_ITEM];
                    /** @var \Magento\Tax\Api\Data\TaxDetailsInterface $baseTaxDetails */
                    $baseTaxDetails = $itemTaxDetail[self::KEY_BASE_ITEM];

                    $appliedTaxes = $taxDetails->getAppliedTaxes();
                    $baseAppliedTaxes = $baseTaxDetails->getAppliedTaxes();

                    $associatedItemCode = $taxDetails->getAssociatedItemCode();

                    $appliedTaxesArray = $this->convertAppliedTaxes($appliedTaxes, $baseAppliedTaxes);
                    $extraTaxableDetails[$itemType][$associatedItemCode][] = [
                        self::KEY_TAX_DETAILS_TYPE => $taxDetails->getType(),
                        self::KEY_TAX_DETAILS_CODE => $taxDetails->getCode(),
                        self::KEY_TAX_DETAILS_PRICE_EXCL_TAX => $taxDetails->getPrice(),
                        self::KEY_TAX_DETAILS_PRICE_INCL_TAX => $taxDetails->getPriceInclTax(),
                        self::KEY_TAX_DETAILS_BASE_PRICE_EXCL_TAX => $baseTaxDetails->getPrice(),
                        self::KEY_TAX_DETAILS_BASE_PRICE_INCL_TAX => $baseTaxDetails->getPriceInclTax(),
                        self::KEY_TAX_DETAILS_ROW_TOTAL => $taxDetails->getRowTotal(),
                        self::KEY_TAX_DETAILS_ROW_TOTAL_INCL_TAX => $taxDetails->getRowTotalInclTax(),
                        self::KEY_TAX_DETAILS_BASE_ROW_TOTAL => $baseTaxDetails->getRowTotal(),
                        self::KEY_TAX_DETAILS_BASE_ROW_TOTAL_INCL_TAX => $baseTaxDetails->getRowTotalInclTax(),
                        self::KEY_TAX_DETAILS_TAX_PERCENT => $taxDetails->getTaxPercent(),
                        self::KEY_TAX_DETAILS_ROW_TAX => $taxDetails->getRowTax(),
                        self::KEY_TAX_DETAILS_BASE_ROW_TAX => $baseTaxDetails->getRowTax(),
                        self::KEY_TAX_DETAILS_APPLIED_TAXES => $appliedTaxesArray,
                    ];

                    $address->addTotalAmount('tax', $taxDetails->getRowTax());
                    $address->addBaseTotalAmount('tax', $baseTaxDetails->getRowTax());
                    //TODO: save applied taxes for the item
                }
            }
        }

        $address->setExtraTaxableDetails($extraTaxableDetails);
        return $this;
    }

    /**
     * Add tax totals information to address object
     *
     * @param   Address $address
     * @return  $this
     */
    public function fetch(Address $address)
    {
        $applied = $address->getAppliedTaxes();
        $store = $address->getQuote()->getStore();
        $amount = $address->getTaxAmount();

        $items = $this->_getAddressItems($address);
        $discountTaxCompensation = 0;
        foreach ($items as $item) {
            $discountTaxCompensation += $item->getDiscountTaxCompensation();
        }
        $taxAmount = $amount + $discountTaxCompensation;

        $area = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $address->getGrandTotal()) {
            $area = 'taxes';
        }

        if ($amount != 0 || $this->_config->displayCartZeroTax($store)) {
            $address->addTotal(
                [
                    'code' => $this->getCode(),
                    'title' => __('Tax'),
                    'full_info' => $applied ? $applied : [],
                    'value' => $amount,
                    'area' => $area,
                ]
            );
        }

        $store = $address->getQuote()->getStore();
        /**
         * Modify subtotal
         */
        if ($this->_config->displayCartSubtotalBoth($store) || $this->_config->displayCartSubtotalInclTax($store)) {
            if ($address->getSubtotalInclTax() > 0) {
                $subtotalInclTax = $address->getSubtotalInclTax();
            } else {
                $subtotalInclTax = $address->getSubtotal() + $taxAmount - $address->getShippingTaxAmount();
            }

            $address->addTotal(
                [
                    'code' => 'subtotal',
                    'title' => __('Subtotal'),
                    'value' => $subtotalInclTax,
                    'value_incl_tax' => $subtotalInclTax,
                    'value_excl_tax' => $address->getSubtotal(),
                ]
            );
        }

        return $this;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        $calculationSequence = $this->_taxData->getCalculationSequence($store);
        switch ($calculationSequence) {
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $config['before'][] = 'discount';
                break;
            default:
                $config['after'][] = 'discount';
                break;
        }
        return $config;
    }

    /**
     * Get Tax label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Tax');
    }
}
