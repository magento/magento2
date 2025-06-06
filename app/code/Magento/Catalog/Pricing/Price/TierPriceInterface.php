<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Pricing\Price;

/**
 * Tier price interface
 *
 * @api
 * @since 100.0.2
 */
interface TierPriceInterface
{
    /**
     * @return array
     */
    public function getTierPriceList();

    /**
     * @return int
     */
    public function getTierPriceCount();

    /**
     * @return bool
     */
    public function isPercentageDiscount();
}
