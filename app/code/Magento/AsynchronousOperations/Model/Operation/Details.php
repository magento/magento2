<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\Operation;

use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Bulk\BulkStatusInterface;

class Details
{
    /**
     * @var array
     */
    private $operationCache = [];

    /**
     * @var \Magento\Framework\Bulk\BulkStatusInterface
     */
    private $bulkStatus;

    /**
     * @var null
     */
    private $bulkUuid;

    /**
     * Map between status codes and human readable indexes
     *
     * @var array
     */
    private $statusMap = [
        OperationInterface::STATUS_TYPE_COMPLETE             => 'operations_successful',
        OperationInterface::STATUS_TYPE_RETRIABLY_FAILED     => 'failed_retriable',
        OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED => 'failed_not_retriable',
        OperationInterface::STATUS_TYPE_OPEN                 => 'open',
        OperationInterface::STATUS_TYPE_REJECTED             => 'rejected',
    ];

    /**
     * Init dependencies.
     *
     * @param \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus
     * @param null $bulkUuid
     */
    public function __construct(
        BulkStatusInterface $bulkStatus,
        $bulkUuid = null
    ) {
        $this->bulkStatus = $bulkStatus;
        $this->bulkUuid = $bulkUuid;
    }

    /**
     * Collect operations statistics for the bulk
     *
     * @param string $bulkUuid
     * @return array
     */
    public function getDetails($bulkUuid)
    {
        $details = [
            'operations_total'      => 0,
            'operations_successful' => 0,
            'operations_failed'     => 0,
            'failed_retriable'      => 0,
            'failed_not_retriable'  => 0,
            'rejected'              => 0,
        ];

        if (array_key_exists($bulkUuid, $this->operationCache)) {
            return $this->operationCache[$bulkUuid];
        }

        foreach ($this->statusMap as $statusCode => $readableKey) {
            $details[$readableKey] = $this->bulkStatus->getOperationsCountByBulkIdAndStatus(
                $bulkUuid,
                $statusCode
            );
        }

        $details['operations_total'] = array_sum($details);
        $details['operations_failed'] = $details['failed_retriable'] + $details['failed_not_retriable'];
        $this->operationCache[$bulkUuid] = $details;

        return $details;
    }

    /**
     * @inheritDoc
     */
    public function getOperationsTotal()
    {
        $this->getDetails($this->bulkUuid);

        return $this->operationCache[$this->bulkUuid]['operations_total'];
    }

    /**
     * @inheritDoc
     */
    public function getOpen()
    {
        $this->getDetails($this->bulkUuid);
        $statusKey = $this->statusMap[OperationInterface::STATUS_TYPE_OPEN];

        return $this->operationCache[$this->bulkUuid][$statusKey];
    }

    /**
     * @inheritDoc
     */
    public function getOperationsSuccessful()
    {
        $this->getDetails($this->bulkUuid);
        $statusKey = $this->statusMap[OperationInterface::STATUS_TYPE_COMPLETE];

        return $this->operationCache[$this->bulkUuid][$statusKey];
    }

    /**
     * @inheritDoc
     */
    public function getTotalFailed()
    {
        $this->getDetails($this->bulkUuid);

        return $this->operationCache[$this->bulkUuid]['operations_failed'];
    }

    /**
     * @inheritDoc
     */
    public function getFailedNotRetriable()
    {
        $statusKey = $this->statusMap[OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED];

        return $this->operationCache[$this->bulkUuid][$statusKey];
    }

    /**
     * @inheritDoc
     */
    public function getFailedRetriable()
    {
        $this->getDetails($this->bulkUuid);
        $statusKey = $this->statusMap[OperationInterface::STATUS_TYPE_RETRIABLY_FAILED];

        return $this->operationCache[$this->bulkUuid][$statusKey];
    }

    /**
     * @inheritDoc
     */
    public function getRejected()
    {
        $this->getDetails($this->bulkUuid);
        $statusKey = $this->statusMap[OperationInterface::STATUS_TYPE_REJECTED];

        return $this->operationCache[$this->bulkUuid][$statusKey];
    }
}
