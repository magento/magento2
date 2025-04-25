<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

class TransactionManager implements TransactionManagerInterface
{
    /**
     * @var Connection[]
     */
    protected $participants = [];

    /**
     * @inheritdoc
     */
    public function start(Connection $connection)
    {
        $this->participants[] = $connection;
        $connection->beginTransaction();
        return $connection;
    }

    /**
     * @inheritdoc
     */
    public function commit()
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        while ($connection = array_pop($this->participants)) {
            $connection->commit();
        }
    }

    /**
     * @inheritdoc
     */
    public function rollBack()
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        while ($connection = array_pop($this->participants)) {
            $connection->rollBack();
        }
    }

    /**
     * Get object key
     *
     * @param Connection $connection
     * @return string
     */
    protected function getConnectionKey(Connection $connection)
    {
        return spl_object_hash($connection);
    }
}
