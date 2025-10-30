<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Model\ResourceModel\System;

/**
 * @api
 * @since 100.0.2
 */
class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Flag that notifies whether Primary key of table is auto-incremented
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('admin_system_messages', 'identity');
    }
}
