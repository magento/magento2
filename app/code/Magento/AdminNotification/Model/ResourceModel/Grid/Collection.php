<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * AdminNotification Inbox model
 */
namespace Magento\AdminNotification\Model\ResourceModel\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
{
    /**
     * Add remove filter
     *
     * @return Collection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addRemoveFilter();
        return $this;
    }
}
