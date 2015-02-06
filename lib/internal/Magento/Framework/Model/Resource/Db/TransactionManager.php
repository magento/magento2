<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Resource\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

class TransactionManager implements TransactionManagerInterface
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
    public function start(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->beginTransaction();
    }

    /**
     * Vote that connection is ready to commit
     *
     * @param Connection $connection
     * @return void
     */
    public function end(Connection $connection)
    {

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

    /**
     * Validate data that is about to be saved. Check that referenced entity(s) exists.
     *
     * @param string $table
     * @param array $involvedData
     * @return void
     * @throws \LogicException
     */
    public function validate($table, array $involvedData)
    {

    }
}
