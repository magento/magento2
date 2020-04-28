<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkStatusInterface
 * @api
 * @since 102.0.4
 */
interface BulkStatusInterface
{
    /**
     * Get failed operations by bulk uuid
     *
     * @param string $bulkUuid
     * @param int|null $failureType
     * @return \Magento\Framework\Bulk\OperationInterface[]
     * @since 102.0.4
     */
    public function getFailedOperationsByBulkId($bulkUuid, $failureType = null);

    /**
     * Get operations count by bulk uuid and status.
     *
     * @param string $bulkUuid
     * @param int $status
     * @return int
     * @since 102.0.4
     */
    public function getOperationsCountByBulkIdAndStatus($bulkUuid, $status);

    /**
     * Get all bulks created by user
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     * @since 102.0.4
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
     * @since 102.0.4
     */
    public function getBulkStatus($bulkUuid);
}
