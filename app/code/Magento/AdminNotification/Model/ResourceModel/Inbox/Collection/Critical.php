<?php
/**
 * Critical messages collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

use Magento\AdminNotification\Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Notification\MessageInterface;

/**
 * Class Critical
 *
 * @package Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
 * @api
 * @since 100.0.2
 */
class Critical extends AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_init(
            Model\Inbox::class,
            Model\ResourceModel\Inbox::class
        );
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initSelect() //phpcs:ignore
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
            MessageInterface::SEVERITY_CRITICAL
        )->setPageSize(
            1
        );
        return $this;
    }
}
