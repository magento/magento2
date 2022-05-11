<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

/**
 * Class BulkOperationInterface
 * @api
 * @since 104.0.0
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
