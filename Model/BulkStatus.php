<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class BulkStatus
 * @since 2.2.0
 */
class BulkStatus implements \Magento\Framework\Bulk\BulkStatusInterface
{
    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory
     * @since 2.2.0
     */
    private $bulkCollectionFactory;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     * @since 2.2.0
     */
    private $operationCollectionFactory;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var CalculatedStatusSql
     * @since 2.2.0
     */
    private $calculatedStatusSql;

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * BulkStatus constructor.
     * @param ResourceModel\Bulk\CollectionFactory $bulkCollection
     * @param ResourceModel\Operation\CollectionFactory $operationCollection
     * @param ResourceConnection $resourceConnection
     * @param CalculatedStatusSql $calculatedStatusSql
     * @param MetadataPool $metadataPool
     * @since 2.2.0
     */
    public function __construct(
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollection,
        \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory $operationCollection,
        ResourceConnection $resourceConnection,
        CalculatedStatusSql $calculatedStatusSql,
        MetadataPool $metadataPool
    ) {
        $this->operationCollectionFactory = $operationCollection;
        $this->bulkCollectionFactory = $bulkCollection;
        $this->resourceConnection = $resourceConnection;
        $this->calculatedStatusSql = $calculatedStatusSql;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritDoc
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getOperationsCountByBulkIdAndStatus($bulkUuid, $status)
    {
        if ($status === OperationInterface::STATUS_TYPE_OPEN) {
            /**
             * Total number of operations that has been scheduled within the given bulk
             */
            $allOperationsQty = $this->getOperationCount($bulkUuid);

            /**
             * Number of operations that has been processed (i.e. operations with any status but 'open')
             */
            $allProcessedOperationsQty = (int)$this->operationCollectionFactory->create()
                ->addFieldToFilter('bulk_uuid', $bulkUuid)
                ->getSize();

            return $allOperationsQty - $allProcessedOperationsQty;
        }

        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection $collection */
        $collection = $this->operationCollectionFactory->create();
        return $collection->addFieldToFilter('bulk_uuid', $bulkUuid)
            ->addFieldToFilter('status', $status)
            ->getSize();
    }

    /**
     * @inheritDoc
     * @since 2.2.0
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
        $select->columns(['status' => $this->calculatedStatusSql->get($operationTableName)])
            ->order(new \Zend_Db_Expr('FIELD(status, ' . implode(',', $statusesArray) . ')'));
        $collection->addFieldToFilter('user_id', $userId)
            ->addOrder('start_time');

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     * @since 2.2.0
     */
    public function getBulkStatus($bulkUuid)
    {
        /**
         * Number of operations that has been processed (i.e. operations with any status but 'open')
         */
        $allProcessedOperationsQty = (int)$this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)
            ->getSize();

        if ($allProcessedOperationsQty == 0) {
            return BulkSummaryInterface::NOT_STARTED;
        }

        /**
         * Total number of operations that has been scheduled within the given bulk
         */
        $allOperationsQty = $this->getOperationCount($bulkUuid);

        /**
         * Number of operations that has not been started yet (i.e. operations with status 'open')
         */
        $allOpenOperationsQty = $allOperationsQty - $allProcessedOperationsQty;

        /**
         * Number of operations that has been completed successfully
         */
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

    /**
     * Get total number of operations that has been scheduled within the given bulk.
     *
     * @param string $bulkUuid
     * @return int
     * @since 2.2.0
     */
    private function getOperationCount($bulkUuid)
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());

        return (int)$connection->fetchOne(
            $connection->select()
                ->from($metadata->getEntityTable(), 'operation_count')
                ->where('uuid = ?', $bulkUuid)
        );
    }
}
