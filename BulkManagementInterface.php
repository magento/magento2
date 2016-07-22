<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

use Magento\Framework\Bulk\IdentityInterface;

/**
 * Interface BulkManagementInterface
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
