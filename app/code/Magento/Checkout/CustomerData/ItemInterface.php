<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;

/**
 * Item interface
 *
 * @api
 * @since 2.0.0
 */
interface ItemInterface
{
    /**
     * Get item data by quote item
     *
     * @param Item $item
     * @return array
     * @since 2.0.0
     */
    public function getItemData(Item $item);
}
