<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Resource;

/**
 * Message collection.
 */
class MessageCollection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\MysqlMq\Model\Message', 'Magento\MysqlMq\Model\Resource\Message');
    }
}
