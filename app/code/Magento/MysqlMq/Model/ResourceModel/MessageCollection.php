<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\MysqlMq\Model\ResourceModel;

/**
 * Message collection.
 */
class MessageCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\MysqlMq\Model\Message::class, \Magento\MysqlMq\Model\ResourceModel\Message::class);
    }
}
