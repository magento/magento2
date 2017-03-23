<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;

/**
 * Item pool interface
 */
interface ItemPoolInterface
{
    /**
     * Get item data by quote item
     *
     * @param Item $item
     * @return array
     */
    public function getItemData(Item $item);
}
