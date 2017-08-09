<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
