<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\Operation;

use Magento\Framework\Bulk\OperationInterface;

/**
 * Class \Magento\AsynchronousOperations\Model\Operation\Details
 *
 * @since 2.2.0
 */
class Details
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $operationCache = [];

    /**
     * @var \Magento\Framework\Bulk\BulkStatusInterface
     * @since 2.2.0
     */
    private $bulkStatus;

    /**
     * Map between status codes and human readable indexes
     * @var array
     * @since 2.2.0
     */
    private $statusMap = [
        OperationInterface::STATUS_TYPE_COMPLETE => 'operations_successful',
        OperationInterface::STATUS_TYPE_RETRIABLY_FAILED => 'failed_retriable',
        OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED => 'failed_not_retriable',
    ];

    /**
     * @param \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus
     * @since 2.2.0
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
     * @since 2.2.0
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
