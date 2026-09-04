<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;

/**
 * Item pool interface
 *
 * @api
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
