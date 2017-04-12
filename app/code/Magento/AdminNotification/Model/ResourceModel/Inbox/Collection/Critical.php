<?php
/**
 * Critical messages collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

class Critical extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\AdminNotification\Model\Inbox::class,
            \Magento\AdminNotification\Model\ResourceModel\Inbox::class
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addOrder(
            'notification_id',
            self::SORT_ORDER_DESC
        )->addFieldToFilter(
            'is_read',
            ['neq' => 1]
        )->addFieldToFilter(
            'is_remove',
            ['neq' => 1]
        )->addFieldToFilter(
            'severity',
            \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
        )->setPageSize(
            1
        );
        return $this;
    }
}
