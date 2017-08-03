<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\View\Element\Template;
use Magento\Weee\Model\Tax;

/**
 * Weee Price Adjustment that handles Weee specific amount and its display
 * @since 2.0.0
 */
class Adjustment extends AbstractAdjustment
{
    /**
     * Weee helper
     *
     * @var \Magento\Weee\Helper\Data
     * @since 2.0.0
     */
    protected $weeeHelper;

    /**
     * @var float
     * @since 2.0.0
     */
    protected $finalAmount;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Weee\Helper\Data $weeeHelper,
        array $data = []
    ) {
        $this->weeeHelper = $weeeHelper;
        parent::__construct($context, $priceCurrency, $data);
    }

    /**
     * @return null
     * @since 2.0.0
     */
    protected function apply()
    {
        $weeeAmount = $this->amountRender->getAmount()->getAdjustmentAmount($this->getAdjustmentCode());
        $weeeTaxAmount =
            $this->amountRender->getAmount()->getAdjustmentAmount(\Magento\Weee\Pricing\TaxAdjustment::ADJUSTMENT_CODE);

        $this->finalAmount = $this->amountRender->getDisplayValue();

        if ($this->typeOfDisplay([Tax::DISPLAY_EXCL_DESCR_INCL])) {
            $this->amountRender->setDisplayValue(
                $this->amountRender->getDisplayValue() - $weeeAmount - $weeeTaxAmount
            );
        }
        return $this->toHtml();
    }

    /**
     * @return float
     * @since 2.0.0
     */
    public function getRawFinalAmount()
    {
        return $this->finalAmount;
    }

    /**
     * Obtain adjustment code
     *
     * @return string
     * @since 2.0.0
     */
    public function getAdjustmentCode()
    {
        return \Magento\Weee\Pricing\Adjustment::ADJUSTMENT_CODE;
    }

    /**
     * @return float
     * @since 2.0.0
     */
    public function getFinalAmount()
    {
        return $this->formatCurrency($this->finalAmount);
    }

    /**
     * Get weee amount
     *
     * @return float
     * @since 2.0.0
     */
    protected function getWeeeAmount()
    {
        $product = $this->getSaleableItem();
        return $this->weeeHelper->getAmountExclTax($product);
    }

    /**
     * Define if adjustment should be shown with including tax, description
     *
     * @return bool
     * @since 2.0.0
     */
    public function showInclDescr()
    {
        return $this->isWeeeShown() && $this->getWeeeAmount() && $this->typeOfDisplay(Tax::DISPLAY_INCL_DESCR);
    }

    /**
     * Define if adjustment should be shown with including tax, excluding tax, description
     *
     * @return bool
     * @since 2.0.0
     */
    public function showExclDescrIncl()
    {
        return $this->isWeeeShown() && $this->getWeeeAmount() && $this->typeOfDisplay(Tax::DISPLAY_EXCL_DESCR_INCL);
    }

    /**
     * Obtain Weee tax attributes
     *
     * @return array|\Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    public function getWeeeTaxAttributes()
    {
        return $this->isWeeeShown() ? $this->getWeeeAttributesForDisplay() : [];
    }

    /**
     * Render Weee tax attributes name
     *
     * @param \Magento\Framework\DataObject $attribute
     * @return string
     * @since 2.0.0
     */
    public function renderWeeeTaxAttributeName(\Magento\Framework\DataObject $attribute)
    {
        return $attribute->getData('name');
    }

    /**
     * Render Weee tax attributes value
     *
     * @param \Magento\Framework\DataObject $attribute
     * @return string
     * @since 2.0.0
     */
    public function renderWeeeTaxAttribute(\Magento\Framework\DataObject $attribute)
    {
        return $this->convertAndFormatCurrency($attribute->getData('amount'));
    }

    /**
     * Render Weee tax attributes value with tax
     *
     * @param \Magento\Framework\DataObject $attribute
     * @return string
     * @since 2.0.0
     */
    public function renderWeeeTaxAttributeWithTax(\Magento\Framework\DataObject $attribute)
    {
        return $this->convertAndFormatCurrency(
            $attribute->getData('amount_excl_tax') + $attribute->getData('tax_amount')
        );
    }

    /**
     * Render Weee tax attributes value without tax
     *
     * @param \Magento\Framework\DataObject $attribute
     * @return string
     * @since 2.0.0
     */
    public function renderWeeeTaxAttributeWithoutTax(\Magento\Framework\DataObject $attribute)
    {
        $price = $attribute->getData('amount_excl_tax');
        return ($price > 0) ? $this->convertAndFormatCurrency($price): $this->convertAndFormatCurrency(0);
    }

    /**
     * Returns display type for price accordingly to current zone
     *
     * @param int|int[]|null $compareTo
     * @param \Magento\Store\Model\Store|null $store
     * @return bool|int
     * @since 2.0.0
     */
    protected function typeOfDisplay($compareTo = null, $store = null)
    {
        return $this->weeeHelper->typeOfDisplay($compareTo, $this->getZone(), $store);
    }

    /**
     * Get Weee attributes for display
     *
     * @return \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    protected function getWeeeAttributesForDisplay()
    {
        $product = $this->getSaleableItem();
        return $this->weeeHelper->getProductWeeeAttributesForDisplay($product);
    }

    /**
     * Returns whether Weee should be displayed
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isWeeeShown()
    {
        $isWeeeShown = $this->typeOfDisplay([Tax::DISPLAY_INCL_DESCR, Tax::DISPLAY_EXCL_DESCR_INCL]);
        return $isWeeeShown;
    }

    /**
     * Returns whether tax should be shown (according to Price Display Settings)
     *
     * @return int
     * @since 2.0.0
     */
    public function getTaxDisplayConfig()
    {
        $showPriceWithTax = $this->weeeHelper->getTaxDisplayConfig();
        return $showPriceWithTax;
    }

    /**
     * Returns whether price includes tax
     *
     * @return bool
     * @since 2.0.0
     */
    public function isPriceIncludesTax()
    {
        $showPriceWithTax = $this->weeeHelper->displayTotalsInclTax();
        return $showPriceWithTax;
    }
}
