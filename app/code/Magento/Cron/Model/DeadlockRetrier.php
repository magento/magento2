<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DeadlockException;

/**
 * Retrier for DB actions
 *
 * If some action throw an exceptions, try
 */
class DeadlockRetrier implements DeadlockRetrierInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(callable $callback, AdapterInterface $connection)
    {
        if ($connection->getTransactionLevel() !== 0) {
            return $callback();
        }

        for ($retries = self::MAX_RETRIES - 1; $retries > 0; $retries--) {
            try {
                return $callback();
            } catch (DeadlockException $e) {
                $this->logger->warning(sprintf("Deadlock detected in cron: %s", $e->getMessage()));
                continue;
            }
        }

        return $callback();
    }
}
