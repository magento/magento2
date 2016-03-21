<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Amount;

/**
 * Amount interface, the amount values are in display currency
 *
 * @api
 */
interface AmountInterface
{
    /**
     * Return full amount value
     *
     * @param null|string|array $exclude
     * @return float
     */
    public function getValue($exclude = null);

    /**
     * Return full amount value in string format
     *
     * @return string
     */
    public function __toString();

    /**
     * Return base amount part value
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Return adjustment amount part value by adjustment code
     *
     * @param string $adjustmentCode
     * @return float
     */
    public function getAdjustmentAmount($adjustmentCode);

    /**
     * Return sum amount of all applied adjustments
     *
     * @return float
     */
    public function getTotalAdjustmentAmount();

    /**
     * Return all applied adjustments as array
     *
     * @return float[]
     */
    public function getAdjustmentAmounts();

    /**
     * Check if adjustment is contained in amount object
     *
     * @param string $adjustmentCode
     * @return boolean
     */
    public function hasAdjustment($adjustmentCode);
}
