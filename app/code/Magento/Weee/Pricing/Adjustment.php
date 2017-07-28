<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing;

use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Weee\Helper\Data as WeeeHelper;

/**
 * Weee pricing adjustment
 * @since 2.0.0
 */
class Adjustment implements AdjustmentInterface
{
    /**
     * Adjustment code weee
     */
    const ADJUSTMENT_CODE = 'weee';

    /**
     * Weee helper
     *
     * @var WeeeHelper
     * @since 2.0.0
     */
    protected $weeeHelper;

    /**
     * Sort order
     *
     * @var int|null
     * @since 2.0.0
     */
    protected $sortOrder;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * Constructor
     *
     * @param WeeeHelper $weeeHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param int $sortOrder
     * @since 2.0.0
     */
    public function __construct(WeeeHelper $weeeHelper, PriceCurrencyInterface $priceCurrency, $sortOrder = null)
    {
        $this->weeeHelper = $weeeHelper;
        $this->priceCurrency = $priceCurrency;
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get adjustment code
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isIncludedInBasePrice()
    {
        return false;
    }

    /**
     * Define if adjustment is included in display price
     *
     * @return bool
     * @since 2.0.0
     */
    public function isIncludedInDisplayPrice()
    {
        return $this->weeeHelper->typeOfDisplay(
            [
                \Magento\Weee\Model\Tax::DISPLAY_INCL,
                \Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR,
                \Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL,
            ]
        );
    }

    /**
     * Extract adjustment amount from the given amount value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getAmount(SaleableInterface $saleableItem)
    {
        $weeeAmount = $this->weeeHelper->getAmountExclTax($saleableItem);
        $weeeAmount = $this->priceCurrency->convert($weeeAmount);
        return $weeeAmount;
    }

    /**
     * Return sort order position
     *
     * @return int
     * @since 2.0.0
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}
