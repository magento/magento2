<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist item option resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model\ResourceModel\Item;

/**
 * @api
 * @since 2.0.0
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('wishlist_item_option', 'option_id');
    }
}
