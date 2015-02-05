<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Resource\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

class ObjectRelationProcessor implements ObjectRelationProcessorInterface
{
    /**
     * @var Connection
     */
    protected $connection = null;

    /**
     * {@inheritdoc}
     */
    public function delete(Connection $connection, $table, $condition, array $involvedData)
    {
        $connection->delete($table, $condition);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }
}
