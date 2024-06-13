<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Model\ResourceModel\Link;

/**
 * Downloadable Product link purchased resource model
 */
class Purchased extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Magento class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('downloadable_link_purchased', 'purchased_id');
    }
}
