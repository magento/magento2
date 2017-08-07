<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

/**
 * Tier price interface
 *
 * @api
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
