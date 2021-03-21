<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

/**
 * Catalog price precision interface
 */
interface PricePrecisionInterface
{
    /**
     * Get price precision
     *
     * @return int
     */
    public function getPrecision(): int;
}
