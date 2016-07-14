<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Bulk;

/**
 * Interface OperationManagementInterface
 */
interface OperationManagementInterface
{
    /**
     * Used by consumer to change status after processing operation
     *
     * @param int $operationId
     * @param int $status
     * @param string $message property to update Result Message
     * @param string $data serialized data object of failed message
     * @return boolean
     */
    public function changeOperationStatus($operationId, $status, $message = null, $data = null);
}
