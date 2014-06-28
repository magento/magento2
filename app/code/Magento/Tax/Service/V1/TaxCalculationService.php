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

namespace Magento\Tax\Service\V1;

use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Resource\Sales\Order\Tax;
use Magento\Tax\Service\V1\Data\QuoteDetails;
use Magento\Tax\Service\V1\Data\QuoteDetails\Item as QuoteDetailsItem;
use Magento\Tax\Service\V1\Data\TaxDetails;
use Magento\Tax\Service\V1\Data\TaxDetails\Item as TaxDetailsItem;
use Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder as TaxDetailsItemBuilder;
use Magento\Tax\Service\V1\Data\TaxDetailsBuilder;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Tax Calculation Service
 *
 */
class TaxCalculationService implements TaxCalculationServiceInterface
{
    /**
     * Tax calculation model
     *
     * @var Calculation
     */
    protected $calculator;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $config;

    /**
     * Tax Details builder
     *
     * @var TaxDetailsBuilder
     */
    protected $taxDetailsBuilder;

    /**
     * Rounding deltas for prices
     *
     * @var array
     * example:
     *  [
     *      'type' => [
     *          'rate' => 'rounding delta',
     *      ],
     *  ]
     */
    protected $roundingDeltas;

    /**
     * Tax details item builder
     *
     * @var TaxDetailsBuilderItem
     */
    protected $taxDetailsItemBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Item code to Item object array.
     *
     * @var QuoteDetailsItem[]
     */
    private $keyedItems;

    /**
     * parent item code to children item array.
     *
     * @var QuoteDetailsItem[][]
     */
    private $parentToChildren;

    /**
     * Constructor
     *
     * @param Calculation $calculation
     * @param \Magento\Tax\Model\Config $config
     * @param TaxDetailsBuilder $taxDetailsBuilder
     * @param TaxDetailsItemBuilder $taxDetailsItemBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Calculation $calculation,
        \Magento\Tax\Model\Config $config,
        TaxDetailsBuilder $taxDetailsBuilder,
        TaxDetailsItemBuilder $taxDetailsItemBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->calculator = $calculation;
        $this->config = $config;
        $this->taxDetailsBuilder = $taxDetailsBuilder;
        $this->taxDetailsItemBuilder = $taxDetailsItemBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateTax(QuoteDetails $quoteDetails, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getStoreId();
        }

        // initial TaxDetails data
        $taxDetailsData = [
            TaxDetails::KEY_SUBTOTAL => 0.0,
            TaxDetails::KEY_TAX_AMOUNT => 0.0,
            TaxDetails::KEY_DISCOUNT_AMOUNT => 0.0
        ];

        $items = $quoteDetails->getItems();
        if (empty($items)) {
            return $this->taxDetailsBuilder->populateWithArray($taxDetailsData)->create();
        }
        $this->computeRelationships($items);

        $addressRequest = $this->getAddressTaxRequest($quoteDetails, $storeId);
        if ($this->config->priceIncludesTax($storeId)) {
            $storeRequest = $this->getStoreTaxRequest($storeId);
            $classIds = [];
            foreach ($items as $item) {
                if ($item->getTaxClassId()) {
                    $classIds[] = $item->getTaxClassId();
                }
            }
            $classIds = array_unique($classIds);
            $addressRequest->setProductClassId($classIds);
            $storeRequest->setProductClassId($classIds);
            if ((bool)$this->config->crossBorderTradeEnabled($storeId)) {
                $addressRequest->setSameRateAsStore(true);
            } else {
                $addressRequest->setSameRateAsStore(
                    $this->calculator->compareRequests($storeRequest, $addressRequest)
                );
            }
        }

        // init rounding deltas for this quote
        $this->roundingDeltas = [];
        $processedItems = [];
        /** @var QuoteDetailsItem $item */
        foreach ($this->keyedItems as $item) {
            if (isset($this->parentToChildren[$item->getCode()])) {
                $processedChildren = [];
                foreach ($this->parentToChildren[$item->getCode()] as $child) {
                    $processedChildren[] = $this->processItem($child, $addressRequest, $storeId);
                }
                $processedItemBuilder = $this->calculateParent($processedChildren, $item->getQuantity());
                $processedItemBuilder->setCode($item->getCode());
                $processedItemBuilder->setType($item->getType());
                $processedItem = $processedItemBuilder->create();
            } else {
                $processedItem = $this->processItem($item, $addressRequest, $storeId);
            }
            $processedItems[] = $processedItem;
            $taxDetailsData = $this->addSubtotalAmount($taxDetailsData, $processedItem);
        }

