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
 * @since 2.0.0
 */
interface TierPriceInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function getTierPriceList();

    /**
     * @return int
     * @since 2.0.0
     */
    public function getTierPriceCount();

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isPercentageDiscount();
}
