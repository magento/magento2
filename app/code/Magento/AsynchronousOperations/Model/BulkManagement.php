<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\BulkSummaryRepositoryInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\Repository\Registry as BulkRepositoryRegistry;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MessageQueue\BulkPublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BulkManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkManagement implements BulkManagementInterface
{
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
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var BulkSummaryRepositoryInterface
     */
    private $bulkSummaryRepository;

    /**
     * @var BulkRepositoryRegistry
     */
    private $bulkRepositoryRegistry;

    /**
     * BulkManagement constructor.
     * @param BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @param CollectionFactory $operationCollectionFactory
     * @param BulkPublisherInterface $publisher
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param \Psr\Log\LoggerInterface $logger
     * @param BulkRepositoryRegistry $bulkRepositoryRegistry
     * @param UserContextInterface|null $userContext
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @throws \Exception
     */
    public function __construct(
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        CollectionFactory $operationCollectionFactory,
        BulkPublisherInterface $publisher,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        BulkRepositoryRegistry $bulkRepositoryRegistry,
        UserContextInterface $userContext = null,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->bulkSummaryFactory = $bulkSummaryFactory;
        $this->operationCollectionFactory = $operationCollectionFactory;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->bulkRepositoryRegistry = $bulkRepositoryRegistry;
        $this->userContext = $userContext ?: ObjectManager::getInstance()->get(UserContextInterface::class);
        $this->bulkSummaryRepository = $this->bulkRepositoryRegistry->getRepository();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function scheduleBulk($bulkUuid, array $operations, $description, $userId = null)
    {
        $userType = $this->userContext->getUserType();
        if ($userType === null) {
            $userType = UserContextInterface::USER_TYPE_ADMIN;
        }
        try {
            $bulkSummary = $this->bulkSummaryFactory->create();
            $bulkSummary->setBulkId($bulkUuid);
            $bulkSummary->setDescription($description);
            $bulkSummary->setUserId($userId);
            $bulkSummary->setUserType($userType);
            $bulkSummary->setOperationCount((int)$bulkSummary->getOperationCount() + count($operations));
            $this->bulkSummaryRepository->saveBulk($bulkSummary);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }

        $this->publishOperations($operations);
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

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter("error_code", $errorCodes, "in")
            ->addFilter("bulk_uuid", $bulkUuid, "eq")
            ->create();

        $retriablyFailedOperations = $this->bulkSummaryRepository
            ->getOperationsList($searchCriteria)
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
                    $this->bulkSummaryRepository->deleteOperationsById($operationIds);
                    $operationIds = [];
                    $currentBatchSize = 0;
                }
                $currentBatchSize++;
                $operationIds[] = $operation->getId();
                // Rescheduled operations must be put in queue in 'open' state (i.e. without ID)
                $operation->setId(null);
            }
            // remove operations from the last batch
            if (!empty($operationIds)) {
                $this->bulkSummaryRepository->deleteOperationsById($operationIds);
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

}
