<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Weee\Block\Item\Price;

use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;

/**
 * Item price render block
 *
 * @api
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @since 100.0.2
 */
class Renderer extends \Magento\Tax\Block\Item\Price\Renderer
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Weee\Helper\Data $weeeHelper,
        array $data = []
    ) {
        $this->weeeHelper = $weeeHelper;
        $data['weeeHelper'] = $this->weeeHelper;
        parent::__construct($context, $taxHelper, $priceCurrency, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Whether to display weee details together with price
     *
     * @return bool
     */
    public function displayPriceWithWeeeDetails()
    {
        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return false;
        }

        $displayWeeeDetails = $this->weeeHelper->typeOfDisplay(
            [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL],
            $this->getZone(),
            $this->getStoreId()
        );
        if (!$displayWeeeDetails) {
            return false;
        }
        if ($this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem()) <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Return the flag whether to include weee in the price
     *
     * @return bool|int
     */
    public function getIncludeWeeeFlag()
    {
        $includeWeee = $this->weeeHelper->typeOfDisplay(
            [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL],
            $this->getZone(),
            $this->getStoreId()
        );
        return $includeWeee;
    }

    /**
     * Get display price for unit price including tax.
     *
     * The Weee amount will be added to unit price including tax depending on Weee display setting.
     *
     * @return float
     */
    public function getUnitDisplayPriceInclTax()
    {
        $priceInclTax = $this->getItem()->getPriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            $qty = $this->getItemQtyForUnitPriceCalculation();
            return $qty > 0 ? $this->getRowDisplayPriceInclTax() / $qty
                : $priceInclTax + $this->weeeHelper->getWeeeTaxInclTax($this->getItem());
        }

        return $priceInclTax;
    }

    /**
     * Get base price for unit price including tax.
     *
     * The Weee amount will be added to unit price including tax depending on Weee display setting.
     *
     * @return float
     */
    public function getBaseUnitDisplayPriceInclTax()
    {
        $basePriceInclTax = $this->getItem()->getBasePriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            $qty = $this->getItemQtyForUnitPriceCalculation();
            return $qty > 0 ? $this->getBaseRowDisplayPriceInclTax() / $qty
                : $basePriceInclTax + $this->weeeHelper->getBaseWeeeTaxInclTax($this->getItem());
        }

        return $basePriceInclTax;
    }

    /**
     * Get display price for row total including tax.
     *
     * The Weee amount will be added to row total including tax depending on Weee display setting.
     *
     * @return float
     */
    public function getRowDisplayPriceInclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->getIncludeWeeeFlag()
                    ? $child->getRowTotalInclTax() + $this->weeeHelper->getRowWeeeTaxInclTax($child)
                    : $child->getRowTotalInclTax();
            }
            return $this->reconcileCartRowInclTaxWithQuoteTotal($sum);
        }

        $item = $this->getItem();
        $rowTotalInclTax = (float)$item->getRowTotalInclTax();
        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $this->reconcileCartRowInclTaxWithQuoteTotal(
                $rowTotalInclTax + $this->weeeHelper->getRowWeeeTaxInclTax($this->getItem())
            );
        }

        return $rowTotalInclTax;
    }

    /**
     * Get base price for row total including tax.
     *
     * The Weee amount will be added to row total including tax depending on Weee display setting.
     *
     * @return float
     */
    public function getBaseRowDisplayPriceInclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->getIncludeWeeeFlag()
                    ? $child->getBaseRowTotalInclTax() + $this->weeeHelper->getBaseRowWeeeTaxInclTax($child)
                    : $child->getBaseRowTotalInclTax();
            }
            return $sum;
        }

        $baseRowTotalInclTax = $this->getItem()->getBaseRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $baseRowTotalInclTax + $this->weeeHelper->getBaseRowWeeeTaxInclTax($this->getItem());
        }

        return $baseRowTotalInclTax;
    }

    /**
     * Get display price for unit price excluding tax.
     *
     * The Weee amount will be added to unit price depending on Weee display setting.
     *
     * @return float
     */
    public function getUnitDisplayPriceExclTax()
    {
        $priceExclTax = $this->getItemDisplayPriceExclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            $qty = $this->getItemQtyForUnitPriceCalculation();
            return $qty > 0 ? $this->getRowDisplayPriceExclTax() / $qty
                : $priceExclTax + $this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem());
        }

        return $priceExclTax;
    }

    /**
     * Get base price for unit price excluding tax.
     *
     * The Weee amount will be added to unit price depending on Weee display setting.
     *
     * @return float
     */
    public function getBaseUnitDisplayPriceExclTax()
    {
        $orderItem = $this->getItem();
        if ($orderItem instanceof InvoiceItem || $orderItem instanceof CreditMemoItem) {
            $orderItem = $orderItem->getOrderItem();
        }

        $qty = $orderItem->getQtyOrdered();
        $basePriceExclTax = $orderItem->getBaseRowTotal() / $qty;

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            $qtyForUnit = $this->getItemQtyForUnitPriceCalculation();
            return $qtyForUnit > 0 ? $this->getBaseRowDisplayPriceExclTax() / $qtyForUnit
                : $basePriceExclTax + $this->getItem()->getBaseWeeeTaxAppliedAmount();
        }

        return $basePriceExclTax;
    }

    /**
     * Get display price for row total excluding tax.
     *
     * The Weee amount will be added to row total depending on Weee display setting.
     *
     * @return float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRowDisplayPriceExclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->getIncludeWeeeFlag()
                    ? $child->getRowTotal() + $this->weeeHelper->getWeeeTaxAppliedRowAmount($child)
                    : $child->getRowTotal();
            }
            return $sum;
        }

        $item = $this->getItem();
        $rowTotalExclTax = (float)$item->getRowTotal();
        if ($rowTotalExclTax <= 0) {
            $qty = method_exists($item, 'getTotalQty') ? (float)$item->getTotalQty() : (float)$item->getQty();
            $calculationPrice = (float)$item->getCalculationPrice();
            if ($calculationPrice > 0 && $qty > 0) {
                $rowTotalExclTax = $calculationPrice * $qty;
            }
        }

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $rowTotalExclTax + $this->weeeHelper->getWeeeTaxAppliedRowAmount($this->getItem());
        }

        return $rowTotalExclTax;
    }

    /**
     * Get base price for row total excluding tax.
     *
     * The Weee amount will be added to row total depending on Weee display setting.
     *
     * @return float
     */
    public function getBaseRowDisplayPriceExclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->getIncludeWeeeFlag()
                    ? $child->getBaseRowTotal() + $child->getBaseWeeeTaxAppliedRowAmnt()
                    : $child->getBaseRowTotal();
            }
            return $sum;
        }

        $baseRowTotalExclTax = $this->getItem()->getBaseRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $baseRowTotalExclTax + $this->getItem()->getBaseWeeeTaxAppliedRowAmnt();
        }

        return $baseRowTotalExclTax;
    }

    /**
     * Get final unit display price including tax.
     *
     * This will add Weee amount to unit price include tax.
     *
     * @return float
     */
    public function getFinalUnitDisplayPriceInclTax()
    {
        $priceInclTax = $this->getItem()->getPriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceInclTax;
        }

        $qty = $this->getItemQtyForUnitPriceCalculation();
        return $qty > 0 ? $this->getFinalRowDisplayPriceInclTax() / $qty
            : $priceInclTax + $this->weeeHelper->getWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get base final unit display price including tax.
     *
     * This will add Weee amount to unit price include tax.
     *
     * @return float
     */
    public function getBaseFinalUnitDisplayPriceInclTax()
    {
        $basePriceInclTax = $this->getItem()->getBasePriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceInclTax;
        }

        $qty = $this->getItemQtyForUnitPriceCalculation();
        return $qty > 0 ? $this->getBaseFinalRowDisplayPriceInclTax() / $qty
            : $basePriceInclTax + $this->weeeHelper->getBaseWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get final row display price including tax.
     *
     * This will add weee amount to rowTotalInclTax.
     *
     * @return float
     */
    public function getFinalRowDisplayPriceInclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->weeeHelper->isEnabled($this->getStoreId())
                    ? $child->getRowTotalInclTax() + $this->weeeHelper->getRowWeeeTaxInclTax($child)
                    : $child->getRowTotalInclTax();
            }
            return $this->reconcileCartRowInclTaxWithQuoteTotal($sum);
        }

        $rowTotalInclTax = $this->getItem()->getRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalInclTax;
        }

        return $this->reconcileCartRowInclTaxWithQuoteTotal(
            $rowTotalInclTax + $this->weeeHelper->getRowWeeeTaxInclTax($this->getItem())
        );
    }

    /**
     * Get base final row display price including tax.
     *
     * This will add weee amount to rowTotalInclTax.
     *
     * @return float
     */
    public function getBaseFinalRowDisplayPriceInclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->weeeHelper->isEnabled($this->getStoreId())
                    ? $child->getBaseRowTotalInclTax() + $this->weeeHelper->getBaseRowWeeeTaxInclTax($child)
                    : $child->getBaseRowTotalInclTax();
            }
            return $sum;
        }

        $baseRowTotalInclTax = $this->getItem()->getBaseRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalInclTax;
        }

        return $baseRowTotalInclTax + $this->weeeHelper->getBaseRowWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get final unit display price excluding tax
     *
     * @return float
     */
    public function getFinalUnitDisplayPriceExclTax()
    {
        $priceExclTax = $this->getItemDisplayPriceExclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceExclTax;
        }

        $qty = $this->getItemQtyForUnitPriceCalculation();
        return $qty > 0 ? $this->getFinalRowDisplayPriceExclTax() / $qty
            : $priceExclTax + $this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem());
    }

    /**
     * Get base final unit display price excluding tax
     *
     * @return float
     */
    public function getBaseFinalUnitDisplayPriceExclTax()
    {
        $orderItem = $this->getItem();
        if ($orderItem instanceof InvoiceItem || $orderItem instanceof CreditMemoItem) {
            $orderItem = $orderItem->getOrderItem();
        }

        $qty = $orderItem->getQtyOrdered();
        $basePriceExclTax = $orderItem->getBaseRowTotal() / $qty;

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceExclTax;
        }

        $qtyForUnit = $this->getItemQtyForUnitPriceCalculation();
        return $qtyForUnit > 0 ? $this->getBaseFinalRowDisplayPriceExclTax() / $qtyForUnit
            : $basePriceExclTax + $this->getItem()->getBaseWeeeTaxAppliedAmount();
    }

    /**
     * Get final row display price excluding tax.
     *
     * This will add Weee amount to rowTotal.
     *
     * @return float
     */
    public function getFinalRowDisplayPriceExclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->weeeHelper->isEnabled($this->getStoreId())
                    ? $child->getRowTotal() + $this->weeeHelper->getWeeeTaxAppliedRowAmount($child)
                    : $child->getRowTotal();
            }
            return $sum;
        }

        $rowTotalExclTax = $this->getItem()->getRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalExclTax;
        }

        return $rowTotalExclTax + $this->weeeHelper->getWeeeTaxAppliedRowAmount($this->getItem());
    }

    /**
     * Get base final row display price excluding tax.
     *
     * This will add Weee amount to rowTotal.
     *
     * @return float
     */
    public function getBaseFinalRowDisplayPriceExclTax()
    {
        $children = $this->getDynamicBundleChildren();
        if ($children !== []) {
            $sum = 0.0;
            foreach ($children as $child) {
                $sum += $this->weeeHelper->isEnabled($this->getStoreId())
                    ? $child->getBaseRowTotal() + $child->getBaseWeeeTaxAppliedRowAmnt()
                    : $child->getBaseRowTotal();
            }
            return $sum;
        }

        $baseRowTotalExclTax = $this->getItem()->getBaseRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalExclTax;
        }

        return $baseRowTotalExclTax + $this->getItem()->getBaseWeeeTaxAppliedRowAmnt();
    }

    /**
     * Whether to display final price that include Weee amounts
     *
     * @return bool
     */
    public function displayFinalPrice()
    {
        $flag = $this->weeeHelper->typeOfDisplay(
            WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL,
            $this->getZone(),
            $this->getStoreId()
        );

        if (!$flag) {
            return false;
        }

        if ($this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem()) <= 0) {
            return false;
        }
        return true;
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return mixed
     */
    public function getTotalAmount($item)
    {
        $totalAmount = $item->getRowTotal()
            - $item->getDiscountAmount()
            + $item->getTaxAmount()
            + $item->getDiscountTaxCompensationAmount()
            + $this->weeeHelper->getRowWeeeTaxInclTax($item);

        return $totalAmount;
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return mixed
     */
    public function getBaseTotalAmount($item)
    {
        $totalAmount = $item->getBaseRowTotal()
            - $item->getBaseDiscountAmount()
            + $item->getBaseTaxAmount()
            + $item->getBaseDiscountTaxCompensationAmount()
            + $this->weeeHelper->getBaseRowWeeeTaxInclTax($item);

        return $totalAmount;
    }

    /**
     * Quantity used to compute unit from row (row ÷ qty) for Weee-inclusive display.
     *
     * @return float
     */
    private function getItemQtyForUnitPriceCalculation()
    {
        $item = $this->getItem();
        if ($item instanceof QuoteAbstractItem) {
            $qty = (float) $item->getQty();
        } elseif ($item instanceof OrderItem) {
            $qty = (float) $item->getQtyOrdered();
        } elseif ($item instanceof InvoiceItem || $item instanceof CreditMemoItem) {
            $qty = (float) $item->getQty();
        } else {
            $qty = 1.0;
        }
        return $qty > 0 ? $qty : 1.0;
    }

    /**
     * Dynamic bundle children only (fixed bundle keeps row totals on parent).
     *
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem[]|\Magento\Sales\Model\Order\Item[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getDynamicBundleChildren(): array
    {
        $item = $this->getItem();
        if ($item instanceof InvoiceItem || $item instanceof CreditMemoItem) {
            $item = $item->getOrderItem();
        }
        if ($item instanceof QuoteAbstractItem) {
            if ($item->getProductType() !== BundleProductType::TYPE_CODE
                || !$item->getHasChildren()
                || !$item->isChildrenCalculated()) {
                return [];
            }
            $ch = $item->getChildren();
            return $ch ? iterator_to_array($ch) : [];
        }
        if ($item instanceof OrderItem
            && $item->getProductType() === BundleProductType::TYPE_CODE
            && $item->isChildrenCalculated()) {
            $ch = $item->getChildrenItems();
            return $ch ?: [];
        }
        return [];
    }

    /**
     * Single visible cart line: align incl. tax row with grand total minus shipping when off by ≤ 2¢.
     *
     * @param float $amount
     * @return float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function reconcileCartRowInclTaxWithQuoteTotal($amount)
    {
        $item = $this->getItem();
        if (!$item instanceof QuoteAbstractItem
            || $this->getZone() !== PricingRender::ZONE_CART
            || !$this->weeeHelper->isEnabled($this->getStoreId())
            || !$this->getIncludeWeeeFlag()) {
            return $amount;
        }
        $quote = $item->getQuote();
        if (!$quote) {
            return $amount;
        }
        $visible = $quote->getAllVisibleItems();
        if (count($visible) !== 1 || (int) reset($visible)->getId() !== (int) $item->getId()) {
            return $amount;
        }
        if ($quote->getIsVirtual()) {
            $shipping = 0.0;
        } else {
            $addr = $quote->getShippingAddress();
            if (!$addr) {
                return $amount;
            }
            $shipping = (float) $addr->getShippingInclTax();
            if ($shipping <= 0.0001) {
                $shipping = (float) $addr->getShippingAmount() + (float) $addr->getShippingTaxAmount();
            }
        }
        $expected = (float) $quote->getGrandTotal() - $shipping;
        if ($expected <= 0) {
            return $amount;
        }
        $diff = abs($expected - $amount);
        if ($diff > 0.0001 && $diff <= 0.02) {
            return $expected;
        }
        return $amount;
    }
}
