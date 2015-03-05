<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Resource\Layout;

/**
 * Layout Link resource model
 */
class Link extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
