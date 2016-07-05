<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api;

use Magento\Framework\Bulk\Api\Data\UuidInterface;
/**
 * Interface BulkStatusInterface
 */
interface BulkStatusInterface
{
    /**
     * @param UuidInterface $bulkId
     * @param int|null $failureType
     * @return \Magento\Framework\Bulk\Api\Data\OperationInterface[]
     */
    public function getFailedOperationsByBulkId(
        \Magento\Framework\Bulk\Api\Data\UuidInterface $bulkId,
        $failureType = null
    );

    /**
     * @param int $userId
     * @return UuidInterface[]
     */
    public function getBulksByUser($userId);

    /**
     * Computational status based on statuses of belonging operations
     *
     * @param \Magento\Framework\Bulk\Api\Data\UuidInterface $bulkId
     * @return int NOT_STARTED | IN_PROGRESS_SUCCESS | IN_PROGRESS_FAILED | FINISHED_SUCCESFULLY | FINISHED_WITH_FAILURE
     * FINISHED_SUCCESFULLY - all operations are handled succesfully
     * FINISHED_WITH_FAILURE - some operations are handled with failure
     */
    public function getBulkStatus(\Magento\Framework\Bulk\Api\Data\UuidInterface $bulkId);
}
