<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Customer\Api\Data\AddressDataBuilder as CustomerAddressBuilder;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Api\Data\RegionDataBuilder as CustomerAddressRegionBuilder;
use Magento\Framework\Object;
use Magento\Sales\Model\Quote\Address as QuoteAddress;
use Magento\Sales\Model\Quote\Address\Total\AbstractTotal;
use Magento\Sales\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\QuoteDetailsDataBuilder;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;

/**
 * Tax totals calculation model
 */
class CommonTaxCollector extends AbstractTotal
{
    /**#@+
     * Constants defined for type of items
     */
    const ITEM_TYPE_SHIPPING = 'shipping';
    const ITEM_TYPE_PRODUCT = 'product';
    /**#@-*/

    /**
     * Constant for shipping item code
     */
    const ITEM_CODE_SHIPPING = 'shipping';

    /**#@+
     * Constants for array keys
     */
    const KEY_ITEM = 'item';
    const KEY_BASE_ITEM = 'base_item';
    /**#@-*/

    /**#@+
     * Constants for fields in associated taxables array
     */
    const KEY_ASSOCIATED_TAXABLE_TYPE = 'type';
    const KEY_ASSOCIATED_TAXABLE_CODE = 'code';
    const KEY_ASSOCIATED_TAXABLE_UNIT_PRICE = 'unit_price';
    const KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE = 'base_unit_price';
    const KEY_ASSOCIATED_TAXABLE_QUANTITY = 'quantity';
    const KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID = 'tax_class_id';
    const KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX = 'price_includes_tax';
    const KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE = 'associated_item_code';
    /**#@-*/

    /**
     * When an extra taxable item is associated with quote and not with an item, this value
     * is used as associated item code
     */
    const ASSOCIATION_ITEM_CODE_FOR_QUOTE = 'quote';

    /**#@+
     * Constants for fields in tax details for associated taxable items
     */
    const KEY_TAX_DETAILS_TYPE = 'type';
    const KEY_TAX_DETAILS_CODE = 'code';
    const KEY_TAX_DETAILS_PRICE_EXCL_TAX = 'price_excl_tax';
    const KEY_TAX_DETAILS_BASE_PRICE_EXCL_TAX = 'base_price_excl_tax';
    const KEY_TAX_DETAILS_PRICE_INCL_TAX = 'price_incl_tax';
    const KEY_TAX_DETAILS_BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const KEY_TAX_DETAILS_ROW_TOTAL = 'row_total_excl_tax';
    const KEY_TAX_DETAILS_BASE_ROW_TOTAL = 'base_row_total_excl_tax';
    const KEY_TAX_DETAILS_ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const KEY_TAX_DETAILS_BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const KEY_TAX_DETAILS_TAX_PERCENT = 'tax_percent';
    const KEY_TAX_DETAILS_ROW_TAX = 'row_tax';
    const KEY_TAX_DETAILS_BASE_ROW_TAX = 'base_row_tax';
    const KEY_TAX_DETAILS_APPLIED_TAXES = 'applied_taxes';
    /**#@-*/

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * Counter that is used to construct temporary ids for taxable items
     *
     * @var int
     */
    protected $counter = 0;

    /**
     * Tax calculation service, the collector will call the service which performs the actual calculation
     *
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $taxCalculationService;

    /**
     * Builder to create QuoteDetails as input to tax calculation service
     *
     * @var \Magento\Tax\Api\Data\QuoteDetailsDataBuilder
     */
    protected $quoteDetailsBuilder;

    /**
     * @var CustomerAddressBuilder
     */
    protected $customerAddressBuilder;

    /**
     * @var CustomerAddressRegionBuilder
     */
    protected $customerAddressRegionBuilder;

    /**
     * @var \Magento\Tax\Api\Data\TaxClassKeyDataBuilder
     */
    protected $taxClassKeyBuilder;

