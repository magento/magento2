<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MessageQueue\BulkPublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Asynchronous Bulk Management
 */
class BulkManagement implements BulkManagementInterface
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
     * @var BulkPublisherInterface
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
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BulkManagement constructor.
     * @param EntityManager $entityManager
     * @param BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @param CollectionFactory $operationCollectionFactory
     * @param BulkPublisherInterface $publisher
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param UserContextInterface $userContext
     */
    public function __construct(
        EntityManager $entityManager,
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        CollectionFactory $operationCollectionFactory,
        BulkPublisherInterface $publisher,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        UserContextInterface $userContext
    ) {
        $this->entityManager = $entityManager;
        $this->bulkSummaryFactory= $bulkSummaryFactory;
        $this->operationCollectionFactory = $operationCollectionFactory;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->userContext = $userContext;
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
        $userType = $this->userContext->getUserType();
        if ($userType === null) {
            $userType = UserContextInterface::USER_TYPE_ADMIN;
        }
        try {
            /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulkSummary */
            $bulkSummary = $this->bulkSummaryFactory->create();
            $this->entityManager->load($bulkSummary, $bulkUuid);
            $bulkSummary->setBulkId($bulkUuid);
            $bulkSummary->setDescription($description);
            $bulkSummary->setUserId($userId);
            $bulkSummary->setUserType($userType);
            $bulkSummary->setOperationCount((int)$bulkSummary->getOperationCount() + count($operations));
            $this->entityManager->save($bulkSummary);

            $this->publishOperations($operations);

            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();
            $this->logger->critical($exception->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Retry bulk operations that failed due to given errors.
     *
     * @param string $bulkUuid target bulk UUID
     * @param array $errorCodes list of corresponding error codes
     * @return int number of affected bulk operations
     */
    public function retryBulk($bulkUuid, array $errorCodes)
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);

        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation[] $retriablyFailedOperations */
        $retriablyFailedOperations = $this->operationCollectionFactory->create()
            ->addFieldToFilter('error_code', ['in' => $errorCodes])
            ->addFieldToFilter('bulk_uuid', ['eq' => $bulkUuid])
            ->getItems();

        // remove corresponding operations from database (i.e. move them to 'open' status)
        $connection->beginTransaction();
        try {
            $operationIds = [];
            $currentBatchSize = 0;
            $maxBatchSize = 10000;
            /** @var OperationInterface $operation */
            foreach ($retriablyFailedOperations as $operation) {
                if ($currentBatchSize === $maxBatchSize) {
                    $whereCondition = $connection->quoteInto('operation_key IN (?)', $operationIds)
                        . " AND "
                        . $connection->quoteInto('bulk_uuid = ?', $bulkUuid);
                    $connection->delete(
                        $this->resourceConnection->getTableName('magento_operation'),
                        $whereCondition
                    );
                    $operationIds = [];
                    $currentBatchSize = 0;
                }
                $currentBatchSize++;
                $operationIds[] = $operation->getId();
            }
            // remove operations from the last batch
            if (!empty($operationIds)) {
                $whereCondition = $connection->quoteInto('operation_key IN (?)', $operationIds)
                    . " AND "
                    . $connection->quoteInto('bulk_uuid = ?', $bulkUuid);
                $connection->delete(
                    $this->resourceConnection->getTableName('magento_operation'),
                    $whereCondition
                );
            }

            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();
            $this->logger->critical($exception->getMessage());
            return 0;
        }
        $this->publishOperations($retriablyFailedOperations);

        return count($retriablyFailedOperations);
    }

    /**
     * Publish list of operations to the corresponding message queues.
     *
     * @param array $operations
     * @return void
     */
    private function publishOperations(array $operations)
    {
        $operationsByTopics = [];
        foreach ($operations as $operation) {
            $operationsByTopics[$operation->getTopicName()][] = $operation;
        }
        foreach ($operationsByTopics as $topicName => $operations) {
            $this->publisher->publish($topicName, $operations);
        }
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
