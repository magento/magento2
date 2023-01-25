<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;

/**
 * Repository interface to create operation
 */
interface OperationRepositoryInterface
{
    /**
     * Create operation by topic, parameters and group ID
     *
     * @param string $topicName
     * @param array $entityParams
     * format: array(
     *     '<arg1-name>' => '<arg1-value>',
     *     '<arg2-name>' => '<arg2-value>',
     * )
     * @param string $groupId
     * @param int $operationId
     * @return OperationInterface
     */
    public function create($topicName, $entityParams, $groupId, $operationId): OperationInterface;
}
