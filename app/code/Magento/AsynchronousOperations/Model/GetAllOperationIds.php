<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation;
use Magento\Framework\Exception\LocalizedException;

/**
 * Get all operation ids
 */
class GetAllOperationIds
{
    /**
     * @var CollectionFactory
     */
    private $operationCollectionFactory;

    /**
     * @var Operation
     */
    private $operation;

    /**
     * @param CollectionFactory $operationCollectionFactory
     * @param Operation $operation
     */
    public function __construct(
        CollectionFactory $operationCollectionFactory,
        Operation $operation
    ) {
        $this->operationCollectionFactory = $operationCollectionFactory;
        $this->operation = $operation;
    }

    /**
     * Get all operation ids
     * @param string $groupId
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $groupId): array
    {
        $select = $this->operation->getConnection()
            ->select()
            ->from($this->operation->getMainTable(), 'id')
            ->where('bulk_uuid = ?', $groupId)
        ;

        return $this->operation->getConnection()->fetchAll($select);
    }
}
