<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;

/**
 * Class BulkManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkManagement implements \Magento\Framework\Bulk\BulkManagementInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BulkSummaryInterfaceFactory
     */
    private $bulkSummaryFactory;

    /**
     * @var CollectionFactory
     */
    private $operationCollectionFactory;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * BulkManagement constructor.
     * @param EntityManager $entityManager
     * @param BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @param CollectionFactory $operationCollectionFactory
     * @param PublisherInterface $publisher
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        CollectionFactory $operationCollectionFactory,
        PublisherInterface $publisher,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->bulkSummaryFactory= $bulkSummaryFactory;
        $this->operationCollectionFactory = $operationCollectionFactory;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function scheduleBulk($bulkUuid, array $operations, $description, $userId = null)
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        // save bulk summary and related operations
        $connection->beginTransaction();
        try {
            /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulkSummary */
            $bulkSummary = $this->bulkSummaryFactory->create();
            $bulkSummary->setBulkId($bulkUuid);
            $bulkSummary->setDescription($description);
            $bulkSummary->setUserId($userId);

            $this->entityManager->save($bulkSummary);
            /** @var OperationInterface $operation */
            foreach ($operations as $operation) {
                $this->entityManager->save($operation);
            }

            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();
            $this->logger->critical($exception->getMessage());
            return false;
        }

        // publish operation to message queue
        foreach ($operations as $operation) {
            $this->publisher->publish($operation->getTopicName(), $operation);
        }
        return true;
    }

    /**
     * Retry bulk operations that failed due to given errors
     *
     * @param string $bulkUuid target bulk UUID
     * @param array $errorCodes list of corresponding error codes
     * @return int number of affected bulk operations
     */
    public function retryBulk($bulkUuid, array $errorCodes)
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());

        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation[] $operations */
        $operations = $this->operationCollectionFactory->create()
            ->addFieldToFilter('error_code', ['in' => $errorCodes])
            ->addFieldToFilter('bulk_uuid', ['eq' => $bulkUuid])
            ->getItems();

        // save related operations
        $connection->beginTransaction();
        try {
            /** @var OperationInterface $operation */
            foreach ($operations as $operation) {
                $operation->setStatus(Operation::STATUS_TYPE_OPEN);
                $operation->setErrorCode(null);
                $operation->setResultMessage(null);
                $this->entityManager->save($operation);
            }

            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();
            $this->logger->critical($exception->getMessage());
            return 0;
        }

        // publish operation to message queue
        foreach ($operations as $operation) {
            $this->publisher->publish($operation->getTopicName(), $operation);
        }
        return count($operations);
    }

    /**
     * @inheritDoc
     */
    public function deleteBulk($bulkId)
    {
        return $this->entityManager->delete(
            $this->entityManager->load(
                $this->bulkSummaryFactory->create(),
                $bulkId
            )
        );
    }
}
