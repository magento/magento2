<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Amount;

/**
 * Amount interface, the amount values are in display currency
 *
 * @api
 * @since 2.0.0
 */
interface AmountInterface
{
    /**
     * Return full amount value
     *
     * @param null|string|array $exclude
     * @return float
     * @since 2.0.0
     */
    public function getValue($exclude = null);

    /**
     * Return full amount value in string format
     *
     * @return string
     * @since 2.0.0
     */
    public function __toString();

    /**
     * Return base amount part value
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseAmount();

    /**
     * Return adjustment amount part value by adjustment code
     *
     * @param string $adjustmentCode
     * @return float
     * @since 2.0.0
     */
    public function getAdjustmentAmount($adjustmentCode);

    /**
     * Return sum amount of all applied adjustments
     *
     * @return float
     * @since 2.0.0
     */
    public function getTotalAdjustmentAmount();

    /**
     * Return all applied adjustments as array
     *
     * @return float[]
     * @since 2.0.0
     */
    public function getAdjustmentAmounts();

    /**
     * Check if adjustment is contained in amount object
     *
     * @param string $adjustmentCode
     * @return boolean
     * @since 2.0.0
     */
    public function hasAdjustment($adjustmentCode);
}