        return $this->taxDetailsBuilder->populateWithArray($taxDetailsData)->setItems($processedItems)->create();
    }

    /**
     * Computes relationships between items, primarily the child to parent relationship.
     *
     * @param QuoteDetailsItem[] $items
     * @return void
     */
    private function computeRelationships($items)
    {
        $this->keyedItems = [];
        $this->parentToChildren = [];
        foreach ($items as $item) {
            if ($item->getParentCode() === null) {
                $this->keyedItems[$item->getCode()] = $item;
            } else {
                $this->parentToChildren[$item->getParentCode()][] = $item;
            }
        }
    }

    /**
     * Get request for fetching address tax rate
     *
     * @param QuoteDetails $quoteDetails
     * @param int $storeId
     * @return \Magento\Framework\Object
     */
    protected function getAddressTaxRequest(QuoteDetails $quoteDetails, $storeId)
    {
        return $this->calculator->getRateRequest(
            $quoteDetails->getShippingAddress(),
            $quoteDetails->getBillingAddress(),
            $quoteDetails->getCustomerTaxClassId(),
            $storeId
        );
    }

    /**
     * Get request for fetching store tax rate
     *
     * @param int $storeId
     * @return \Magento\Framework\Object
     */
    protected function getStoreTaxRequest($storeId)
    {
        return $this->calculator->getRateOriginRequest($storeId);
    }

    /**
     * Calculate item price and row total with customized rounding level
     *
     * @param QuoteDetailsItem $item
     * @param \Magento\Framework\Object $taxRequest
     * @param int $storeId
     * @return TaxDetailsItem|null
     */
    protected function processItem(
        QuoteDetailsItem $item,
        \Magento\Framework\Object $taxRequest,
        $storeId
    ) {
        switch ($this->config->getAlgorithm($storeId)) {
            case Calculation::CALC_UNIT_BASE:
                return $this->unitBaseCalculation($item, $taxRequest, $storeId);
            case Calculation::CALC_ROW_BASE:
                return $this->rowBaseCalculation($item, $taxRequest, $storeId);
            case Calculation::CALC_TOTAL_BASE:
                return $this->totalBaseCalculation($item, $taxRequest, $storeId);
            default:
                return null;
        }
    }

    /**
     * Calculate item price and row total including/excluding tax based on unit price rounding level
     *
     * @param QuoteDetailsItem $item
     * @param \Magento\Framework\Object $taxRequest
     * @param int $storeId
     * @return TaxDetailsItem
     */
    protected function unitBaseCalculation(
        QuoteDetailsItem $item,
        \Magento\Framework\Object $taxRequest,
        $storeId
    ) {
        $taxRequest->setProductClassId($item->getTaxClassId());
        $storeRate = $this->calculator->getStoreRate($taxRequest, $storeId);
        $rate = $this->calculator->getRate($taxRequest);
        $quantity = $this->getTotalQuantity($item);
        $price = $taxPrice = $this->calculator->round($item->getUnitPrice());
        $subtotal = $taxSubtotal = $this->calcRowTotal($item);
        if ($item->getTaxIncluded()) {
            if ($taxRequest->getSameRateAsStore() || ($rate == $storeRate)) {
                $taxable = $price;
                $tax = $this->calculator->calcTaxAmount($taxable, $rate, true);
                $price = $price - $tax;
                $subtotal = $price * $quantity;
            } else {
                $taxPrice = $this->calculatePriceInclTax($price, $storeRate, $rate);
                $taxable = $taxPrice;
                $tax = $this->calculator->calcTaxAmount($taxable, $rate, true, true);
                $price = $taxPrice - $tax;
                $taxSubtotal = $taxPrice * $quantity;
                $subtotal = $price * $quantity;
            }
        } else {
            $taxable = $price;
            $appliedRates = $this->calculator->getAppliedRates($taxRequest);
            $taxes = [];
            foreach ($appliedRates as $appliedRate) {
                $taxRate = $appliedRate['percent'];
                $taxes[] = $this->calculator->calcTaxAmount($taxable, $taxRate, false);
            }
            $tax = array_sum($taxes);
            $taxPrice = $price + $tax;
            $taxSubtotal = $taxPrice * $quantity;
        }

        $this->taxDetailsItemBuilder->setRowTax($tax * $quantity);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($taxPrice);
        $this->taxDetailsItemBuilder->setRowTotal($subtotal);
        $this->taxDetailsItemBuilder->setTaxableAmount($taxable);
        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);

        return $this->taxDetailsItemBuilder->create();
    }

    /**
     * Calculate item price and row total including/excluding tax based on row total price rounding level
     *
     * @param QuoteDetailsItem $item
     * @param \Magento\Framework\Object $taxRequest
     * @param int $storeId
     * @return TaxDetailsItem
     */
    protected function rowBaseCalculation(
        QuoteDetailsItem $item,
        \Magento\Framework\Object $taxRequest,
        $storeId
    ) {
        $taxRequest->setProductClassId($item->getTaxClassId());
        $storeRate = $this->calculator->getStoreRate($taxRequest, $storeId);
        $rate = $this->calculator->getRate($taxRequest);
        $quantity = $this->getTotalQuantity($item);
        $price = $this->calculator->round($item->getUnitPrice());
        $subtotal = $this->calcRowTotal($item);

        if ($item->getTaxIncluded()) {
            if ($taxRequest->getSameRateAsStore() || ($rate == $storeRate)) {
                $taxable = $subtotal;
                $rowTax = $this->calculator->calcTaxAmount($taxable, $rate, true, true);
                $taxPrice = $price;
                $taxSubtotal = $subtotal;
                $subtotal = $this->calculator->round($subtotal - $rowTax);
                $price = $this->calculator->round($subtotal / $quantity);
            } else {
                $taxPrice = $this->calculatePriceInclTax($price, $storeRate, $rate);
                $tax = $this->calculator->calcTaxAmount($taxPrice, $rate, true, true);
                $price = $taxPrice - $tax;
                $taxable = $taxPrice * $quantity;
                $taxSubtotal = $taxPrice * $quantity;
                $rowTax = $this->calculator->calcTaxAmount($taxable, $rate, true, true);
                $subtotal = $taxSubtotal - $rowTax;
            }
        } else {
            $taxable = $subtotal;
            $appliedRates = $this->calculator->getAppliedRates($taxRequest);
            $rowTaxes = [];
            foreach ($appliedRates as $appliedRate) {
                $taxRate = $appliedRate['percent'];
                $rowTaxes[] = $this->calculator->calcTaxAmount($taxable, $taxRate, false, true);
            }
            $rowTax = array_sum($rowTaxes);
            $taxSubtotal = $subtotal + $rowTax;
            $taxPrice = $this->calculator->round($taxSubtotal / $quantity);
        }

        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($taxPrice);
        $this->taxDetailsItemBuilder->setRowTotal($subtotal);
        $this->taxDetailsItemBuilder->setTaxableAmount($taxable);
        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);
        $this->taxDetailsItemBuilder->setRowTax($rowTax);

        return $this->taxDetailsItemBuilder->create();
    }

    /**
     * Calculate item price and row total including/excluding tax based on total price rounding level
     *
     * @param QuoteDetailsItem $item
     * @param \Magento\Framework\Object $taxRequest
     * @param int $storeId
     * @return TaxDetailsItem
     */
    protected function totalBaseCalculation(
        QuoteDetailsItem $item,
        \Magento\Framework\Object $taxRequest,
        $storeId
    ) {
        $taxRequest->setProductClassId($item->getTaxClassId());
        $storeRate = $this->calculator->getStoreRate($taxRequest, $storeId);
        $rate = $this->calculator->getRate($taxRequest);
        $quantity = $this->getTotalQuantity($item);
        $price = $this->calculator->round($item->getUnitPrice());
        $subtotal = $taxSubtotal = $this->calcRowTotal($item);

        if ($item->getTaxIncluded()) {
            if ($taxRequest->getSameRateAsStore() || ($rate == $storeRate)) {
                $taxable = $subtotal;
                $rowTaxExact = $this->calculator->calcTaxAmount($taxable, $rate, true, false);
                $rowTax = $this->deltaRound($rowTaxExact, $rate, true);
                $subtotal = $subtotal - $rowTax;
                $taxPrice = $price;
                $price = $this->calculator->round($subtotal / $quantity);

            } else {
                $taxPrice = $this->calculatePriceInclTax($price, $storeRate, $rate);
                $tax = $this->calculator->calcTaxAmount($taxPrice, $rate, true, true);
                $price = $taxPrice - $tax;
                $taxSubtotal = $taxable = $taxPrice * $quantity;
                $rowTax =
                    $this->deltaRound($this->calculator->calcTaxAmount($taxable, $rate, true, false), $rate, true);
                $subtotal = $taxSubtotal - $rowTax;
            }
        } else {
            $taxable = $subtotal;
            $appliedRates = $this->calculator->getAppliedRates($taxRequest);
            $rowTaxes = [];
            foreach ($appliedRates as $appliedRate) {
                $taxId = $appliedRate['id'];
                $taxRate = $appliedRate['percent'];
                $rowTaxes[] = $this->deltaRound(
                    $this->calculator->calcTaxAmount($taxable, $taxRate, false, false),
                    $taxId,
                    false
                );
            }
            $rowTax = array_sum($rowTaxes);
            $taxSubtotal = $subtotal + $rowTax;
            $taxPrice = $this->calculator->round($taxSubtotal / $quantity);
        }

        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($taxPrice);
        $this->taxDetailsItemBuilder->setRowTotal($subtotal);
        $this->taxDetailsItemBuilder->setTaxableAmount($taxable);
        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);

        return $this->taxDetailsItemBuilder->create();
    }

    /**
     * Round price based on previous rounding operation delta
     *
     * @param float $price
     * @param string $rate
     * @param bool $direction
     * @param string $type
     * @return float
     */
    protected function deltaRound($price, $rate, $direction, $type = 'regular')
    {
        if ($price) {
            $rate = (string)$rate;
            $type = $type . $direction;
            // initialize the delta to a small number to avoid non-deterministic behavior with rounding of 0.5
            $delta = isset($this->roundingDeltas[$type][$rate]) ?
                $this->roundingDeltas[$type][$rate] :
                0.000001;
            $price += $delta;
            $roundPrice = $this->calculator->round($price);
            $this->roundingDeltas[$type][$rate] = $price - $roundPrice;
            $price = $roundPrice;
        }
        return $price;
    }

    /**
     * Calculate row information for item based on children calculation
     *
     * @param TaxDetailsItem[] $children
     * @param int $quantity
     * @return TaxDetailsItemBuilder
     */
    protected function calculateParent($children, $quantity)
    {
        $rowTotal = 0.00;
        $rowTax = 0.00;
        $taxableAmount = 0.00;

        foreach ($children as $child) {
            $rowTotal += $child->getRowTotal();
            $rowTax += $child->getRowTax();
            $taxableAmount += $child->getTaxableAmount();
        }

        $rowTotalInclTax = $rowTotal + $rowTax;
        $price = $this->calculator->round($rowTotal / $quantity);
        $priceInclTax = $this->calculator->round($rowTotalInclTax / $quantity);

        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($rowTotal);
        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setTaxableAmount($taxableAmount);

        return $this->taxDetailsItemBuilder;
    }

    /**
     * Given a store price that includes tax at the store rate, this function will back out the store's tax, and add in
     * the customer's tax.  Returns this new price which is the customer's price including tax.
     *
     * @param float $storePriceInclTax
     * @param float $storeRate
     * @param float $customerRate
     * @return float
     */
    protected function calculatePriceInclTax($storePriceInclTax, $storeRate, $customerRate)
    {
        $storeTax = $this->calculator->calcTaxAmount($storePriceInclTax, $storeRate, true, false);
        $priceExclTax = $storePriceInclTax - $storeTax;
        $customerTax = $this->calculator->calcTaxAmount($priceExclTax, $customerRate, false, false);
        $customerPriceInclTax = $this->calculator->round($priceExclTax + $customerTax);
        return $customerPriceInclTax;
    }

    /**
     * Add row total item amount to subtotal
     *
     * @param array $taxDetailsData
     * @param TaxDetailsItem $item
     * @return array
     */
    protected function addSubtotalAmount($taxDetailsData, TaxDetailsItem $item)
    {
        $taxDetailsData[TaxDetails::KEY_SUBTOTAL]
            = $taxDetailsData[TaxDetails::KEY_SUBTOTAL] + $item->getRowTotal();

        $taxDetailsData[TaxDetails::KEY_TAX_AMOUNT]
            = $taxDetailsData[TaxDetails::KEY_TAX_AMOUNT] + $item->getRowTax();

        $taxDetailsData[TaxDetails::KEY_DISCOUNT_AMOUNT]
            = $taxDetailsData[TaxDetails::KEY_DISCOUNT_AMOUNT] + $item->getDiscountAmount();

        return $taxDetailsData;
    }

    /**
     * Calculates the total quantity for this item.
     *
     * What this really means is that if this is a child item, it return the parent quantity times
     * the child quantity and return that as the child's quantity.
     *
     * @param QuoteDetailsItem $item
     * @return float
     */
    protected function getTotalQuantity(QuoteDetailsItem $item)
    {
        if ($item->getParentCode()) {
            $parentQuantity = $this->keyedItems[$item->getParentCode()]->getQuantity();
            return $parentQuantity * $item->getQuantity();
        }
        return $item->getQuantity();
    }

    /**
     * Calculate the row total for an item
     *
     * @param QuoteDetailsItem $item
     * @return float
     */
    protected function calcRowTotal(QuoteDetailsItem $item)
    {
        $qty = $this->getTotalQuantity($item);

        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        $total = $this->calculator->round($item->getUnitPrice()) * $qty;

        return $this->calculator->round($total);
    }
}
