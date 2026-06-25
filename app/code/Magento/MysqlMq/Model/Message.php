<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\MysqlMq\Model;

/**
 * Message model
 *
 * @api
 * @since 100.0.2
 */
class Message extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Magento\MysqlMq\Model\ResourceModel\Message::class);
    }
}
