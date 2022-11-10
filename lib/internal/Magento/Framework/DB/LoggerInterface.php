<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB;

/**
 * DB logger interface
 *
 * @api
 */
interface LoggerInterface
{
    /**#@+
     * Types of connections to be logged
     */
    const TYPE_CONNECT     = 'connect';
    const TYPE_TRANSACTION = 'transaction';
    const TYPE_QUERY       = 'query';
    /**#@-*/

    /**
     * Adds log record
     *
     * @param string $str
     * @return void
     */
    public function log($str);

    /**
     * @return void
     */
    public function startTimer();

    /**
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return void
     */
    public function logStats($type, $sql, $bind = [], $result = null);

    /**
     * @param \Exception $e
     * @return void
     */
    public function critical(\Exception $e);
}
