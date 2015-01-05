<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\AdminNotification\Model\Resource\System;

class Message extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Flag that notifies whether Primary key of table is auto-incremeted
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
