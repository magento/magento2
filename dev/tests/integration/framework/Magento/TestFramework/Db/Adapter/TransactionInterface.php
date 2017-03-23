<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * DB adapter transaction interface that allows starting transaction with adjusted level,
 * transparently to the application
 */
namespace Magento\TestFramework\Db\Adapter;

interface TransactionInterface
{
    /**
     * Increment "transparent" transaction counter and start real transaction
     *
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    public function beginTransparentTransaction();

    /**
     * Decrement "transparent" transaction counter and commit real transaction
     *
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    public function commitTransparentTransaction();

    /**
     * Decrement "transparent" transaction counter and rollback real transaction
     *
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    public function rollbackTransparentTransaction();
}
