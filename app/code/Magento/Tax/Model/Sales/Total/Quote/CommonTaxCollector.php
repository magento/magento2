<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

/**
 * Tax totals calculation model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * Factory to create QuoteDetails as input to tax calculation service
     *
     * @var \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory
     */
    protected $quoteDetailsDataObjectFactory;

    /**
     * @var CustomerAddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var CustomerAddressRegionFactory
     */
    protected $customerAddressRegionFactory;

    /**
     * @var \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory
     */
    protected $taxClassKeyDataObjectFactory;

    /**
     * @var \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory
     */
    protected $quoteDetailsItemDataObjectFactory;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param CustomerAddressRegionFactory $customerAddressRegionFactory
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory
    ) {
        $this->taxCalculationService = $taxCalculationService;
        $this->quoteDetailsDataObjectFactory = $quoteDetailsDataObjectFactory;
        $this->_config = $taxConfig;
        $this->taxClassKeyDataObjectFactory = $taxClassKeyDataObjectFactory;
        $this->quoteDetailsItemDataObjectFactory = $quoteDetailsItemDataObjectFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->customerAddressRegionFactory = $customerAddressRegionFactory;
    }

    /**
     * Map quote address to customer address
     *
     * @param QuoteAddress $address
     * @return CustomerAddress
     */
    public function mapAddress(QuoteAddress $address)
    {
        $customerAddress = $this->customerAddressFactory->create();
        $customerAddress->setCountryId($address->getCountryId());
        $customerAddress->setRegion(
            $this->customerAddressRegionFactory->create()->setRegionId($address->getRegionId())
        );
        $customerAddress->setPostcode($address->getPostcode());
        $customerAddress->setCity($address->getCity());
        $customerAddress->setStreet($address->getStreet());

        return $customerAddress;
    }

    /**
     * Map an item to item data object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     */
    public function mapItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        if (!$item->getTaxCalculationItemId()) {
            $sequence = 'sequence-' . $this->getNextIncrement();
            $item->setTaxCalculationItemId($sequence);
        }
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject */
        $itemDataObject = $itemDataObjectFactory->create();
        $itemDataObject->setCode($item->getTaxCalculationItemId())
            ->setQuantity($item->getQty())
            ->setTaxClassKey(
                $this->taxClassKeyDataObjectFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($item->getProduct()->getTaxClassId())
            )
            ->setIsTaxIncluded($priceIncludesTax)
            ->setType(self::ITEM_TYPE_PRODUCT);

        if ($useBaseCurrency) {
            if (!$item->getBaseTaxCalculationPrice()) {
                $item->setBaseTaxCalculationPrice($item->getBaseCalculationPriceOriginal());
            }
            $itemDataObject->setUnitPrice($item->getBaseTaxCalculationPrice())
                ->setDiscountAmount($item->getBaseDiscountAmount());
        } else {
            if (!$item->getTaxCalculationPrice()) {
                $item->setTaxCalculationPrice($item->getCalculationPriceOriginal());
            }
            $itemDataObject->setUnitPrice($item->getTaxCalculationPrice())
                ->setDiscountAmount($item->getDiscountAmount());
        }

        $itemDataObject->setParentCode($parentCode);

        return $itemDataObject;
    }

    /**
     * Map item extra taxables
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapItemExtraTaxables(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
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

            if ($useBaseCurrency) {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE];
            } else {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE];
            }
            /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject */
            $itemDataObject = $itemDataObjectFactory->create();
            $itemDataObject->setCode($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_CODE])
                ->setType($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TYPE])
                ->setQuantity($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_QUANTITY])
                ->setTaxClassKey(
                    $this->taxClassKeyDataObjectFactory->create()
                        ->setType(TaxClassKeyInterface::TYPE_ID)
                        ->setValue($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID])
                )
                ->setUnitPrice($unitPrice)
                ->setIsTaxIncluded($extraTaxableIncludesTax)
                ->setAssociatedItemCode($item->getTaxCalculationItemId());
            $itemDataObjects[] = $itemDataObject;
        }

        return $itemDataObjects;
    }

    /**
     * Add quote items
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param bool $useBaseCurrency
     * @param bool $priceIncludesTax
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapItems(
        ShippingAssignmentInterface $shippingAssignment,
        $priceIncludesTax,
        $useBaseCurrency
    ) {
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return [];
        }

        //Populate with items
        $itemDataObjectFactory = $this->quoteDetailsItemDataObjectFactory;
        $itemDataObjects = [];
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentItemDataObject = $this->mapItem($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency);
                $itemDataObjects[] = $parentItemDataObject;
                foreach ($item->getChildren() as $child) {
                    $childItemDataObject = $this->mapItem(
                        $itemDataObjectFactory,
                        $child,
                        $priceIncludesTax,
                        $useBaseCurrency,
                        $parentItemDataObject->getCode()
                    );
                    $itemDataObjects[] = $childItemDataObject;
                    $extraTaxableItems = $this->mapItemExtraTaxables(
                        $itemDataObjectFactory,
                        $item,
                        $priceIncludesTax,
                        $useBaseCurrency
                    );
                    $itemDataObjects = array_merge($itemDataObjects, $extraTaxableItems);
                }
            } else {
                $itemDataObject = $this->mapItem($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency);
                $itemDataObjects[] = $itemDataObject;
                $extraTaxableItems = $this->mapItemExtraTaxables(
                    $itemDataObjectFactory,
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
     * Populate the quote details with address information
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @param QuoteAddress $address
     * @return QuoteDetailsInterface
     */
    public function populateAddressData(QuoteDetailsInterface $quoteDetails, QuoteAddress $address)
    {
        $quoteDetails->setBillingAddress($this->mapAddress($address->getQuote()->getBillingAddress()));
        $quoteDetails->setShippingAddress($this->mapAddress($address));
        return $quoteDetails;
    }

    /**
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param QuoteAddress\Total $total
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     */
    public function getShippingDataObject(
        ShippingAssignmentInterface $shippingAssignment,
        QuoteAddress\Total $total,
        $useBaseCurrency
    ) {
        $store = $shippingAssignment->getShipping()->getAddress()->getQuote()->getStore();
        if ($total->getShippingTaxCalculationAmount() === null) {
            //Save the original shipping amount because shipping amount will be overridden
            //with shipping amount excluding tax
            $total->setShippingTaxCalculationAmount($total->getShippingAmount());
            $total->setBaseShippingTaxCalculationAmount($total->getBaseShippingAmount());
        }
        if ($total->getShippingTaxCalculationAmount() !== null) {
            /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject */
            $itemDataObject = $this->quoteDetailsItemDataObjectFactory->create()
                ->setType(self::ITEM_TYPE_SHIPPING)
                ->setCode(self::ITEM_CODE_SHIPPING)
                ->setQuantity(1);
            if ($useBaseCurrency) {
                $itemDataObject->setUnitPrice($total->getBaseShippingTaxCalculationAmount());
            } else {
                $itemDataObject->setUnitPrice($total->getShippingTaxCalculationAmount());
            }
            if ($total->getShippingDiscountAmount()) {
                if ($useBaseCurrency) {
                    $itemDataObject->setDiscountAmount($total->getBaseShippingDiscountAmount());
                } else {
                    $itemDataObject->setDiscountAmount($total->getShippingDiscountAmount());
                }
            }
            $itemDataObject->setTaxClassKey(
                $this->taxClassKeyDataObjectFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($this->_config->getShippingTaxClass($store))
            );
            $itemDataObject->setIsTaxIncluded(
                $this->_config->shippingPriceIncludesTax($store)
            );
            return $itemDataObject;
        }

        return null;
    }

    /**
     * Populate QuoteDetails object from quote address object
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $itemDataObjects
     * @return \Magento\Tax\Api\Data\QuoteDetailsInterface
     */
    protected function prepareQuoteDetails(ShippingAssignmentInterface $shippingAssignment, $itemDataObjects)
    {
        $items = $shippingAssignment->getItems();
        $address = $shippingAssignment->getShipping()->getAddress();
        if (!count($items)) {
            return $this->quoteDetailsDataObjectFactory->create();
        }

        $quoteDetails = $this->quoteDetailsDataObjectFactory->create();
        $this->populateAddressData($quoteDetails, $address);

        //Set customer tax class
        $quoteDetails->setCustomerTaxClassKey(
            $this->taxClassKeyDataObjectFactory->create()
                ->setType(TaxClassKeyInterface::TYPE_ID)
                ->setValue($address->getQuote()->getCustomerTaxClassId())
        );
        $quoteDetails->setItems($itemDataObjects);
        $quoteDetails->setCustomerId($address->getQuote()->getCustomerId());

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
     * subtotal, subtotalInclTax, tax, discount_tax_compensation,
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param array $itemTaxDetails
     * @param QuoteAddress\Total $total
     * @return $this
     */
    protected function processProductItems(
        ShippingAssignmentInterface $shippingAssignment,
        array $itemTaxDetails,
        QuoteAddress\Total $total
    ) {
        $store = $shippingAssignment->getShipping()->getAddress()->getQuote()->getStore();

        /** @var AbstractItem[] $keyedAddressItems */
        $keyedAddressItems = [];
        foreach ($shippingAssignment->getItems() as $addressItem) {
            $keyedAddressItems[$addressItem->getTaxCalculationItemId()] = $addressItem;
        }

        $subtotal = $baseSubtotal = 0;
        $discountTaxCompensation = $baseDiscountTaxCompensation = 0;
        $tax = $baseTax = 0;
        $subtotalInclTax = $baseSubtotalInclTax = 0;

        foreach ($itemTaxDetails as $code => $itemTaxDetail) {
            /** @var TaxDetailsItemInterface $taxDetail */
            $taxDetail = $itemTaxDetail[self::KEY_ITEM];
            /** @var TaxDetailsItemInterface $baseTaxDetail */
            $baseTaxDetail = $itemTaxDetail[self::KEY_BASE_ITEM];
            $quoteItem = $keyedAddressItems[$code];
            $this->updateItemTaxInfo($quoteItem, $taxDetail, $baseTaxDetail, $store);

            //Update aggregated values
            if ($quoteItem->getHasChildren() && $quoteItem->isChildrenCalculated()) {
                //avoid double counting
                continue;
            }
            $subtotal += $taxDetail->getRowTotal();
            $baseSubtotal += $baseTaxDetail->getRowTotal();
            $discountTaxCompensation += $taxDetail->getDiscountTaxCompensationAmount();
            $baseDiscountTaxCompensation += $baseTaxDetail->getDiscountTaxCompensationAmount();
            $tax += $taxDetail->getRowTax();
            $baseTax += $baseTaxDetail->getRowTax();
            $subtotalInclTax += $taxDetail->getRowTotalInclTax();
            $baseSubtotalInclTax += $baseTaxDetail->getRowTotalInclTax();
        }

        //Set aggregated values
        $total->setTotalAmount('subtotal', $subtotal);
        $total->setBaseTotalAmount('subtotal', $baseSubtotal);
        $total->setTotalAmount('tax', $tax);
        $total->setBaseTotalAmount('tax', $baseTax);
        $total->setTotalAmount('discount_tax_compensation', $discountTaxCompensation);
        $total->setBaseTotalAmount('discount_tax_compensation', $baseDiscountTaxCompensation);

        $total->setSubtotalInclTax($subtotalInclTax);
        $total->setBaseSubtotalTotalInclTax($baseSubtotalInclTax);
        $total->setBaseSubtotalInclTax($baseSubtotalInclTax);
        $shippingAssignment->getShipping()->getAddress()->setBaseSubtotalTotalInclTax($baseSubtotalInclTax);;

        return $this;
    }

    /**
     * Process applied taxes for items and quote
     *
     * @param QuoteAddress\Total $total
     * @param array $itemsByType
     * @return $this
     */
    protected function processAppliedTaxes(
        QuoteAddress\Total $total,
        ShippingAssignmentInterface $shippingAssignment,
        Array $itemsByType
    ) {
        $total->setAppliedTaxes([]);
        $allAppliedTaxesArray = [];

        /** @var AbstractItem[] $keyedAddressItems */
        $keyedAddressItems = [];
        foreach ($shippingAssignment->getItems() as $addressItem) {
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
                        $total,
                        [$appliedTaxArray],
                        $appliedTaxArray['amount'],
                        $appliedTaxArray['base_amount'],
                        $appliedTaxArray['percent']
                    );
                }
            }
        }

        $total->setItemsAppliedTaxes($allAppliedTaxesArray);

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
        $quoteItem->setDiscountTaxCompensationAmount($itemTaxDetails->getDiscountTaxCompensationAmount());

        $quoteItem->setBasePrice($baseItemTaxDetails->getPrice());
        $quoteItem->setBasePriceInclTax($baseItemTaxDetails->getPriceInclTax());
        $quoteItem->setBaseRowTotal($baseItemTaxDetails->getRowTotal());
        $quoteItem->setBaseRowTotalInclTax($baseItemTaxDetails->getRowTotalInclTax());
        $quoteItem->setBaseTaxAmount($baseItemTaxDetails->getRowTax());
        $quoteItem->setTaxPercent($baseItemTaxDetails->getTaxPercent());
        $quoteItem->setBaseDiscountTaxCompensationAmount($baseItemTaxDetails->getDiscountTaxCompensationAmount());

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
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param QuoteAddress\Total $total
     * @param TaxDetailsItemInterface $shippingTaxDetails
     * @param TaxDetailsItemInterface $baseShippingTaxDetails
     * @return $this
     */
    protected function processShippingTaxInfo(
        ShippingAssignmentInterface $shippingAssignment,
        QuoteAddress\Total $total,
        $shippingTaxDetails,
        $baseShippingTaxDetails
    ) {
        $total->setTotalAmount('shipping', $shippingTaxDetails->getRowTotal());
        $total->setBaseTotalAmount('shipping', $baseShippingTaxDetails->getRowTotal());
        $total->setTotalAmount('shipping_discount_tax_compensation', $shippingTaxDetails->getDiscountTaxCompensationAmount());
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', $baseShippingTaxDetails->getDiscountTaxCompensationAmount());

        $total->setShippingInclTax($shippingTaxDetails->getRowTotalInclTax());
        $total->setBaseShippingInclTax($baseShippingTaxDetails->getRowTotalInclTax());
        $total->setShippingTaxAmount($shippingTaxDetails->getRowTax());
        $total->setBaseShippingTaxAmount($baseShippingTaxDetails->getRowTax());

        //Add the shipping tax to total tax amount
        $total->addTotalAmount('tax', $shippingTaxDetails->getRowTax());
        $total->addBaseTotalAmount('tax', $baseShippingTaxDetails->getRowTax());

        if ($this->_config->discountTax($shippingAssignment->getShipping()->getAddress()->getQuote()->getStore())) {
            $total->setShippingAmountForDiscount($shippingTaxDetails->getRowTotalInclTax());
            $total->setBaseShippingAmountForDiscount($baseShippingTaxDetails->getRowTotalInclTax());
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
     * @param QuoteAddress\Total $total
     * @param array $applied
     * @param float $amount
     * @param float $baseAmount
     * @param float $rate
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveAppliedTaxes(
        QuoteAddress\Total $total,
        $applied,
        $amount,
        $baseAmount,
        $rate
    ) {
        $previouslyAppliedTaxes = $total->getAppliedTaxes();
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
        $total->setAppliedTaxes($previouslyAppliedTaxes);
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
