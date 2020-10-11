<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;

/**
 * Interface for saving multiple operations
 *
 * @api
 * @since 100.4.0
 */
interface SaveMultipleOperationsInterface
{
    /**
     * Save Operations for Bulk
     *
     * @param OperationInterface[] $operations
     * @return void
     * @since 100.4.0
     */
    public function execute(array $operations): void;
}
