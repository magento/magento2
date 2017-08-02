<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

/**
 * Special price interface
 * @since 2.0.0
 */
interface SpecialPriceInterface
{
    /**
     * Returns special price
     *
     * @return float
     * @since 2.0.0
     */
    public function getSpecialPrice();

    /**
     * Returns starting date of the special price
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getSpecialFromDate();

    /**
     * Returns end date of the special price
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getSpecialToDate();

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isScopeDateInInterval();

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isPercentageDiscount();
}
