<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\ResourceModel\Layout;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Layout Link resource model
 */
class Link extends AbstractDb
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
