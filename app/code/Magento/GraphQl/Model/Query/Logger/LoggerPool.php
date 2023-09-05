<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Query\Logger;

use InvalidArgumentException;

/**
 * GraphQl logger pool
 */
class LoggerPool implements LoggerInterface
{
    /**
     * @var LoggerInterface[]
     */
    private $loggers;

    /**
     * @param LoggerInterface[] $loggers
     */
    public function __construct(
        $loggers = []
    ) {
        $this->loggers = $loggers;
    }

    /**
     * Logs details of GraphQl query
     *
     * @param array $queryDetails
     * @return void
     */
    public function execute(
        array $queryDetails
    ) {
        foreach ($this->loggers as $logger) {
            $logger->execute($queryDetails);
        }
    }
}
