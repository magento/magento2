<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Bulk;

/**
 * Interface OperationManagementInterface
 * @api
 * @since 103.0.0
 */
interface OperationManagementInterface
{
    /**
     * Used by consumer to change status after processing operation
     *
     * @param string $bulkUuid
     * @param int $operationKey
     * @param int $status
     * @param int|null $errorCode
     * @param string|null $message property to update Result Message
     * @param string|null $data serialized data object of failed message
     * @return boolean
     * @since 103.0.0
     */
    public function changeOperationStatus($bulkUuid, $operationKey, $status, $errorCode = null, $message = null, $data = null); // @codingStandardsIgnoreLine
}
