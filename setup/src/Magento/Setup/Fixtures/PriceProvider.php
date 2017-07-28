<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Random price provider for fixtures
 * @since 2.2.0
 */
class PriceProvider
{
    /**
     * Get random price for product
     *
     * @param int $productIndex
     * @return float
     * @since 2.2.0
     */
    public function getPrice($productIndex)
    {
        mt_srand($productIndex);
        switch (mt_rand(0, 3)) {
            case 0:
                return 9.99;
            case 1:
                return 5;
            case 2:
                return 1;
            case 3:
                return mt_rand(1, 10000) / 10;
        }
    }
}
