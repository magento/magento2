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
class LoggerPool
{
    /**
     * @var LoggerInterface[]
     */
    private $loggers;

    /**
     * @var LoggerInterfaceFactory
     */
    private $loggerFactory;

    /**
     * @param LoggerInterfaceFactory $loggerFactory
     * @param LoggerInterface[] $loggers
     */
    public function __construct(
        LoggerInterfaceFactory $loggerFactory,
        $loggers = []
    ) {
        $this->loggerFactory = $loggerFactory;
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
        foreach ($this->loggers as $loggerClass) {
            $logger = $this->loggerFactory->create(
                $loggerClass,
                [
                    'queryDetails' => $queryDetails,
                ]
            );
            if (!$logger instanceof LoggerInterface) {
                throw new InvalidArgumentException(__(
                    'Type %1 is not an instance of %2',
                    get_class($logger),
                    LoggerInterface::class
                ));
            }
            $logger->execute();
        }
    }

}
