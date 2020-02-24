<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\BulkStatusRepositoryInterface;
use Magento\AsynchronousOperations\Api\BulkSummaryRepositoryInterface;
use Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterface;
use Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterfaceFactory as BulkStatusShortFactory;
use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterface;
use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterfaceFactory as BulkStatusDetailedFactory;
use Magento\AsynchronousOperations\Model\Repository\Registry as BulkRepositoryRegistry;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Bulk\BulkSummaryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Bulk\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface as AsynchronousOperationInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface as AsynchronousBulkSummaryInterface;

/**
 * Interface BulkStatusInterface.
 *
 * Bulk summary data with list of operations items short data.
 *
 * @api
 */
class BulkStatusRepository implements BulkStatusRepositoryInterface
{
    /**
     * @var BulkRepositoryRegistry
     */
    private $bulkRepositoryRegistry;

    /**
     * @var BulkSummaryRepositoryInterface
     */
    private $bulkSummaryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var BulkStatusDetailedFactory
     */
    private $bulkDetailedFactory;

    /**
     * @var BulkStatusShortFactory
     */
    private $bulkShortFactory;

    /**
     * BulkStatusRepository constructor.
     *
     * @param BulkRepositoryRegistry $bulkRepositoryRegistry
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BulkStatusDetailedFactory $bulkDetailedFactory
     * @param BulkStatusShortFactory $bulkShortFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        BulkRepositoryRegistry $bulkRepositoryRegistry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BulkStatusDetailedFactory $bulkDetailedFactory,
        BulkStatusShortFactory $bulkShortFactory
    ) {
        $this->bulkRepositoryRegistry = $bulkRepositoryRegistry;
        $this->bulkSummaryRepository = $this->bulkRepositoryRegistry->getRepository();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->bulkDetailedFactory = $bulkDetailedFactory;
        $this->bulkShortFactory = $bulkShortFactory;
    }

    /**
     * Get Bulk summary data with list of operations items full data.
     *
     * @param string $bulkUuid
     * @return DetailedBulkOperationsStatusInterface
     * @throws NoSuchEntityException
     */
    public function getBulkDetailedStatus(string $bulkUuid): DetailedBulkOperationsStatusInterface
    {
        $bulkSummary = $this->bulkSummaryRepository->getBulkByUuid($bulkUuid);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OperationInterface::BULK_ID, $bulkUuid, "eq")
            ->create();
        $bulkOperations = $this->bulkSummaryRepository
            ->getOperationsList($searchCriteria)
            ->getItems();

        $bulkSummaryDetailed = $this->bulkDetailedFactory->create($bulkSummary->getData());
        $bulkSummaryDetailed->setOperationsList($bulkOperations);

        return $bulkSummaryDetailed;
    }

    /**
     * Get Bulk summary data with list of operations items short data.
     *
     * @param string $bulkUuid
     * @return BulkOperationsStatusInterface
     * @throws NoSuchEntityException
     */
    public function getBulkShortStatus(string $bulkUuid): BulkOperationsStatusInterface
    {
        $bulkSummary = $this->bulkSummaryRepository->getBulkByUuid($bulkUuid);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OperationInterface::BULK_ID, $bulkUuid, "eq")
            ->create();
        $bulkOperations = $this->bulkSummaryRepository
            ->getOperationsList($searchCriteria)
            ->getItems();

        $bulkSummaryDetailed = $this->bulkShortFactory->create($bulkSummary->getData());
        $bulkSummaryDetailed->setOperationsList($bulkOperations);

        return $bulkSummaryDetailed;
    }

    /**
     * Get failed operations by bulk uuid
     *
     * @param string $bulkUuid
     * @param int|null $failureType
     * @return \Magento\Framework\Bulk\OperationInterface[]
     * @since 100.2.0
     */
    public function getFailedOperationsByBulkId($bulkUuid, $failureType = null)
    {
        $failureCodes = $failureType
            ? [$failureType]
            : [
                AsynchronousOperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
                OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED
            ];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OperationInterface::BULK_ID, $bulkUuid, "eq")
            ->addFilter(OperationInterface::STATUS, $failureCodes, "in")
            ->create();
        $operations = $this->bulkSummaryRepository
            ->getOperationsList($searchCriteria)
            ->getItems();

        return $operations;
    }

    /**
     * Get operations count by bulk uuid and status.
     *
     * @param string $bulkUuid
     * @param int $status
     * @return int
     * @since 100.2.0
     */
    public function getOperationsCountByBulkIdAndStatus($bulkUuid, $status)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OperationInterface::BULK_ID, $bulkUuid, "eq")
            ->addFilter(OperationInterface::STATUS, $status, "eq")
            ->create();
        return $this->bulkSummaryRepository
            ->getOperationsList($searchCriteria)
            ->getTotalCount();
    }

    /**
     * Get all bulks created by user
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     * @since 100.2.0
     */
    public function getBulksByUser($userId)
    {
        /** @var ResourceModel\Bulk\Collection $collection */
        $collection = $this->bulkCollectionFactory->create();
        $operationTableName = $this->resourceConnection->getTableName('magento_operation');
        $statusesArray = [
            AsynchronousOperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
            OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
            AsynchronousBulkSummaryInterface::NOT_STARTED,
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
     * Computational status based on statuses of belonging operations
     *
     * FINISHED_SUCCESFULLY - all operations are handled succesfully
     * FINISHED_WITH_FAILURE - some operations are handled with failure
     *
     * @param string $bulkUuid
     * @return int NOT_STARTED | IN_PROGRESS | FINISHED_SUCCESFULLY | FINISHED_WITH_FAILURE
     * @since 100.2.0
     */
    public function getBulkStatus($bulkUuid)
    {
    }
}
