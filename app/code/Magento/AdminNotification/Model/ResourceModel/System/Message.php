<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\ResourceModel\System;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Message
 *
 * @package Magento\AdminNotification\Model\ResourceModel\System
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Message extends AbstractDb
{
    /**
     * Flag that notifies whether Primary key of table is auto-incremented
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false; //phpcs:ignore

    /**
     * Resource initialization
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_init('admin_system_messages', 'identity');
    }
}
