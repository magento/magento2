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
 * @since 100.0.2
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
     */
    public function getPrices();

    /**
     * Returns price by code
     *
     * @param string $priceCode
     * @return PriceInterface
     */
    public function getPrice($priceCode);

    /**
     * Get all registered adjustments
     *
     * @return AdjustmentInterface[]
     */
    public function getAdjustments();

    /**
     * Get adjustment by code
     *
     * @param string $adjustmentCode
     * @return AdjustmentInterface
     */
    public function getAdjustment($adjustmentCode);
}
