<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bulk\Api;

use Magento\Bulk\Api\Data\OperationInterface;

/**
 * Interface BulkManagementInterface
 */
interface BulkManagementInterface
{
    /**
     * @param UuidInterface $bulkId
     * @param OperationInterface[] $operations
     * @param string $description
     * @param int $userId
     * @return boolean
     */
    public function scheduleBulk(UuidInterface $bulkId, array $operations, $description, $userId = null);
    
    /**
     * @param UuidInterface $bulkId
     * @return boolean
     */
    public function deleteBulk(UuidInterface $bulkId);
}