    /**
     * @var \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder
     */
    protected $quoteDetailsItemBuilder;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param QuoteDetailsDataBuilder $quoteDetailsBuilder
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $quoteDetailsItemBuilder
     * @param \Magento\Tax\Api\Data\TaxClassKeyDataBuilder $taxClassKeyBuilder
     * @param CustomerAddressBuilder $customerAddressBuilder
     * @param CustomerAddressRegionBuilder $customerAddressRegionBuilder
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsDataBuilder $quoteDetailsBuilder,
        \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $quoteDetailsItemBuilder,
        \Magento\Tax\Api\Data\TaxClassKeyDataBuilder $taxClassKeyBuilder,
        CustomerAddressBuilder $customerAddressBuilder,
        CustomerAddressRegionBuilder $customerAddressRegionBuilder
    ) {
        $this->taxCalculationService = $taxCalculationService;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->_config = $taxConfig;
        $this->taxClassKeyBuilder = $taxClassKeyBuilder;
        $this->quoteDetailsItemBuilder = $quoteDetailsItemBuilder;
        $this->customerAddressBuilder = $customerAddressBuilder;
        $this->customerAddressRegionBuilder = $customerAddressRegionBuilder;
    }

    /**
     * Map quote address to customer address
     *
     * @param QuoteAddress $address
     * @return CustomerAddress
     */
    public function mapAddress(QuoteAddress $address)
    {
        $this->customerAddressBuilder->setCountryId($address->getCountryId());
        $this->customerAddressBuilder->setRegion(
            $this->customerAddressRegionBuilder->setRegionId($address->getRegionId())->create()
        );
        $this->customerAddressBuilder->setPostcode($address->getPostcode());
        $this->customerAddressBuilder->setCity($address->getCity());
        $this->customerAddressBuilder->setStreet($address->getStreet());

        return $this->customerAddressBuilder->create();
    }

    /**
     * Map an item to item data object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $itemBuilder
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     */
    public function mapItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $itemBuilder,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        if (!$item->getTaxCalculationItemId()) {
            $sequence = 'sequence-' . $this->getNextIncrement();
            $item->setTaxCalculationItemId($sequence);
        }
        $itemBuilder->setCode($item->getTaxCalculationItemId());
        $itemBuilder->setQuantity($item->getQty());
        $this->taxClassKeyBuilder->setType(TaxClassKeyInterface::TYPE_ID);
        $this->taxClassKeyBuilder->setValue($item->getProduct()->getTaxClassId());
        $itemBuilder->setTaxClassKey($this->taxClassKeyBuilder->create());

        $itemBuilder->setTaxIncluded($priceIncludesTax);
        $itemBuilder->setType(self::ITEM_TYPE_PRODUCT);

        if ($useBaseCurrency) {
            if (!$item->getBaseTaxCalculationPrice()) {
                $item->setBaseTaxCalculationPrice($item->getBaseCalculationPriceOriginal());
            }
            $itemBuilder->setUnitPrice($item->getBaseTaxCalculationPrice());
            $itemBuilder->setDiscountAmount($item->getBaseDiscountAmount());
        } else {
            if (!$item->getTaxCalculationPrice()) {
                $item->setTaxCalculationPrice($item->getCalculationPriceOriginal());
            }
            $itemBuilder->setUnitPrice($item->getTaxCalculationPrice());
            $itemBuilder->setDiscountAmount($item->getDiscountAmount());
        }

        $itemBuilder->setParentCode($parentCode);

        return $itemBuilder->create();
    }

    /**
     * Map item extra taxables
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $itemBuilder
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapItemExtraTaxables(
        \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $itemBuilder,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency
    ) {
        $itemDataObjects = [];
        $extraTaxables = $item->getAssociatedTaxables();
        if (!$extraTaxables) {
            return [];
        }

        foreach ($extraTaxables as $extraTaxable) {
            $extraTaxableIncludesTax =
                isset($extraTaxable['price_includes_tax']) ? $extraTaxable['price_includes_tax'] : $priceIncludesTax;

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
            $itemBuilder->setTaxIncluded($extraTaxableIncludesTax);
            $itemBuilder->setAssociatedItemCode($item->getTaxCalculationItemId());
            $itemDataObjects[] = $itemBuilder->create();
        }

        return $itemDataObjects;
    }

    /**
     * Add quote items to quoteDetailsBuilder
     *
     * @param QuoteAddress $address
     * @param bool $useBaseCurrency
     * @param bool $priceIncludesTax
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapItems(
        QuoteAddress $address,
        $priceIncludesTax,
        $useBaseCurrency
    ) {
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return [];
        }

        //Populate with items
        $itemBuilder = $this->quoteDetailsItemBuilder;
        $itemDataObjects = [];
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentItemDataObject = $this->mapItem($itemBuilder, $item, $priceIncludesTax, $useBaseCurrency);
                $itemDataObjects[] = $parentItemDataObject;
                foreach ($item->getChildren() as $child) {
                    $childItemDataObject = $this->mapItem(
                        $itemBuilder,
                        $child,
                        $priceIncludesTax,
                        $useBaseCurrency,
                        $parentItemDataObject->getCode()
                    );
                    $itemDataObjects[] = $childItemDataObject;
                    $extraTaxableItems = $this->mapItemExtraTaxables(
                        $itemBuilder,
                        $item,
                        $priceIncludesTax,
                        $useBaseCurrency
                    );
                    $itemDataObjects = array_merge($itemDataObjects, $extraTaxableItems);
                }
            } else {
                $itemDataObject = $this->mapItem($itemBuilder, $item, $priceIncludesTax, $useBaseCurrency);
                $itemDataObjects[] = $itemDataObject;
                $extraTaxableItems = $this->mapItemExtraTaxables(
                    $itemBuilder,
                    $item,
                    $priceIncludesTax,
                    $useBaseCurrency
                );
                $itemDataObjects = array_merge($itemDataObjects, $extraTaxableItems);
            }
        }

        return $itemDataObjects;
    }

    /**
     * Populate the quote details builder with address information
     *
     * @param QuoteDetailsDataBuilder $quoteDetailsBuilder
     * @param QuoteAddress $address
     * @return QuoteDetailsDataBuilder
     */
    public function populateAddressData(QuoteDetailsDataBuilder $quoteDetailsBuilder, QuoteAddress $address)
    {
        $this->quoteDetailsBuilder->setBillingAddress($this->mapAddress($address->getQuote()->getBillingAddress()));
        $this->quoteDetailsBuilder->setShippingAddress($this->mapAddress($address));
        return $quoteDetailsBuilder;
    }

    /**
     * @param QuoteAddress $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     */
    public function getShippingDataObject(QuoteAddress $address, $useBaseCurrency)
    {
        if ($address->getShippingTaxCalculationAmount() === null) {
            //Save the original shipping amount because shipping amount will be overridden
            //with shipping amount excluding tax
            $address->setShippingTaxCalculationAmount($address->getShippingAmount());
            $address->setBaseShippingTaxCalculationAmount($address->getBaseShippingAmount());
        }
        if ($address->getShippingTaxCalculationAmount() !== null) {
            $itemBuilder = $this->quoteDetailsItemBuilder;
            $itemBuilder->setType(self::ITEM_TYPE_SHIPPING);
            $itemBuilder->setCode(self::ITEM_CODE_SHIPPING);
            $itemBuilder->setQuantity(1);
            if ($useBaseCurrency) {
                $itemBuilder->setUnitPrice($address->getBaseShippingTaxCalculationAmount());
            } else {
                $itemBuilder->setUnitPrice($address->getShippingTaxCalculationAmount());
            }
            if ($address->getShippingDiscountAmount()) {
                if ($useBaseCurrency) {
                    $itemBuilder->setDiscountAmount($address->getBaseShippingDiscountAmount());
                } else {
                    $itemBuilder->setDiscountAmount($address->getShippingDiscountAmount());
                }
            }
            $this->taxClassKeyBuilder->setType(TaxClassKeyInterface::TYPE_ID);
            $this->taxClassKeyBuilder->setValue($this->_config->getShippingTaxClass($address->getQuote()->getStore()));
            $itemBuilder->setTaxClassKey($this->taxClassKeyBuilder->create());
            $itemBuilder->setTaxIncluded($this->_config->shippingPriceIncludesTax($address->getQuote()->getStore()));
            return $itemBuilder->create();
        }

        return null;
    }

    /**
     * Populate QuoteDetails object from quote address object
     *
     * @param QuoteAddress $address
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $itemDataObjects
     * @return \Magento\Tax\Api\Data\QuoteDetailsInterface
     */
    protected function prepareQuoteDetails(QuoteAddress $address, $itemDataObjects)
    {
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this->quoteDetailsBuilder->create();
        }

        $this->populateAddressData($this->quoteDetailsBuilder, $address);

        $this->taxClassKeyBuilder->setType(TaxClassKeyInterface::TYPE_ID);
        $this->taxClassKeyBuilder->setValue($address->getQuote()->getCustomerTaxClassId());
        //Set customer tax class
        $this->quoteDetailsBuilder->setCustomerTaxClassKey($this->taxClassKeyBuilder->create());
        $this->quoteDetailsBuilder->setItems($itemDataObjects);
        $this->quoteDetailsBuilder->setCustomerId($address->getQuote()->getCustomerId());

        $quoteDetails = $this->quoteDetailsBuilder->create();
        return $quoteDetails;
    }

    /**
     * Organize tax details by type and by item code
     *
     * @param TaxDetailsInterface $taxDetails
     * @param TaxDetailsInterface $baseTaxDetails
     * @return array
     */
    protected function organizeItemTaxDetailsByType(
        TaxDetailsInterface $taxDetails,
        TaxDetailsInterface $baseTaxDetails
    ) {
        /** @var \Magento\Tax\Api\Data\TaxDetailsItemInterface[] $keyedItems */
        $keyedItems = [];
        foreach ($taxDetails->getItems() as $item) {
            $keyedItems[$item->getCode()] = $item;
        }
        /** @var \Magento\Tax\Api\Data\TaxDetailsItemInterface[] $baseKeyedItems */
        $baseKeyedItems = [];
        foreach ($baseTaxDetails->getItems() as $item) {
            $baseKeyedItems[$item->getCode()] = $item;
        }

        $itemsByType = [];
        foreach ($keyedItems as $code => $item) {
            $baseItem = $baseKeyedItems[$code];
            $itemType = $item->getType();
            $itemsByType[$itemType][$code] = [self::KEY_ITEM => $item, self::KEY_BASE_ITEM => $baseItem];
        }

        return $itemsByType;
    }

    /**
     * Process product items in the quote.
     * Set the following aggregated values in the quote object:
     * subtotal, subtotalInclTax, tax, hidden_tax,
     *
     * @param QuoteAddress $address
     * @param array $itemTaxDetails
     * @return $this
     */
    protected function processProductItems(QuoteAddress $address, array $itemTaxDetails)
    {
        /** @var AbstractItem[] $keyedAddressItems */
        $keyedAddressItems = [];
        foreach ($this->_getAddressItems($address) as $addressItem) {
            $keyedAddressItems[$addressItem->getTaxCalculationItemId()] = $addressItem;
        }

        $subtotal = $baseSubtotal = 0;
        $hiddenTax = $baseHiddenTax = 0;
        $tax = $baseTax = 0;
        $subtotalInclTax = $baseSubtotalInclTax = 0;

        foreach ($itemTaxDetails as $code => $itemTaxDetail) {
            /** @var TaxDetailsItemInterface $taxDetail */
            $taxDetail = $itemTaxDetail[self::KEY_ITEM];
            /** @var TaxDetailsItemInterface $baseTaxDetail */
            $baseTaxDetail = $itemTaxDetail[self::KEY_BASE_ITEM];
            $quoteItem = $keyedAddressItems[$code];
            $this->updateItemTaxInfo($quoteItem, $taxDetail, $baseTaxDetail, $address->getQuote()->getStore());

            //Update aggregated values
            if ($quoteItem->getHasChildren() && $quoteItem->isChildrenCalculated()) {
                //avoid double counting
                continue;
            }
            $subtotal += $taxDetail->getRowTotal();
            $baseSubtotal += $baseTaxDetail->getRowTotal();
            $hiddenTax += $taxDetail->getDiscountTaxCompensationAmount();
            $baseHiddenTax += $baseTaxDetail->getDiscountTaxCompensationAmount();
            $tax += $taxDetail->getRowTax();
            $baseTax += $baseTaxDetail->getRowTax();
            $subtotalInclTax += $taxDetail->getRowTotalInclTax();
            $baseSubtotalInclTax += $baseTaxDetail->getRowTotalInclTax();
        }

        //Set aggregated values
        $address->setTotalAmount('subtotal', $subtotal);
        $address->setBaseTotalAmount('subtotal', $baseSubtotal);
        $address->setTotalAmount('tax', $tax);
        $address->setBaseTotalAmount('tax', $baseTax);
        $address->setTotalAmount('hidden_tax', $hiddenTax);
        $address->setBaseTotalAmount('hidden_tax', $baseHiddenTax);

        $address->setSubtotalInclTax($subtotalInclTax);
        $address->setBaseSubtotalInclTax($baseSubtotalInclTax);

        return $this;
    }

    /**
     * Process applied taxes for items and quote
     *
     * @param QuoteAddress $address
     * @param array $itemsByType
     * @return $this
     */
    protected function processAppliedTaxes(QuoteAddress $address, Array $itemsByType)
    {
        $address->setAppliedTaxes([]);
        $allAppliedTaxesArray = [];

        /** @var AbstractItem[] $keyedAddressItems */
        $keyedAddressItems = [];
        foreach ($this->_getAddressItems($address) as $addressItem) {
            $keyedAddressItems[$addressItem->getTaxCalculationItemId()] = $addressItem;
        }

        foreach ($itemsByType as $itemType => $items) {
            foreach ($items as $itemTaxCalculationId => $itemTaxDetails) {
                /** @var TaxDetailsItemInterface $taxDetails */
                $taxDetails = $itemTaxDetails[self::KEY_ITEM];
                $baseTaxDetails = $itemTaxDetails[self::KEY_BASE_ITEM];

                $appliedTaxes = $taxDetails->getAppliedTaxes();
                $baseAppliedTaxes = $baseTaxDetails->getAppliedTaxes();

                $itemType = $taxDetails->getType();
                $itemId = null;
                $associatedItemId = null;
                if ($itemType == self::ITEM_TYPE_PRODUCT) {
                    //Use item id instead of tax calculation id
                    $itemId = $keyedAddressItems[$itemTaxCalculationId]->getId();
                } else {
                    if ($taxDetails->getAssociatedItemCode()
                        && $taxDetails->getAssociatedItemCode() != self::ASSOCIATION_ITEM_CODE_FOR_QUOTE) {
                        //This item is associated with a product item
                        $associatedItemId = $keyedAddressItems[$taxDetails->getAssociatedItemCode()]->getId();
                    } else {
                        //This item is associated with an order, e.g., shipping, etc.
                        $itemId = null;
                    }
                }
                $extraInfo = [
                    'item_id' => $itemId,
                    'item_type' => $itemType,
                    'associated_item_id' => $associatedItemId,
                ];

                $appliedTaxesArray = $this->convertAppliedTaxes($appliedTaxes, $baseAppliedTaxes, $extraInfo);

                if ($itemType == self::ITEM_TYPE_PRODUCT) {
                    $quoteItem = $keyedAddressItems[$itemTaxCalculationId];
                    $quoteItem->setAppliedTaxes($appliedTaxesArray);
                }

                $allAppliedTaxesArray[$itemTaxCalculationId] = $appliedTaxesArray;

                foreach ($appliedTaxesArray as $appliedTaxArray) {
                    $this->_saveAppliedTaxes(
                        $address,
                        [$appliedTaxArray],
                        $appliedTaxArray['amount'],
                        $appliedTaxArray['base_amount'],
                        $appliedTaxArray['percent']
                    );
                }
            }
        }

        $address->setItemsAppliedTaxes($allAppliedTaxesArray);

        return $this;
    }

    /**
     * Update tax related fields for quote item
     *
     * @param AbstractItem $quoteItem
     * @param TaxDetailsItemInterface $itemTaxDetails
     * @param TaxDetailsItemInterface $baseItemTaxDetails
     * @param Store $store
     * @return $this
     */
    public function updateItemTaxInfo($quoteItem, $itemTaxDetails, $baseItemTaxDetails, $store)
    {
        //The price should be base price
        $quoteItem->setPrice($baseItemTaxDetails->getPrice());
        $quoteItem->setConvertedPrice($itemTaxDetails->getPrice());
        $quoteItem->setPriceInclTax($itemTaxDetails->getPriceInclTax());
        $quoteItem->setRowTotal($itemTaxDetails->getRowTotal());
        $quoteItem->setRowTotalInclTax($itemTaxDetails->getRowTotalInclTax());
        $quoteItem->setTaxAmount($itemTaxDetails->getRowTax());
        $quoteItem->setTaxPercent($itemTaxDetails->getTaxPercent());
        $quoteItem->setHiddenTaxAmount($itemTaxDetails->getDiscountTaxCompensationAmount());

        $quoteItem->setBasePrice($baseItemTaxDetails->getPrice());
        $quoteItem->setBasePriceInclTax($baseItemTaxDetails->getPriceInclTax());
        $quoteItem->setBaseRowTotal($baseItemTaxDetails->getRowTotal());
        $quoteItem->setBaseRowTotalInclTax($baseItemTaxDetails->getRowTotalInclTax());
        $quoteItem->setBaseTaxAmount($baseItemTaxDetails->getRowTax());
        $quoteItem->setTaxPercent($baseItemTaxDetails->getTaxPercent());
        $quoteItem->setBaseHiddenTaxAmount($baseItemTaxDetails->getDiscountTaxCompensationAmount());

        //Set discount calculation price, this may be needed by discount collector
        if ($this->_config->discountTax($store)) {
            $quoteItem->setDiscountCalculationPrice($itemTaxDetails->getPriceInclTax());
            $quoteItem->setBaseDiscountCalculationPrice($baseItemTaxDetails->getPriceInclTax());
        } else {
            $quoteItem->setDiscountCalculationPrice($itemTaxDetails->getPrice());
            $quoteItem->setBaseDiscountCalculationPrice($baseItemTaxDetails->getPrice());
        }

        return $this;
    }

    /**
     * Update tax related fields for shipping
     *
     * @param QuoteAddress $address
     * @param TaxDetailsItemInterface $shippingTaxDetails
     * @param TaxDetailsItemInterface $baseShippingTaxDetails
     * @return $this
     */
    protected function processShippingTaxInfo(QuoteAddress $address, $shippingTaxDetails, $baseShippingTaxDetails)
    {
        $address->setTotalAmount('shipping', $shippingTaxDetails->getRowTotal());
        $address->setBaseTotalAmount('shipping', $baseShippingTaxDetails->getRowTotal());
        $address->setTotalAmount('shipping_hidden_tax', $shippingTaxDetails->getDiscountTaxCompensationAmount());
        $address->setBaseTotalAmount('shipping_hidden_tax', $baseShippingTaxDetails->getDiscountTaxCompensationAmount());

        $address->setShippingInclTax($shippingTaxDetails->getRowTotalInclTax());
        $address->setBaseShippingInclTax($baseShippingTaxDetails->getRowTotalInclTax());
        $address->setShippingTaxAmount($shippingTaxDetails->getRowTax());
        $address->setBaseShippingTaxAmount($baseShippingTaxDetails->getRowTax());

        //Add the shipping tax to total tax amount
        $address->addTotalAmount('tax', $shippingTaxDetails->getRowTax());
        $address->addBaseTotalAmount('tax', $baseShippingTaxDetails->getRowTax());

        if ($this->_config->discountTax($address->getQuote()->getStore())) {
            $address->setShippingAmountForDiscount($shippingTaxDetails->getRowTotalInclTax());
            $address->setBaseShippingAmountForDiscount($baseShippingTaxDetails->getRowTotalInclTax());
        }

        return $this;
    }

    /**
     * Convert appliedTax data object from tax calculation service to internal array format
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $baseAppliedTaxes
     * @param array $extraInfo
     * @return array
     */
    public function convertAppliedTaxes($appliedTaxes, $baseAppliedTaxes, $extraInfo = [])
    {
        $appliedTaxesArray = [];

        if (!$appliedTaxes || !$baseAppliedTaxes) {
            return $appliedTaxesArray;
        }

        foreach ($appliedTaxes as $taxId => $appliedTax) {
            $baseAppliedTax = $baseAppliedTaxes[$taxId];
            $rateDataObjects = $appliedTax->getRates();

            $rates = [];
            foreach ($rateDataObjects as $rateDataObject) {
                $rates[] = [
                    'percent' => $rateDataObject->getPercent(),
                    'code' => $rateDataObject->getCode(),
                    'title' => $rateDataObject->getTitle(),
                ];
            }

            $appliedTaxArray = [
                'amount' => $appliedTax->getAmount(),
                'base_amount' => $baseAppliedTax->getAmount(),
                'percent' => $appliedTax->getPercent(),
                'id' => $appliedTax->getTaxRateKey(),
                'rates' => $rates,
            ];
            if (!empty($extraInfo)) {
                $appliedTaxArray = array_merge($appliedTaxArray, $extraInfo);
            }

            $appliedTaxesArray[] = $appliedTaxArray;
        }

        return $appliedTaxesArray;
    }

    /**
     * Collect applied tax rates information on address level
     *
     * @param QuoteAddress $address
     * @param array $applied
     * @param float $amount
     * @param float $baseAmount
     * @param float $rate
     * @return void
     */
    protected function _saveAppliedTaxes(
        QuoteAddress $address,
        $applied,
        $amount,
        $baseAmount,
        $rate
    ) {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if ($row['percent'] == 0) {
                continue;
            }
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount / $rate * $row['percent'];
                $baseAppliedAmount = $baseAmount / $rate * $row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }

            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }

    /**
     * Determine whether to include shipping in tax calculation
     *
     * @return bool
     */
    protected function includeShipping()
    {
        return false;
    }

    /**
     * Determine whether to include item in tax calculation
     *
     * @return bool
     */
    protected function includeItems()
    {
        return false;
    }

    /**
     * Determine whether to include item in tax calculation
     *
     * @return bool
     */
    protected function includeExtraTax()
    {
        return false;
    }

    /**
     * Determine whether to save applied tax in address
     *
     * @return bool
     */
    protected function saveAppliedTaxes()
    {
        return false;
    }

    /**
     * Increment and return counter. This function is intended to be used to generate temporary
     * id for an item.
     *
     * @return int
     */
    protected function getNextIncrement()
    {
        return ++$this->counter;
    }
}
