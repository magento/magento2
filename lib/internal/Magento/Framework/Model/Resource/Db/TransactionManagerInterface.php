<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Resource\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

interface TransactionManagerInterface
{
    /**
     * Process delete operation
     *
     * @param Connection $connection
     * @param string $table
     * @param string $condition
     * @param array $involvedData
     * @return void
     */
    public function delete(Connection $connection, $table, $condition, array $involvedData);

    /**
     * Validate data that is about to be saved. Check that referenced entity(s) exists.
     *
     * @param string $table
     * @param array $involvedData
     * @return void
     * @throws \LogicException
     */
    public function validate($table, array $involvedData);

    /**
     * Start transaction
     *
     * @param Connection $connection
     * @return Connection
     */
    public function start(Connection $connection);

    /**
     * Vote that connection is ready to commit
     *
     * @param Connection $connection
     * @return void
     */
    public function end(Connection $connection);

    /**
     * Commit transaction
     *
     * @return void
     */
    public function commit();

    /**
     * Rollback transaction
     *
     * @return void
     */
    public function rollBack();
}
