<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api;

use Magento\Framework\Bulk\Api\Data\OperationInterface;
use Magento\Framework\Bulk\Api\Data\UuidInterface;

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
    public function scheduleBulk(
        \Magento\Framework\Bulk\Api\Data\UuidInterface $bulkId, 
        array $operations, 
        $description, 
        $userId = null
    );
    
    /**
     * @param UuidInterface $bulkId
     * @return boolean
     */
    public function deleteBulk(\Magento\Framework\Bulk\Api\Data\UuidInterface $bulkId);
}
