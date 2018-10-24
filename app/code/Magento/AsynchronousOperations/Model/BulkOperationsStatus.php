<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterfaceFactory as BulkStatusShortFactory;
use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterfaceFactory as BulkStatusDetailedFactory;
use Magento\AsynchronousOperations\Api\Data\OperationDetailsInterfaceFactory;
use Magento\AsynchronousOperations\Api\BulkStatusInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;

/**
 * Class BulkStatus
 */
class BulkOperationsStatus implements BulkStatusInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BulkStatusDetailedFactory
     */
    private $bulkDetailedFactory;

    /**
     * @var BulkStatusShortFactory
     */
    private $bulkShortFactory;

    /**
     * @var BulkStatus
     */
    private $bulkStatus;

    /**
     * @var CollectionFactory
     */
    private $operationCollectionFactory;

    /**
     * Init dependencies.
     *
     * @param BulkStatus $bulkStatus
     * @param CollectionFactory $operationCollection
     * @param BulkStatusDetailedFactory $bulkDetailedFactory
     * @param BulkStatusShortFactory $bulkShortFactory
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     */
    public function __construct(
        BulkStatus $bulkStatus,
        CollectionFactory $operationCollection,
        BulkStatusDetailedFactory $bulkDetailedFactory,
        BulkStatusShortFactory $bulkShortFactory,
        EntityManager $entityManager
    ) {
        $this->operationCollectionFactory = $operationCollection;
        $this->bulkStatus = $bulkStatus;
        $this->bulkDetailedFactory = $bulkDetailedFactory;
        $this->bulkShortFactory = $bulkShortFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function getFailedOperationsByBulkId($bulkUuid, $failureType = null)
    {
        return $this->bulkStatus->getFailedOperationsByBulkId($bulkUuid, $failureType);
    }

    /**
     * @inheritDoc
     */
    public function getOperationsCountByBulkIdAndStatus($bulkUuid, $status)
    {
        return $this->bulkStatus->getOperationsCountByBulkIdAndStatus($bulkUuid, $status);
    }

    /**
     * @inheritDoc
     */
    public function getBulksByUser($userId)
    {
        return $this->bulkStatus->getBulksByUser($userId);
    }

    /**
     * @inheritDoc
     */
    public function getBulkStatus($bulkUuid)
    {
        return $this->bulkStatus->getBulkStatus($bulkUuid);
    }

    /**
     * @inheritDoc
     */
    public function getBulkDetailedStatus($bulkUuid)
    {
        $bulkSummary = $this->bulkDetailedFactory->create();

        /** @var \Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterface $bulk */
        $bulk = $this->entityManager->load($bulkSummary, $bulkUuid);

        if ($bulk->getBulkId() === null) {
            throw new NoSuchEntityException(
                __(
                    'Bulk uuid %bulkUuid not exist',
                    ['bulkUuid' => $bulkUuid]
                )
            );
        }
        $operations = $this->operationCollectionFactory->create()->addFieldToFilter('bulk_uuid', $bulkUuid)->getItems();
        $bulk->setOperationsList($operations);

        return $bulk;
    }

    /**
     * @inheritDoc
     */
    public function getBulkShortStatus($bulkUuid)
    {
        $bulkSummary = $this->bulkShortFactory->create();

        /** @var \Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterface $bulk */
        $bulk = $this->entityManager->load($bulkSummary, $bulkUuid);
        if ($bulk->getBulkId() === null) {
            throw new NoSuchEntityException(
                __(
                    'Bulk uuid %bulkUuid not exist',
                    ['bulkUuid' => $bulkUuid]
                )
            );
        }
        $operations = $this->operationCollectionFactory->create()->addFieldToFilter('bulk_uuid', $bulkUuid)->getItems();
        $bulk->setOperationsList($operations);

        return $bulk;
    }
}
