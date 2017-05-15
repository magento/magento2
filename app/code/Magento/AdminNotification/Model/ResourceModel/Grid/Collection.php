<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * AdminNotification Inbox model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdminNotification\Model\ResourceModel\Grid;

class Collection extends \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
{
    /**
     * Add remove filter
     *
     * @return \Magento\AdminNotification\Model\ResourceModel\Grid\Collection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addRemoveFilter();
        return $this;
    }
}
