<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\Repository;

use Psr\Log\LoggerInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterfaceFactory as SearchResultFactory;
use Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation as OperationResource;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;

/**
 * Repository class for @see \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
 */
class Operation implements \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory
     */
    private $operationExtensionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OperationResource
     */
    private $operationResource;

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * Operation constructor.
     * @param EntityManager $entityManager
     * @param CollectionFactory $collectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param OperationExtensionInterfaceFactory $operationExtension
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Psr\Log\LoggerInterface $logger
     * @param OperationResource $operationResource
     * @param OperationInterfaceFactory $operationFactory
     */
    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        SearchResultFactory $searchResultFactory,
        JoinProcessorInterface $joinProcessor,
        OperationExtensionInterfaceFactory $operationExtension,
        CollectionProcessorInterface $collectionProcessor,
        LoggerInterface $logger,
        OperationResource $operationResource,
        OperationInterfaceFactory $operationFactory
    ) {
        $this->entityManager = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->joinProcessor = $joinProcessor;
        $this->operationExtensionFactory = $operationExtension;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
        $this->collectionProcessor = $collectionProcessor;
        $this->operationResource = $operationResource;
        $this->operationFactory = $operationFactory;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();

        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection, OperationInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getItems());

        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity)
    {
        try {
            $this->operationResource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getByUuid($bulkUuid)
    {
        /** @var OperationInterface $operation */
        $operation = $this->operationFactory->create();
        $this->operationResource->load($operation, $bulkUuid, 'bulk_uuid');
        if (!$operation->getId()) {
            throw new NoSuchEntityException(__('The Operation with the "%1" UUID doesn\'t exist.', $bulkUuid));
        }
        return $operation;
    }
}
