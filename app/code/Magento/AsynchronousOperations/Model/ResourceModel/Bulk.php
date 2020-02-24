<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Bulk
 */
class Bulk extends AbstractDb
{
    public const TABLE_NAME = "magento_bulk";
    public const TABLE_PRIMARY_KEY = "uuid";

    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

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
