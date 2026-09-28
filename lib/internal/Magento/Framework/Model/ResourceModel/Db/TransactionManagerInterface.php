<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

/**
 * @api
 * @since 100.0.2
 */
interface TransactionManagerInterface
{
    /**
     * Start transaction
     *
     * @param Connection $connection
     * @return Connection
     */
    public function start(Connection $connection);

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
