<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\SaveMultipleOperationsInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation as OperationResource;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Implementation for saving multiple operations
 */
class SaveMultipleOperations implements SaveMultipleOperationsInterface
{

    /**
     * @var OperationResource
     */
    private $operationResource;

    /**
     * BulkSummary constructor.
     *
     * @param OperationResource $operationResource
     */
    public function __construct(
        OperationResource $operationResource
    ) {
        $this->operationResource = $operationResource;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $operations): void
    {
        try {
            $operationsToInsert = array_map(function ($operation) {
                return $operation->getData();
            }, $operations);

            $connection = $this->operationResource->getConnection();
            $connection->insertMultiple(
                $this->operationResource->getTable(OperationResource::TABLE_NAME),
                $operationsToInsert
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }
}
