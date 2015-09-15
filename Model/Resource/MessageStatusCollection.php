<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Resource;

/**
 * Message Status collection.
 */
class MessageStatusCollection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\MysqlMq\Model\MessageStatus', 'Magento\MysqlMq\Model\Resource\MessageStatus');
    }
}
