<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\ResourceModel\System;

/**
 * @api
 * @since 2.0.0
 */
class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Flag that notifies whether Primary key of table is auto-incremeted
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('admin_system_messages', 'identity');
    }
}
