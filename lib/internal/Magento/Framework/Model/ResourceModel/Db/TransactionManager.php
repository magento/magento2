<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

/**
 * Class \Magento\Framework\Model\ResourceModel\Db\TransactionManager
 *
 * @since 2.0.0
 */
class TransactionManager implements TransactionManagerInterface
{
    /**
     * @var Connection[]
     * @since 2.0.0
     */
    protected $participants;

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function start(Connection $connection)
    {
        $this->participants[] = $connection;
        $connection->beginTransaction();
        return $connection;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function commit()
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        while ($connection = array_pop($this->participants)) {
            $connection->commit();
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getConnectionKey(Connection $connection)
    {
        return spl_object_hash($connection);
    }
}
