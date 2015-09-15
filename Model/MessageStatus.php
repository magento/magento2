<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Message model for message status
 */
class MessageStatus extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magento\MysqlMq\Model\Resource\MessageStatus');
    }
}
