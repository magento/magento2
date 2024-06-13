<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Pricing\Price;

/**
 * Special price interface
 *
 * @api
 */
interface SpecialPriceInterface
{
    /**
     * Returns special price
     *
     * @return float
     */
    public function getSpecialPrice();

    /**
     * Returns starting date of the special price
     *
     * @return mixed
     */
    public function getSpecialFromDate();

    /**
     * Returns end date of the special price
     *
     * @return mixed
     */
    public function getSpecialToDate();

    /**
     * @return bool
     */
    public function isScopeDateInInterval();

    /**
     * @return bool
     */
    public function isPercentageDiscount();
}
