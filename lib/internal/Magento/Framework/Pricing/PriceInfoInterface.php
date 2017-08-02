<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Price info model interface
 *
 * @api
 * @since 2.0.0
 */
interface PriceInfoInterface
{
    /**
     * Default product quantity
     */
    const PRODUCT_QUANTITY_DEFAULT = 1.;

    /**
     * Returns array of prices
     *
     * @return PriceInterface[]
     * @since 2.0.0
     */
    public function getPrices();

    /**
     * Returns price by code
     *
     * @param string $priceCode
     * @return PriceInterface
     * @since 2.0.0
     */
    public function getPrice($priceCode);

    /**
     * Get all registered adjustments
     *
     * @return AdjustmentInterface[]
     * @since 2.0.0
     */
    public function getAdjustments();

    /**
     * Get adjustment by code
     *
     * @param string $adjustmentCode
     * @return AdjustmentInterface
     * @since 2.0.0
     */
    public function getAdjustment($adjustmentCode);
}
