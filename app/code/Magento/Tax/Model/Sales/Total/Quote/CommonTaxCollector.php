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
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Store\Model\Store;
use Magento\Sales\Model\Quote\Address;
use Magento\Sales\Model\Quote\Address\Total\AbstractTotal;
use Magento\Tax\Model\Calculation;
use Magento\Sales\Model\Quote\Item\AbstractItem;
use Magento\Customer\Service\V1\Data\AddressBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetailsBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetails\Item as ItemDataObject;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use Magento\Tax\Service\V1\Data\TaxDetails;
use Magento\Tax\Service\V1\Data\QuoteDetails;
use Magento\Tax\Service\V1\Data\TaxDetails\Item as ItemTaxDetails;
use Magento\Framework\Object;

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
     * Tax calculation service, the collector will call the service which performs the actual calculation
     *
     * @var \Magento\Tax\Service\V1\TaxCalculationService
     */
    protected $taxCalculationService;

    /**
     * Builder to create QuoteDetails as input to tax calculation service
     *
     * @var \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder
     */
    protected $quoteDetailsBuilder;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Service\V1\TaxCalculationService $taxCalculationService
     * @param \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder $quoteDetailsBuilder
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Service\V1\TaxCalculationService $taxCalculationService,
        \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder $quoteDetailsBuilder
    ) {
        $this->taxCalculationService = $taxCalculationService;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->_config = $taxConfig;
    }

    /**
     * Map Address to Address data object
     *
     * @param AddressBuilder $addressBuilder
     * @param Address $address
     * @return \Magento\Customer\Service\V1\Data\Address
     */
    public function mapAddress(AddressBuilder $addressBuilder, Address $address)
    {
        $addressBuilder->setCountryId($address->getCountryId());
        $addressBuilder->setRegion(
            $addressBuilder->getRegionBuilder()
                ->setRegionId($address->getRegionId())
                ->create()
        );
        $addressBuilder->setPostcode($address->getPostcode());
        $addressBuilder->setCity($address->getCity());
        $addressBuilder->setStreet($address->getStreet());

        return $addressBuilder->create();
    }

    /**
     * Map an item to item data object
     *
     * @param ItemBuilder $itemBuilder
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return ItemDataObject
     */
    public function mapItem(
        ItemBuilder $itemBuilder,
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
        $itemBuilder->setTaxClassKey(
            $itemBuilder->getTaxClassKeyBuilder()
                ->setType(TaxClassKey::TYPE_ID)
                ->setValue($item->getProduct()->getTaxClassId())
                ->create()
        );

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
     * @param ItemBuilder $itemBuilder
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @return ItemDataObject[]
     */
    public function mapItemExtraTaxables(
        ItemBuilder $itemBuilder,
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
                $itemBuilder->getTaxClassKeyBuilder()
                    ->setType(TaxClassKey::TYPE_ID)
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
     * @param Address $address
     * @param bool $useBaseCurrency
     * @param bool $priceIncludesTax
     * @return ItemDataObject[]
     */
    public function mapItems(
        Address $address,
        $priceIncludesTax,
        $useBaseCurrency
    ) {
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return [];
        }

        //Populate with items
        $itemBuilder = $this->quoteDetailsBuilder->getItemBuilder();
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
     * @param QuoteDetailsBuilder $quoteDetailsBuilder
     * @param Address $address
     * @return QuoteDetailsBuilder
     */
    public function populateAddressData(QuoteDetailsBuilder $quoteDetailsBuilder, Address $address)
    {
        $addressBuilder = $this->quoteDetailsBuilder->getAddressBuilder();

        //Set billing address
        $this->quoteDetailsBuilder->setBillingAddress(
            $this->mapAddress($addressBuilder, $address->getQuote()->getBillingAddress())
        );
        //Set shipping address
        $this->quoteDetailsBuilder->setShippingAddress(
            $this->mapAddress($addressBuilder, $address)
        );

        return $quoteDetailsBuilder;
    }

    /**
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Service\V1\Data\QuoteDetails\Item
     */
    public function getShippingDataObject(Address $address, $useBaseCurrency)
    {
        if ($address->getShippingTaxCalculationAmount() === null) {
            //Save the original shipping amount because shipping amount will be overridden
            //with shipping amount excluding tax
            $address->setShippingTaxCalculationAmount($address->getShippingAmount());
            $address->setBaseShippingTaxCalculationAmount($address->getBaseShippingAmount());
        }
        if ($address->getShippingTaxCalculationAmount() !== null) {
            $itemBuilder = $this->quoteDetailsBuilder->getItemBuilder();
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
            $itemBuilder->setTaxClassKey(
                $itemBuilder->getTaxClassKeyBuilder()
                    ->setType(TaxClassKey::TYPE_ID)
                    ->setValue($this->_config->getShippingTaxClass($address->getQuote()->getStore()))
                    ->create()
            );
            $itemBuilder->setTaxIncluded($this->_config->shippingPriceIncludesTax($address->getQuote()->getStore()));
            return $itemBuilder->create();
        }

        return null;
    }

    /**
     * Populate QuoteDetails object from Address object
     *
     * @param Address $address
     * @param ItemDataObject[] $itemDataObjects
     * @return \Magento\Tax\Service\V1\Data\QuoteDetails
     */
    protected function prepareQuoteDetails(Address $address, $itemDataObjects)
    {
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this->quoteDetailsBuilder->create();
        }

        $this->populateAddressData($this->quoteDetailsBuilder, $address);

        //Set customer tax class
        $this->quoteDetailsBuilder->setCustomerTaxClassKey(
            $this->quoteDetailsBuilder->getTaxClassKeyBuilder()
                ->setType(TaxClassKey::TYPE_ID)
                ->setValue($address->getQuote()->getCustomerTaxClassId())
                ->create()
        );
        $this->quoteDetailsBuilder->setItems($itemDataObjects);
        $this->quoteDetailsBuilder->setCustomerId($address->getQuote()->getCustomerId());

        $quoteDetails = $this->quoteDetailsBuilder->create();
        return $quoteDetails;
    }

    /**
     * Organize tax details by type and by item code
     *
     * @param TaxDetails $taxDetails
     * @param TaxDetails $baseTaxDetails
     * @return array
     */
    protected function organizeItemTaxDetailsByType(TaxDetails $taxDetails, TaxDetails $baseTaxDetails)
    {
        /** @var \Magento\Tax\Service\V1\Data\TaxDetails\Item[] $keyedItems */
        $keyedItems = [];
        foreach ($taxDetails->getItems() as $item) {
            $keyedItems[$item->getCode()] = $item;
        }
        /** @var \Magento\Tax\Service\V1\Data\TaxDetails\Item[] $baseKeyedItems */
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
     * @param Address $address
     * @param array $itemTaxDetails
     * @return $this
     */
    protected function processProductItems(Address $address, array $itemTaxDetails)
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
            /** @var ItemTaxDetails $taxDetail */
            $taxDetail = $itemTaxDetail[self::KEY_ITEM];
            /** @var ItemTaxDetails $baseTaxDetail */
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
     * @param Address $address
     * @param array $itemsByType
     * @return $this
     */
    protected function processAppliedTaxes(Address $address, Array $itemsByType)
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
                /** @var ItemTaxDetails $taxDetails */
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
     * @param ItemTaxDetails $itemTaxDetails
     * @param ItemTaxDetails $baseItemTaxDetails
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
     * @param Address $address
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\Item $shippingTaxDetails
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\Item $baseShippingTaxDetails
     * @return $this
     */
    protected function processShippingTaxInfo(Address $address, $shippingTaxDetails, $baseShippingTaxDetails)
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
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $appliedTaxes
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $baseAppliedTaxes
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
     * @param Address $address
     * @param array $applied
     * @param float $amount
     * @param float $baseAmount
     * @param float $rate
     * @return void
     */
    protected function _saveAppliedTaxes(
        Address $address,
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
}
