<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkStatusInterface
 * @api
 * @since 100.2.0
 */
interface BulkStatusInterface
{
    /**
     * Get failed operations by bulk uuid
     *
     * @param string $bulkUuid
     * @param int|null $failureType
     * @return \Magento\Framework\Bulk\OperationInterface[]
     * @since 100.2.0
     */
    public function getFailedOperationsByBulkId($bulkUuid, $failureType = null);

    /**
     * Get operations count by bulk uuid and status.
     *
     * @param string $bulkUuid
     * @param int $status
     * @return int
     * @since 100.2.0
     */
    public function getOperationsCountByBulkIdAndStatus($bulkUuid, $status);

    /**
     * Get all bulks created by user
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     * @since 100.2.0
     */
    public function getBulksByUser($userId);

    /**
     * Computational status based on statuses of belonging operations
     *
     * FINISHED_SUCCESFULLY - all operations are handled succesfully
     * FINISHED_WITH_FAILURE - some operations are handled with failure
     *
     * @param string $bulkUuid
     * @return int NOT_STARTED | IN_PROGRESS | FINISHED_SUCCESFULLY | FINISHED_WITH_FAILURE
     * @since 100.2.0
     */
    public function getBulkStatus($bulkUuid);
}
