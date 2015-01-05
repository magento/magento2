<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
