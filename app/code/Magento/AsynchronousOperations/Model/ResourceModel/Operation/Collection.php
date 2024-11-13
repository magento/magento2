<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\AsynchronousOperations\Model\Operation;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation as OperationResourceModel;

/**
 * Class Collection for Magento Operation table
 * @codeCoverageIgnore
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define collection item type and corresponding table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Operation::class,
            OperationResourceModel::class
        );
        $this->setMainTable('magento_operation');
        $this->_setIdFieldName(OperationResourceModel::TABLE_PRIMARY_KEY);
    }
}
