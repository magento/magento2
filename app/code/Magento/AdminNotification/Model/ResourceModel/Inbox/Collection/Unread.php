<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


declare(strict_types=1);

/**
 * Collection of unread notifications
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

/**
 * Class Unread
 *
 * @package Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
 * @api
 * @since 100.0.2
 */
class Unread extends Collection
{
    /**
     * Init collection select
     *
     * @return \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Unread
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initSelect() //phpcs:ignore
    {
        parent::_initSelect();
        $this->addFilter('is_remove', 0);
        $this->addFilter('is_read', 0);
        $this->setOrder('date_added');
        return $this;
    }
}
