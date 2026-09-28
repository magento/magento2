<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkManagementInterface
 * @api
 * @since 103.0.0
 */
interface BulkManagementInterface
{
    /**
     * Schedule new bulk
     *
     * @param string $bulkUuid
     * @param OperationInterface[] $operations
     * @param string $description
     * @param int $userId
     * @return boolean
     * @since 103.0.0
     */
    public function scheduleBulk($bulkUuid, array $operations, $description, $userId = null);

    /**
     * Delete bulk
     *
     * @param string $bulkId
     * @return boolean
     * @since 103.0.0
     */
    public function deleteBulk($bulkId);
}
