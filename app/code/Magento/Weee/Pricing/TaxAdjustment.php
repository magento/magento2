<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing;

use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Weee tax pricing adjustment
 */
class TaxAdjustment implements AdjustmentInterface
{
    /**
     * Adjustment code weee
     */
    const ADJUSTMENT_CODE = 'weee_tax';

    /**
     * Weee helper
     *
     * @var WeeeHelper
     */
    protected $weeeHelper;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * Sort order
     *
     * @var int|null
     */
    protected $sortOrder;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Constructor
     *
     * @param WeeeHelper $weeeHelper
     * @param TaxHelper $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param int $sortOrder
     */
    public function __construct(
        WeeeHelper $weeeHelper,
        TaxHelper $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        $sortOrder = null
    ) {
        $this->weeeHelper = $weeeHelper;
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get adjustment code
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return self::ADJUSTMENT_CODE;
    }

    /**
     * Define if adjustment is included in base price
     * (FPT is excluded from base price)
     *
     * @return bool
     */
    public function isIncludedInBasePrice()
    {
        return false;
    }

    /**
     * Define if adjustment is included in display price
     *
     * @return bool
     */
    public function isIncludedInDisplayPrice()
    {
        if ($this->taxHelper->displayPriceExcludingTax()) {
            return false;
        }
        if ($this->weeeHelper->isEnabled() == true &&
            $this->weeeHelper->isTaxable() == true &&
            $this->weeeHelper->typeOfDisplay([\Magento\Weee\Model\Tax::DISPLAY_EXCL]) == false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Extract adjustment amount from the given amount value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return 0;
    }

    /**
     * Apply adjustment amount and return result value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     */
    public function applyAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        if (isset($context[CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG])) {
            return $amount;
        }
        return $amount + $this->getAmount($saleableItem);
    }

    /**
     * Check if adjustment should be excluded from calculations along with the given adjustment
     *
     * @param string $adjustmentCode
     * @return bool
     */
    public function isExcludedWith($adjustmentCode)
    {
        return (($adjustmentCode == self::ADJUSTMENT_CODE) ||
            ($adjustmentCode == \Magento\Tax\Pricing\Adjustment::ADJUSTMENT_CODE));
    }

    /**
     * Obtain amount
     *
     * @param SaleableInterface $saleableItem
     * @return float
     */
    protected function getAmount(SaleableInterface $saleableItem)
    {
        $weeeTaxAmount = 0;
        $attributes = $this->weeeHelper->getProductWeeeAttributes($saleableItem, null, null, null, true, false);
        if ($attributes != null) {
            foreach ($attributes as $attribute) {
                $weeeTaxAmount += $attribute->getData('tax_amount');
            }
        }
        $weeeTaxAmount = $this->priceCurrency->convert($weeeTaxAmount);
        return $weeeTaxAmount;
    }

    /**
     * Return sort order position
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}
