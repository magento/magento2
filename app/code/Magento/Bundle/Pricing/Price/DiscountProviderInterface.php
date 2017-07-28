<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface DiscountProviderInterface
 * @api
 * @since 2.0.0
 */
interface DiscountProviderInterface
{
    /**
     * @return float
     * @since 2.0.0
     */
    public function getDiscountPercent();
}
