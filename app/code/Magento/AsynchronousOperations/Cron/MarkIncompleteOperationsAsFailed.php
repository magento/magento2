<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Cron;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Marks incomplete operations as failed
 */
class MarkIncompleteOperationsAsFailed
{
    /**
     * Default message maximum processing time. Default to 12h
     */
    private const DEFAULT_MESSAGE_MAX_PROCESSING_TIME = 43200;

    /**
     * Default error code
     */
    private const ERROR_CODE = 0;

    /**
     * Default error message
     */
    private const ERROR_MESSAGE = 'Unknown Error';

    /**
     * @var Operation
     */
    private $resource;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var int
     */
    private $messageMaxProcessingTime;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var int
     */
    private $failedStatus;

    /**
     * @param Operation $resource
     * @param DateTime $dateTime
     * @param int $messageMaxProcessingTime
     * @param int $failedStatus
     * @param int $errorCode
     * @param string $errorMessage
     */
    public function __construct(
        Operation $resource,
        DateTime $dateTime,
        int $messageMaxProcessingTime = self::DEFAULT_MESSAGE_MAX_PROCESSING_TIME,
        int $failedStatus = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
        int $errorCode = self::ERROR_CODE,
        string $errorMessage = self::ERROR_MESSAGE
    ) {
        $this->resource = $resource;
        $this->dateTime = $dateTime;
        $this->messageMaxProcessingTime = $messageMaxProcessingTime;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
        $this->failedStatus = $failedStatus;
    }

    /**
     * Marks incomplete operations as failed
     */
    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $now = $this->dateTime->gmtTimestamp();
        $idField = $this->resource->getIdFieldName();
        $select = $connection->select()
            ->from($this->resource->getMainTable(), [$idField])
            ->where('status = ?', OperationInterface::STATUS_TYPE_OPEN)
            ->where('started_at <= ?', $connection->formatDate($now - $this->messageMaxProcessingTime));

        foreach ($connection->fetchCol($select) as $id) {
            $connection->update(
                $this->resource->getMainTable(),
                [
                    'status' => $this->failedStatus,
                    'result_message' => $this->errorMessage,
                    'error_code' => $this->errorCode,
                ],
                [
                    "$idField = ?" => (int) $id
                ]
            );
        }
    }
}
