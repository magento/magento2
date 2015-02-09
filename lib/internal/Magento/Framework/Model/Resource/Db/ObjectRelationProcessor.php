<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Resource\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

class ObjectRelationProcessor
{
    /**
     * Process delete action
     *
     * @param TransactionManagerInterface $transactionManager
     * @param Connection $connection
     * @param string $table
     * @param string $condition
     * @param array $involvedData
     * @return void
     * @throws \LogicException
     */
    public function delete(
        TransactionManagerInterface $transactionManager,
        Connection $connection,
        $table,
        $condition,
        array $involvedData
    ) {
        $connection->delete($table, $condition);
        $transactionManager->end($connection);
    }

    /**
     * Validate integrity of the given data
     *
     * @param string $table
     * @param array $involvedData
     */
    public function validateDataIntegrity($table, array $involvedData)
    {
        return;
    }
}
