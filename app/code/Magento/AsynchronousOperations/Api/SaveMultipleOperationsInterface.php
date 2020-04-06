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
 */
interface SaveMultipleOperationsInterface
{
    /**
     * Save Operations for Bulk
     *
     * @param OperationInterface[] $operations
     * @return void
     */
    public function execute(array $operations): void;
}
