<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;

/**
 * Interface for load all operations from a bulk
 */
interface GetAllOperationsInterface
{
    /**
     * @param string $groupId
     * @return OperationInterface[]
     */
    public function execute(string $groupId): array;
}
