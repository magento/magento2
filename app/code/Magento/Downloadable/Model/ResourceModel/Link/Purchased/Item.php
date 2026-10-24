<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Model\ResourceModel\Link\Purchased;

/**
 * Downloadable Product link purchased items resource model
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Magento class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('downloadable_link_purchased_item', 'item_id');
    }
}
