<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkStatusInterface
 */
interface BulkStatusInterface
{
    /**
     * @param string $bulkUuid
     * @param int|null $failureType
     * @return \Magento\BulkOperations\Api\Data\OperationInterface[]
     */
    public function getFailedOperationsByBulkId($bulkUuid, $failureType = null);

    /**
     * @param int $userId
     * @return BulkSummaryInterface[]
     */
    public function getBulksByUser($userId);

    /**
     * Computational status based on statuses of belonging operations
     *
     * @param string $bulkUuid
     * @return int NOT_STARTED | IN_PROGRESS_SUCCESS | IN_PROGRESS_FAILED | FINISHED_SUCCESFULLY | FINISHED_WITH_FAILURE
     * FINISHED_SUCCESFULLY - all operations are handled succesfully
     * FINISHED_WITH_FAILURE - some operations are handled with failure
     */
    public function getBulkStatus($bulkUuid);
}
