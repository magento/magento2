<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cron\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Retrier Interface
 *
 * @api
 */
interface DeadlockRetrierInterface
{
    /**
     * Maximum numbers of attempts
     */
    public const MAX_RETRIES = 5;

    /**
     * Runs callback function
     *
     * If $callback throws an exception DeadlockException,
     * this callback will be run maximum self::MAX_RETRIES times or less.
     *
     * @param callable $callback
     * @param AdapterInterface $connection
     * @return mixed
     */
    public function execute(callable $callback, AdapterInterface $connection);
}
