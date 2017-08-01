<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Pricing;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Tax pricing adjustment model
 * @since 2.0.0
 */
class Adjustment implements AdjustmentInterface
{
    /**
     * Adjustment code tax
     */
    const ADJUSTMENT_CODE = 'tax';

    /**
     * @var TaxHelper
     * @since 2.0.0
     */
    protected $taxHelper;

    /**
     * \Magento\Catalog\Helper\Data
     *
     * @var CatalogHelper
     * @since 2.0.0
     */
    protected $catalogHelper;

    /**
     * @var int|null
     * @since 2.0.0
     */
    protected $sortOrder;

    /**
     * @param TaxHelper $taxHelper
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param int|null $sortOrder
     * @since 2.0.0
     */
    public function __construct(TaxHelper $taxHelper, \Magento\Catalog\Helper\Data $catalogHelper, $sortOrder = null)
    {
        $this->taxHelper = $taxHelper;
        $this->catalogHelper = $catalogHelper;
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
     *
     * @return bool
     * @since 2.0.0
     */
    public function isIncludedInBasePrice()
    {
        return $this->taxHelper->priceIncludesTax();
    }

    /**
     * Define if adjustment is included in display price
     *
     * @return bool
     * @since 2.0.0
     */
    public function isIncludedInDisplayPrice()
    {
        return $this->taxHelper->displayPriceIncludingTax() || $this->taxHelper->displayBothPrices();
    }

    /**
     * Extract adjustment amount from the given amount value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     * @since 2.0.0
     */
    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        if ($this->taxHelper->priceIncludesTax()) {
            $adjustedAmount = $this->catalogHelper->getTaxPrice(
                $saleableItem,
                $amount,
                false,
                null,
                null,
                null,
                null,
                null,
                false
            );
            $result = $amount - $adjustedAmount;
        } else {
            $result = 0.;
        }
        return $result;
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
        return $this->catalogHelper->getTaxPrice(
            $saleableItem,
            $amount,
            true,
            null,
            null,
            null,
            null,
            null,
            false
        );
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
        return $this->getAdjustmentCode() === $adjustmentCode;
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
