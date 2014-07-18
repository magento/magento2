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
use Magento\Tax\Service\V1\Data\TaxDetails;

/**
 * Tax totals calculation model
 */
class Tax extends AbstractTotal
{
    /**#@+
     * Constants defined for type of items
     */
    const SHIPPING_ITEM_TYPE = 'shipping';
    const PRODUCT_ITEM_TYPE = 'product';
    /**#@-*/

    /**
     * Constant for shipping item code
     */
    const SHIPPING_ITEM_CODE = 'shipping';

    /**
     * Static counter
     *
     * @var int
     */
    protected static $counter = 0;

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
     * @var Store
     */
    protected $_store;

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
     * Hidden taxes array
     *
     * @var array
     */
    protected $_hiddenTaxes = array();

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Service\V1\TaxCalculationService $taxCalculationService
     * @param \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder $quoteDetailsBuilder
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Service\V1\TaxCalculationService $taxCalculationService,
        \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder $quoteDetailsBuilder
    ) {
        $this->setCode('tax');
        $this->_taxData = $taxData;
        $this->taxCalculationService = $taxCalculationService;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->_config = $taxConfig;
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
        $items = $this->_getAddressItems($address);
        if (!$items) {
            return $this;
        }
        //Preparation for calling taxCalculationService with base currency
        $quoteDetails = $this->prepareQuoteDetails($address, true);

        $baseTaxDetailsBase = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        //Preparation for calling taxCalculationService with display currency
        $quoteDetails = $this->prepareQuoteDetails($address, false);

        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());


        //Populate address and items with tax calculation results
        $this->updateTaxInfo($address, $taxDetails, $baseTaxDetailsBase);

        if ($this->processExtraSubtotalAmount()) {
            $address->addTotalAmount('tax', $address->getExtraTaxAmount());
            $address->addBaseTotalAmount('tax', $address->getBaseExtraTaxAmount());
            $address->addTotalAmount('subtotal', $address->getExtraSubtotalAmount());
            $address->addBaseTotalAmount('subtotal', $address->getBaseExtraSubtotalAmount());
            $address->setSubtotalInclTax(
                $address->getSubtotalInclTax() + $address->getExtraSubtotalAmount() + $address->getExtraTaxAmount()
            );
            $address->setBaseSubtotalInclTax(
                $address->getBaseSubtotalInclTax() +
                $address->getBaseExtraSubtotalAmount() +
                $address->getBaseExtraTaxAmount()
            );
        }
        return $this;
    }

    /**
     * Populate QuoteDetails object from Address object
     *
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Service\V1\Data\QuoteDetails
     */
    protected function prepareQuoteDetails(Address $address, $useBaseCurrency)
    {
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this->quoteDetailsBuilder->create();
        }

        $addressBuilder = $this->quoteDetailsBuilder->getAddressBuilder();

        //Set billing address
        $this->quoteDetailsBuilder->setBillingAddress(
            $this->mapAddress($addressBuilder, $address->getQuote()->getBillingAddress())
        );
        //Set shipping address
        $this->quoteDetailsBuilder->setShippingAddress(
            $this->mapAddress($addressBuilder, $address)
        );
        //Set customer tax class
        $this->quoteDetailsBuilder->setCustomerTaxClassId($address->getQuote()->getCustomerTaxClassId());
        //Populate with items
        $priceIncludesTax = $this->_config->priceIncludesTax($this->_store);
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
                }
            } else {
                $itemDataObject = $this->mapItem($itemBuilder, $item, $priceIncludesTax, $useBaseCurrency);
                $itemDataObjects[] = $itemDataObject;
            }
        }

        if ($this->includeShipping()) {
            //Add shipping as an item
            if (!$address->getShippingTaxCalculationAmount() || $address->getShippingTaxCalculationAmount() <= 0) {
                //Save the original shipping amount because shipping amount will be overridden
                //with shipping amount excluding tax
                $address->setShippingTaxCalculationAmount($address->getShippingAmount());
                $address->setBaseShippingTaxCalculationAmount($address->getBaseShippingAmount());
            }
            if ($address->getShippingTaxCalculationAmount() > 0) {
                $itemBuilder->setType(self::SHIPPING_ITEM_TYPE);
                $itemBuilder->setCode(self::SHIPPING_ITEM_CODE);
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
                $itemBuilder->setTaxClassId($this->_config->getShippingTaxClass($this->_store));
                $itemBuilder->setTaxIncluded($this->_config->shippingPriceIncludesTax($this->_store));
                $itemDataObjects[] = $itemBuilder->create();
            }
        }
        $this->quoteDetailsBuilder->setItems($itemDataObjects);

        $quoteDetails = $this->quoteDetailsBuilder->create();
        return $quoteDetails;
    }

    /**
     * Map Address to Address data object
     *
     * @param AddressBuilder $addressBuilder
     * @param Address $address
     * @return \Magento\Customer\Service\V1\Data\Address
     */
    protected function mapAddress(AddressBuilder $addressBuilder, Address $address)
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
    protected function mapItem(
        ItemBuilder $itemBuilder,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        if (!$item->getSequence()) {
            $sequence = 'sequence-' . $this->getNextIncrement();
            $item->setSequence($sequence);
        }
        $itemBuilder->setCode($item->getSequence());
        $itemBuilder->setQuantity($item->getQty());
        $itemBuilder->setTaxClassId($item->getProduct()->getTaxClassId());

        $itemBuilder->setTaxIncluded($priceIncludesTax);
        $itemBuilder->setType('product'); //TODO: find a place to define constants

        if ($item->getParentItem()) {
            $itemBuilder->setParentCode($item->getParentItem()->getId());
        }

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
     * Increment and return static counter
     *
     * @return int
     */
    protected function getNextIncrement()
    {
        return ++self::$counter;
    }

    /**
     * Update item tax and prices from item tax details object from tax calculation service
     *
     * @param Address $address
     * @param TaxDetails $taxDetails
     * @param TaxDetails $baseTaxDetails
     * @return $this
     */
    protected function updateTaxInfo(Address $address, TaxDetails $taxDetails, TaxDetails $baseTaxDetails)
    {
        $address->setAppliedTaxes([]);
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

        $appliedTaxesByItem = [];

        /** @var AbstractItem[] $keyedAddressItems */
        $keyedAddressItems = [];
        foreach ($this->_getAddressItems($address) as $addressItem) {
            $keyedAddressItems[$addressItem->getSequence()] = $addressItem;
        }

        $subtotal = $baseSubtotal = 0;
        $hiddenTax = $baseHiddenTax = 0;
        $tax = $baseTax = 0;
        $subtotalInclTax = $baseSubtotalInclTax = 0;

        foreach ($keyedItems as $code => $itemTaxDetails) {
            $baseItemTaxDetails = $baseKeyedItems[$code];
            $type = $itemTaxDetails->getType();
            if ($type == self::PRODUCT_ITEM_TYPE) {
                $quoteItem = $keyedAddressItems[$code];
                $this->updateItemTaxInfo($quoteItem, $itemTaxDetails, $baseItemTaxDetails);

                if ($quoteItem->getHasChildren() && $quoteItem->isChildrenCalculated()) {
                    //avoid double counting
                    continue;
                }
                $subtotal += $itemTaxDetails->getRowTotal();
                $baseSubtotal += $baseItemTaxDetails->getRowTotal();
                $hiddenTax += $itemTaxDetails->getDiscountTaxCompensationAmount();
                $baseHiddenTax += $baseItemTaxDetails->getDiscountTaxCompensationAmount();
                $tax += $itemTaxDetails->getRowTax();
                $baseTax += $baseItemTaxDetails->getRowTax();
                $subtotalInclTax += $itemTaxDetails->getRowTotalInclTax();
                $baseSubtotalInclTax += $baseItemTaxDetails->getRowTotalInclTax();

                $appliedTaxes = $itemTaxDetails->getAppliedTaxes();
                $baseAppliedTaxes = $baseItemTaxDetails->getAppliedTaxes();
                $appliedTaxesArray = $this->convertAppliedTaxes($appliedTaxes, $baseAppliedTaxes);

                foreach ($appliedTaxesArray as $appliedTaxArray) {
                    $this->_saveAppliedTaxes(
                        $address,
                        [$appliedTaxArray],
                        $appliedTaxArray['amount'],
                        $appliedTaxArray['base_amount'],
                        $appliedTaxArray['percent']
                    );
                }

                $appliedTaxesByItem[$quoteItem->getId()] = $appliedTaxesArray;
                //Set applied tax for item
                $quoteItem->setAppliedTaxes($appliedTaxesArray);
            }
            $address->getQuote()->setTaxesForItems($appliedTaxesByItem);
        }

        // Set item subtotals
        $address->setTotalAmount('subtotal', $subtotal);
        $address->setBaseTotalAmount('subtotal', $baseSubtotal);

        $address->setSubtotalInclTax($subtotalInclTax);
        $address->setBaseSubtotalInclTax($baseSubtotalInclTax);
        $address->setTotalAmount('hidden_tax', $hiddenTax);
        $address->setBaseTotalAmount('hidden_tax', $baseHiddenTax);

        //Set shipping tax
        if (isset($keyedItems[self::SHIPPING_ITEM_CODE]) && isset($baseKeyedItems[self::SHIPPING_ITEM_CODE])) {
            $shippingItem = $keyedItems[self::SHIPPING_ITEM_CODE];
            $baseShippingItem = $baseKeyedItems[self::SHIPPING_ITEM_CODE];

            $this->updateShippingTaxInfo($address, $shippingItem, $baseShippingItem);

            $tax += $shippingItem->getRowTax();
            $baseTax += $baseShippingItem->getRowTax();
        }

        $address->setTotalAmount('tax', $tax);
        $address->setBaseTotalAmount('tax', $baseTax);

        return $this;
    }

    /**
     * Update tax related fields for quote item
     *
     * @param AbstractItem $quoteItem
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\Item $itemTaxDetails
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\Item $baseItemTaxDetails
     * @return $this
     */
    protected function updateItemTaxInfo($quoteItem, $itemTaxDetails, $baseItemTaxDetails)
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
        if ($this->_config->discountTax($this->_store)) {
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
    protected function updateShippingTaxInfo(Address $address, $shippingTaxDetails, $baseShippingTaxDetails)
    {
        $address->setTotalAmount('shipping', $shippingTaxDetails->getRowTotal());
        $address->setBaseTotalAmount('shipping', $baseShippingTaxDetails->getRowTotal());
        $address->setShippingTaxAmount($shippingTaxDetails->getRowTax());
        $address->setBaseShippingTaxAmount($baseShippingTaxDetails->getRowTax());
        $address->setTotalAmount('shipping_hidden_tax', $shippingTaxDetails->getDiscountTaxCompensationAmount());
        $address->setBaseTotalAmount('shipping_hidden_tax', $baseShippingTaxDetails->getDiscountTaxCompensationAmount());

        $address->setShippingInclTax($shippingTaxDetails->getRowTotalInclTax());
        $address->setBaseShippingInclTax($baseShippingTaxDetails->getRowTotalInclTax());

        if ($this->_config->discountTax($this->_store)) {
            $address->setShippingAmountForDiscount($shippingTaxDetails->getRowTotalInclTax());
            $address->setBaseShippingAmountForDiscount($baseShippingTaxDetails->getRowTotalInclTax());
        }

        //Add taxes applied to shipping to applied taxes
        $appliedTaxes = $shippingTaxDetails->getAppliedTaxes();
        $baseAppliedTaxes = $baseShippingTaxDetails->getAppliedTaxes();
        $appliedTaxesArray = $this->convertAppliedTaxes($appliedTaxes, $baseAppliedTaxes);
        $this->_saveAppliedTaxes(
            $address,
            $appliedTaxesArray,
            $shippingTaxDetails->getRowTax(),
            $baseShippingTaxDetails->getRowTax(),
            $shippingTaxDetails->getTaxPercent()
        );

        return $this;
    }

    /**
     * Convert appliedTax data object from tax calculation service to internal array format
     *
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $appliedTaxes
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $baseAppliedTaxes
     * @return array
     */
    protected function convertAppliedTaxes($appliedTaxes, $baseAppliedTaxes)
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

            $appliedTaxesArray[] = [
                'amount' => $appliedTax->getAmount(),
                'base_amount' => $baseAppliedTax->getAmount(),
                'percent' => $appliedTax->getPercent(),
                'id' => $appliedTax->getTaxRateKey(),
                'rates' => $rates,
            ];
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
                array(
                    'code' => $this->getCode(),
                    'title' => __('Tax'),
                    'full_info' => $applied ? $applied : array(),
                    'value' => $amount,
                    'area' => $area
                )
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
                array(
                    'code' => 'subtotal',
                    'title' => __('Subtotal'),
                    'value' => $subtotalInclTax,
                    'value_incl_tax' => $subtotalInclTax,
                    'value_excl_tax' => $address->getSubtotal()
                )
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

    /**
     * Determine whether to include shipping in tax calculation
     *
     * @return bool
     */
    protected function includeShipping()
    {
        return true;
    }

    /**
     * Return a flag to indicate whether to process extra subtotal field in the quote
     *
     * @return bool
     */
    protected function processExtraSubtotalAmount()
    {
        return true;
    }
}
