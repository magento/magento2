<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource class for Bulk Operations
 */
class Operation extends AbstractDb
{

    public const TABLE_NAME = "magento_operation";
    public const TABLE_PRIMARY_KEY = "id";

    /**
     * Initialize banner sales rule resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::TABLE_PRIMARY_KEY);
    }
}
