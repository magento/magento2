<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Reorder;

use Magento\Sales\Model\Order\Item;

/**
 * @api
 * @since 101.0.0
 */
interface OrderedProductAvailabilityCheckerInterface
{
    /**
     * Checks that the selected options of order item are still presented in Catalog.
     * Returns true if the previously ordered item configuration is still available.
     *
     * @param Item $item
     * @return bool
     * @since 101.0.0
     */
    public function isAvailable(Item $item);
}
