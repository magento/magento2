<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\Operation;

use Magento\Framework\Bulk\OperationInterface;

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
     * Map between status codes and human readable indexes
     * @var array
     */
    private $statusMap = [
        OperationInterface::STATUS_TYPE_COMPLETE => 'operations_successful',
        OperationInterface::STATUS_TYPE_RETRIABLY_FAILED => 'failed_retriable',
        OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED => 'failed_not_retriable',
    ];

    /**
     * @param \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus
     */
    public function __construct(
        \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus
    ) {
        $this->bulkStatus = $bulkStatus;
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
            'operations_total' => 0,
            'operations_successful' => 0,
            'operations_failed' => 0,
            'failed_retriable' => 0,
            'failed_not_retriable' => 0,
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

        // total is sum of successful, retriable, not retriable and open operations
        $details['operations_total'] = array_sum($details) + $this->bulkStatus->getOperationsCountByBulkIdAndStatus(
            $bulkUuid,
            OperationInterface::STATUS_TYPE_OPEN
        );
        $details['operations_failed'] = $details['failed_retriable'] + $details['failed_not_retriable'];
        $this->operationCache[$bulkUuid] = $details;
        return $details;
    }
}
