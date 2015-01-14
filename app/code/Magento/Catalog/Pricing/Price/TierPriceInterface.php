<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

/**
 * Tier price interface
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
