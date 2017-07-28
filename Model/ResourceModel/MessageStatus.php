<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\ResourceModel;

/**
 * Resource model for message status.
 * @since 2.0.0
 */
class MessageStatus extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('queue_message_status', 'id');
    }
}
