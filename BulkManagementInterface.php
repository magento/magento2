<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkManagementInterface
 * @api
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
     */
    public function scheduleBulk($bulkUuid, array $operations, $description, $userId = null);
    
    /**
     * Delete bulk
     *
     * @param string $bulkId
     * @return boolean
     */
    public function deleteBulk($bulkId);
}
