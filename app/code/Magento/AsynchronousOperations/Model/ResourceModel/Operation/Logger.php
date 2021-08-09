<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Exception;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Operation process logger
 */
class Logger
{
    /**
     * @var Operation
     */
    private $resource;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Operation $resource
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        Operation $resource,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    /**
     * Log operation process start time
     *
     * @param OperationInterface $operation
     * @return void
     */
    public function logStartTime(OperationInterface $operation): void
    {
        try {
            $this->resource->getConnection()->update(
                $this->resource->getMainTable(),
                [
                    'started_at' => $this->resource->getConnection()->formatDate($this->dateTime->gmtTimestamp())
                ],
                [
                    'bulk_uuid = ?' => $operation->getBulkUuid(),
                    'operation_key = ?' => $operation->getId()
                ]
            );
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
