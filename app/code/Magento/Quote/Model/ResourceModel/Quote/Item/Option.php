<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Item;

/**
 * Item option resource model
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_item_option', 'option_id');
    }
}
