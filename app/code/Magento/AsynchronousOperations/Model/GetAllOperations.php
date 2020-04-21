<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\GetAllOperationsInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;

class GetAllOperations implements GetAllOperationsInterface
{
    /**
     * @var CollectionFactory
     */
    private $operationCollectionFactory;

    /**
     * BulkSummary constructor.
     *
     * @param CollectionFactory $operationCollectionFactory
     */
    public function __construct(
        CollectionFactory $operationCollectionFactory
    ) {
        $this->operationCollectionFactory = $operationCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $groupId): array
    {
        return $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', ['eq' => $groupId])
            ->getItems();
    }
}
