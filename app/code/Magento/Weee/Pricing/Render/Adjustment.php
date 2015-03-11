<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\View\Element\Template;
use Magento\Weee\Model\Tax;

/**
 * Weee Tax Price Adjustment
 */
class Adjustment extends AbstractAdjustment
{
    /**
     * Weee helper
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var float
     */
    protected $finalAmount;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param array $data
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
     */
    protected function apply()
    {
        if ($this->typeOfDisplay([Tax::DISPLAY_EXCL, Tax::DISPLAY_EXCL_DESCR_INCL])) {
            $this->finalAmount = $this->amountRender->getDisplayValue();
            $this->amountRender->setDisplayValue(
                $this->amountRender->getDisplayValue() -
                $this->amountRender->getAmount()->getAdjustmentAmount($this->getAdjustmentCode())
            );
        }
        return $this->toHtml();
    }

    /**
     * Obtain adjustment code
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return \Magento\Weee\Pricing\Adjustment::ADJUSTMENT_CODE;
    }

    /**
     * @return float
     */
    public function getFinalAmount()
    {
        return $this->formatCurrency($this->finalAmount);
    }

    /**
     * Get weee amount
     *
     * @return float
     */
    protected function getWeeeTaxAmount()
    {
        $product = $this->getSaleableItem();
        return $this->weeeHelper->getAmount($product);
    }

    /**
     * Define if adjustment should be shown with including tax, description
     *
     * @return bool
     */
    public function showInclDescr()
    {
        return $this->isDisplayFpt() && $this->getWeeeTaxAmount() && $this->typeOfDisplay(Tax::DISPLAY_INCL_DESCR);
    }

    /**
     * Define if adjustment should be shown with including tax, excluding tax, description
     *
     * @return bool
     */
    public function showExclDescrIncl()
    {
        return $this->isDisplayFpt() && $this->getWeeeTaxAmount() && $this->typeOfDisplay(Tax::DISPLAY_EXCL_DESCR_INCL);
    }

    /**
     * Obtain Weee tax attributes
     *
     * @return array|\Magento\Framework\Object[]
     */
    public function getWeeeTaxAttributes()
    {
        return $this->isDisplayFpt() ? $this->getWeeeAttributesForDisplay() : [];
    }

    /**
     * Render Weee tax attributes name
     *
     * @param \Magento\Framework\Object $attribute
     * @return string
     */
    public function renderWeeeTaxAttributeName(\Magento\Framework\Object $attribute)
    {
        return $attribute->getData('name');
    }

    /**
     * Render Weee tax attributes value
     *
     * @param \Magento\Framework\Object $attribute
     * @return string
     */
    public function renderWeeeTaxAttribute(\Magento\Framework\Object $attribute)
    {
        return $this->convertAndFormatCurrency($attribute->getData('amount'));
    }

    /**
     * Returns display type for price accordingly to current zone
     *
     * @param int|int[]|null $compareTo
     * @param \Magento\Store\Model\Store|null $store
     * @return bool|int
     */
    protected function typeOfDisplay($compareTo = null, $store = null)
    {
        return $this->weeeHelper->typeOfDisplay($compareTo, $this->getZone(), $store);
    }

    /**
     * Get Weee attributes for display
     *
     * @return \Magento\Framework\Object[]
     */
    protected function getWeeeAttributesForDisplay()
    {
        $product = $this->getSaleableItem();
        return $this->weeeHelper->getProductWeeeAttributesForDisplay($product);
    }

    /**
     * Define if the FPT should be displayed
     *
     * @return bool
     */
    protected function isDisplayFpt()
    {
        $isDisplayFpt = $this->typeOfDisplay([Tax::DISPLAY_INCL_DESCR, Tax::DISPLAY_EXCL_DESCR_INCL]);
        return $isDisplayFpt;
    }
}
