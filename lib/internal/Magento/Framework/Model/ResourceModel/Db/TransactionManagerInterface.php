<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

/**
 * @api
 * @since 2.0.0
 */
interface TransactionManagerInterface
{
    /**
     * Start transaction
     *
     * @param Connection $connection
     * @return Connection
     * @since 2.0.0
     */
    public function start(Connection $connection);

    /**
     * Commit transaction
     *
     * @return void
     * @since 2.0.0
     */
    public function commit();

    /**
     * Rollback transaction
     *
     * @return void
     * @since 2.0.0
     */
    public function rollBack();
}
