<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Widget\Model\ResourceModel\Layout;

/**
 * Layout Link resource model
 */
class Link extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('layout_link', 'layout_link_id');
    }
}
