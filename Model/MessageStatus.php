<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Message model for message status
 *
 * @api
 * @since 100.0.2
 */
class MessageStatus extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Magento\MysqlMq\Model\ResourceModel\MessageStatus::class);
    }
}
