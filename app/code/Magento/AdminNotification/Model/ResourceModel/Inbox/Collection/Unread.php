<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Collection of unread notifications
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

class Unread extends \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
{
    /**
     * Init collection select
     *
     * @return \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Unread
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilter('is_remove', 0);
        $this->addFilter('is_read', 0);
        $this->setOrder('date_added');
        return $this;
    }
}
