<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api;

use Magento\Framework\Bulk\Api\Data\OperationInterface;
use Magento\Framework\Bulk\Api\Data\IdentityInterface;

/**
 * Interface BulkManagementInterface
 */
interface BulkManagementInterface
{
    /**
     * @param string $bulkUuid
     * @param OperationInterface[] $operations
     * @param string $description
     * @param int $userId
     * @return boolean
     */
    public function scheduleBulk($bulkUuid, array $operations, $description, $userId = null);
    
    /**
     * @param string $bulkId
     * @return boolean
     */
    public function deleteBulk($bulkId);
}
