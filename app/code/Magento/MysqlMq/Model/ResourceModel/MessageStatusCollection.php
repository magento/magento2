<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\ResourceModel;

/**
 * Message Status collection.
 *
 * @api
 * @since 100.0.2
 */
class MessageStatusCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\MysqlMq\Model\MessageStatus::class,
            \Magento\MysqlMq\Model\ResourceModel\MessageStatus::class
        );
    }
}
