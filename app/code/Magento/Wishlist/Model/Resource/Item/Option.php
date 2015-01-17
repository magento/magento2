<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist item option resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model\Resource\Item;

class Option extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('wishlist_item_option', 'option_id');
    }
}
