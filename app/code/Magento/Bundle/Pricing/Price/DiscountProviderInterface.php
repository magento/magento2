<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface DiscountProviderInterface
 */
interface DiscountProviderInterface
{
    /**
     * @return float
     */
    public function getDiscountPercent();
}
