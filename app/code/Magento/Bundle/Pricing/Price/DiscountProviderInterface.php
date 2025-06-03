<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface DiscountProviderInterface
 * @api
 * @since 100.0.2
 */
interface DiscountProviderInterface
{
    /**
     * @return float
     */
    public function getDiscountPercent();
}
