<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface DiscountProviderInterface
 * @api
 */
interface DiscountProviderInterface
{
    /**
     * @return float
     */
    public function getDiscountPercent();
}
