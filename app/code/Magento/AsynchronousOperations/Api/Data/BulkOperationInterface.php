<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Class BulkOperationInterface
 * @api
 * @since 103.0.0
 */
interface BulkOperationInterface
{
    public const OPERATION_ID = 'id';

    /**
     * Get operation key
     *
     * @return int|null
     * @since 103.0.1
     */
    public function getOperationKey();

    /**
     * Set operation key
     *
     * @param int|null $operationKey
     * @since 103.0.1
     */
    public function setOperationKey(?int $operationKey);
}
