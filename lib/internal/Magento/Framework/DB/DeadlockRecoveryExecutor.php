<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;

/**
 * Executes database operations with automatic retry handling for deadlock failures in concurrent environments.
 */
class DeadlockRecoveryExecutor implements DeadlockRecoveryExecutorInterface
{
    /**
     * @var int
     */
    private $attempts;

    /**
     * @var int
     */
    private $maxJitter;

    /**
     * @param int $attempts
     * @param int $maxJitter
     */
    public function __construct(
        int $attempts,
        int $maxJitter
    ) {
        $this->attempts = $attempts;
        $this->maxJitter = $maxJitter;
    }

    /**
     * @inheritdoc
     */
    public function execute(AdapterInterface $connection, callable $callable, array $args)
    {
        $deadlockException = null;
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            try {
                $connection->beginTransaction();
                $result = $callable(...$args);
                $connection->commit();

                return $result;
            } catch (DeadlockException|LockWaitException $e) {
                $connection->rollBack();
                $deadlockException = $e;
                if ($this->maxJitter > 0) {
                    usleep(random_int(0, $this->maxJitter));
                }
            } catch (\Throwable $e) {
                $connection->rollBack();
                throw $e;
            }
        }
        throw $deadlockException ?? new \LogicException('The number of retry attempts should must be greater than 0');
    }
}
