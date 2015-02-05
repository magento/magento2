<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Resource\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

interface ObjectRelationProcessorInterface
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
     * Start transaction
     *
     * @param Connection $connection
     * @return void
     */
    public function beginTransaction(Connection $connection);

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
