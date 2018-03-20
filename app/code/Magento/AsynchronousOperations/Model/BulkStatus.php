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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Setup\Exception;
use Magento\Framework\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\ShortOperationListInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationDetailsInterfaceFactory;

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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk
     */
    private $bulkResourceModel;

    /**
     * @var \Magento\AsynchronousOperations\Model\BulkSummaryFactory
     */
    private $bulkSummaryFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory
     */
    private $operationListInterfaceFactory;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\ShortOperationListInterfaceFactory
     */
    private $shortOperationListFactory;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterfaceFactory
     */
    private $bulkSummaryExtensionInterfaceFactory;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationDetailsInterface
     */
    private $operationDetailsFactory;

    /**
     * Init dependencies.
     *
     * @param \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollection
     * @param \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory $operationCollection
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql $calculatedStatusSql
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\AsynchronousOperations\Model\ResourceModel\Bulk $bulkResourceModel
     * @param \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory $operationListInterfaceFactory
     * @param \Magento\AsynchronousOperations\Api\Data\ShortOperationListInterfaceFactory $shortOperationListFactory
     * @param \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterfaceFactory $bulkSummaryExtensionInterfaceFactory
     * @param \Magento\AsynchronousOperations\Api\Data\OperationDetailsInterfaceFactory $operationDetails
     */
    public function __construct(
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollection,
        \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory $operationCollection,
        ResourceConnection $resourceConnection,
        CalculatedStatusSql $calculatedStatusSql,
        MetadataPool $metadataPool,
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk $bulkResourceModel,
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        EntityManager $entityManager,
        OperationListInterfaceFactory $operationListInterfaceFactory,
        ShortOperationListInterfaceFactory $shortOperationListFactory,
        BulkSummaryExtensionInterfaceFactory $bulkSummaryExtensionInterfaceFactory,
        OperationDetailsInterfaceFactory $operationDetails
    ) {
        $this->operationCollectionFactory = $operationCollection;
        $this->bulkCollectionFactory = $bulkCollection;
        $this->resourceConnection = $resourceConnection;
        $this->calculatedStatusSql = $calculatedStatusSql;
        $this->metadataPool = $metadataPool;
        $this->bulkResourceModel = $bulkResourceModel;
        $this->bulkSummaryFactory = $bulkSummaryFactory;
        $this->entityManager = $entityManager;
        $this->operationListInterfaceFactory = $operationListInterfaceFactory;
        $this->bulkSummaryExtensionInterfaceFactory = $bulkSummaryExtensionInterfaceFactory;
        $this->shortOperationListFactory = $shortOperationListFactory;
        $this->operationDetailsFactory = $operationDetails;
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
                OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
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
        if ($status === OperationInterface::STATUS_TYPE_OPEN) {
            /**
             * Total number of operations that has been scheduled within the given bulk
             */
            $allOperationsQty = $this->getOperationCount($bulkUuid);

            /**
             * Number of operations that has been processed (i.e. operations with any status but 'open')
             */
            $allProcessedOperationsQty = (int)$this->operationCollectionFactory->create()
                                                                               ->addFieldToFilter(
                                                                                   'bulk_uuid',
                                                                                   $bulkUuid
                                                                               )
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
            OperationInterface::STATUS_TYPE_COMPLETE,
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
                                                                     ->addFieldToFilter('bulk_uuid', $bulkUuid)
                                                                     ->addFieldToFilter(
                                                                         'status',
                                                                         OperationInterface::STATUS_TYPE_COMPLETE
                                                                     )
                                                                     ->getSize();

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

    /**
     * @inheritDoc
     */
    public function getBulkDetails($bulkUuid)
    {
        $bulkSummary = $this->bulkSummaryFactory->create();

        /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulk */
        $bulk = $this->entityManager->load($bulkSummary, $bulkUuid);

        if ($bulk->getBulkId() === null) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Bulk uuid %bulkUuid not exist',
                    ['bulkUuid' => $bulkUuid]
                )
            );
        }

        $operations = $this->operationCollectionFactory->create()->addFieldToFilter('bulk_uuid', $bulkUuid)->getItems();
        $operationList = $this->shortOperationListFactory->create(['items' => $operations]);

        /** @var \Magento\AsynchronousOperations\Model\Operation\Details $operationDetails */
        $operationDetails = $this->operationDetailsFactory->create(['bulkUuid' => $bulkUuid]);

        /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface $bulkExtensionAttribute */
        $bulkExtensionAttribute = $this->bulkSummaryExtensionInterfaceFactory->create();
        $bulkExtensionAttribute->setOperationsList($operationList);

        $bulkExtensionAttribute->setOperationsCount($operationDetails);
        $bulk->setExtensionAttributes($bulkExtensionAttribute);

        return $bulk;
    }
}
