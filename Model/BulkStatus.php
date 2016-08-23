<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql;

/**
 * Class BulkStatus
 */
class BulkStatus implements \Magento\Framework\Bulk\BulkStatusInterface
{
    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory
     */
    private $bulkCollectionFactory;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CalculatedStatusSql
     */
    private $calculatedStatusSql;

    /**
     * BulkStatus constructor.
     * @param ResourceModel\Bulk\CollectionFactory $bulkCollection
     * @param ResourceModel\Operation\CollectionFactory $operationCollection
     * @param ResourceConnection $resourceConnection
     * @param CalculatedStatusSql $calculatedStatusSql
     */
    public function __construct(
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollection,
        \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory $operationCollection,
        ResourceConnection $resourceConnection,
        CalculatedStatusSql $calculatedStatusSql
    ) {
        $this->operationCollectionFactory = $operationCollection;
        $this->bulkCollectionFactory = $bulkCollection;
        $this->resourceConnection = $resourceConnection;
        $this->calculatedStatusSql = $calculatedStatusSql;
    }

    /**
     * @inheritDoc
     */
    public function getFailedOperationsByBulkId($bulkUuid, $failureType = null)
    {
        $failureCodes = $failureType
            ? [$failureType]
            : [
                OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
                OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED
            ];
        $operations = $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)
            ->addFieldToFilter('status', $failureCodes)
            ->getItems();
        return $operations;
    }

    /**
     * @inheritDoc
     */
    public function getOperationsCountByBulkIdAndStatus($bulkUuid, $status)
    {
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection $collection */
        $collection = $this->operationCollectionFactory->create();
        return $collection->addFieldToFilter('bulk_uuid', $bulkUuid)
            ->addFieldToFilter('status', $status)
            ->getSize();
    }

    /**
     * @inheritDoc
     */
    public function getBulksByUser($userId)
    {
        /** @var ResourceModel\Bulk\Collection $collection */
        $collection = $this->bulkCollectionFactory->create();
        $operationTableName = $this->resourceConnection->getTableName('magento_operation');
        $statusesArray = [
            OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
            OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
            BulkSummaryInterface::NOT_STARTED,
            OperationInterface::STATUS_TYPE_OPEN,
            OperationInterface::STATUS_TYPE_COMPLETE
        ];
        $select = $collection->getSelect();
        $select->columns(['status' => $this->calculatedStatusSql->execute($operationTableName)])
            ->order(new \Zend_Db_Expr('FIELD(status, ' . implode(',', $statusesArray) . ')'));
        $collection->addFieldToFilter('user_id', $userId)
            ->addOrder('start_time');

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getBulkStatus($bulkUuid)
    {
        $allOperationsQty = $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)->getSize();
        $allOpenOperationsQty = $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)->addFieldToFilter(
                'status',
                OperationInterface::STATUS_TYPE_OPEN
            )->getSize();
        if ($allOperationsQty == $allOpenOperationsQty) {
            return BulkSummaryInterface::NOT_STARTED;
        }
        $allCompleteOperationsQty = $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)->addFieldToFilter(
                'status',
                OperationInterface::STATUS_TYPE_COMPLETE
            )->getSize();
        if ($allCompleteOperationsQty == $allOperationsQty) {
            return BulkSummaryInterface::FINISHED_SUCCESSFULLY;
        }
        if ($allOpenOperationsQty > 0 && $allOpenOperationsQty !== $allOperationsQty) {
            return BulkSummaryInterface::IN_PROGRESS;
        }
        return BulkSummaryInterface::FINISHED_WITH_FAILURE;
    }
}
