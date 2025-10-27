<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Wishlist\Model\ResourceModel\Item;

/**
 * Wishlist item option resource model
 *
 * @api
 * @since 100.0.2
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialise the resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('wishlist_item_option', 'option_id');
    }
}
