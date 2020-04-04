<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel;

/**
 * Resource class for Bulk Operations
 */
class Operation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
