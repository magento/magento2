<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql;
use Magento\Framework\App\ResourceConnection;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection;
use Magento\Framework\Bulk\GetBulksByUserAndTypeInterface;

/**
 * @inheritDoc
 */
class GetBulksByUserAndType implements GetBulksByUserAndTypeInterface
{
    /**
     * @var array
     */
    private $statusesArray = [
        OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
        OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
        BulkSummaryInterface::NOT_STARTED,
        OperationInterface::STATUS_TYPE_OPEN,
        OperationInterface::STATUS_TYPE_COMPLETE
    ];

    /**
     * @var CollectionFactory
     */
    private $bulkCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CalculatedStatusSql
     */
    private $calculatedStatusSql;

    /**
     * @param ResourceConnection $resourceConnection
     * @param CalculatedStatusSql $calculatedStatusSql
     * @param CollectionFactory $bulkCollection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CalculatedStatusSql $calculatedStatusSql,
        CollectionFactory $bulkCollection
    ) {
        $this->bulkCollectionFactory = $bulkCollection;
        $this->resourceConnection = $resourceConnection;
        $this->calculatedStatusSql = $calculatedStatusSql;
    }

    /**
     * @inheritDoc
     */
    public function execute($userId, $userTypeId): array
    {
        /** @var Collection $collection */
        $collection = $this->bulkCollectionFactory->create();
        $operationTableName = $this->resourceConnection->getTableName('magento_operation');

        $select = $collection->getSelect();
        $select->columns(['status' => $this->calculatedStatusSql->get($operationTableName)])
            ->order(new \Zend_Db_Expr('FIELD(status, ' . implode(',', $this->statusesArray) . ')'));
        $collection->addFieldToFilter('user_id', $userId)
            ->addFieldToFilter('user_type', $userTypeId)
            ->addOrder('start_time');

        return $collection->getItems();
    }
}
