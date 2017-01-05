<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Bulk\BulkSummaryInterface;

/**
 * Class StatusMapper
 */
class StatusMapper
{
    /**
     * Map operation status to bulk summary status
     *
     * @param int $operationStatus
     * @return null|int
     */
    public function operationStatusToBulkSummaryStatus($operationStatus)
    {
        $statusMapping = [
            OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED => BulkSummaryInterface::FINISHED_WITH_FAILURE,
            OperationInterface::STATUS_TYPE_RETRIABLY_FAILED => BulkSummaryInterface::FINISHED_WITH_FAILURE,
            OperationInterface::STATUS_TYPE_COMPLETE => BulkSummaryInterface::FINISHED_SUCCESSFULLY,
            OperationInterface::STATUS_TYPE_OPEN => BulkSummaryInterface::IN_PROGRESS,
            BulkSummaryInterface::NOT_STARTED => BulkSummaryInterface::NOT_STARTED
        ];

        if (isset($statusMapping[$operationStatus])) {
            return $statusMapping[$operationStatus];
        }
        return null;
    }

    /**
     * Map bulk summary status to operation status
     *
     * @param int $bulkStatus
     * @return int|null
     */
    public function bulkSummaryStatusToOperationStatus($bulkStatus)
    {
        $statusMapping = [
            BulkSummaryInterface::FINISHED_WITH_FAILURE => [
                OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
                OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
            ],
            BulkSummaryInterface::FINISHED_SUCCESSFULLY => OperationInterface::STATUS_TYPE_COMPLETE,
            BulkSummaryInterface::IN_PROGRESS => OperationInterface::STATUS_TYPE_OPEN,
            BulkSummaryInterface::NOT_STARTED => BulkSummaryInterface::NOT_STARTED
        ];

        if (isset($statusMapping[$bulkStatus])) {
            return $statusMapping[$bulkStatus];
        }
        return null;
    }
}
