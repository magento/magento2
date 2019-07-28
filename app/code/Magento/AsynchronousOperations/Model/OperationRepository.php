<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterfaceFactory as SearchResultFactory;
use Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository class for @see \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
 */
class OperationRepository implements \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
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
     * OperationRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param CollectionFactory $collectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param OperationExtensionInterfaceFactory $operationExtension
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        SearchResultFactory $searchResultFactory,
        JoinProcessorInterface $joinProcessor,
        OperationExtensionInterfaceFactory $operationExtension,
        CollectionProcessorInterface $collectionProcessor,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->joinProcessor = $joinProcessor;
        $this->operationExtensionFactory = $operationExtension;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
        $this->collectionProcessor = $collectionProcessor;
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
        $this->joinProcessor->process($collection, \Magento\AsynchronousOperations\Api\Data\OperationInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getItems());

        return $searchResult;
    }
}
