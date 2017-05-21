<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Inventory\Model\Resource\Source as ResourceSource;
use Magento\Inventory\Model\Resource\Source\CollectionFactory;
use \Psr\Log\LoggerInterface;

/**
 * Class SourceRepository
 *
 * Provides implementation of CQRS for sourcemodel
 *
 */
class SourceRepository implements SourceRepositoryInterface
{
    /**
     * @var ResourceSource
     */
    private $resource;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SourceSearchResultsInterfaceFactory
     */
    private $sourceSearchResultsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceRepository constructor.
     * @param ResourceSource $resource
     * @param SourceInterfaceFactory $sourceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory
     */
    public function __construct(
        ResourceSource $resource,
        SourceInterfaceFactory $sourceFactory,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->sourceFactory = $sourceFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->sourceSearchResultsFactory = $sourceSearchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(SourceInterface $source)
    {
        try {
            $this->resource->save($source);
            return $source->getSourceId();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new CouldNotSaveException(__('Could not save source'));
        }
    }

    /**
     * @inheritdoc
     */
    public function get($sourceId)
    {
        /** @var SourceInterface|AbstractModel $model */
        $source = $this->sourceFactory->create();
        $this->resource->load($source, $sourceId, SourceInterface::SOURCE_ID);

        if (!$source->getSourceId()) {
            throw NoSuchEntityException::singleField(SourceInterface::SOURCE_ID, $sourceId);
        }

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var \Magento\Inventory\Model\Resource\Source\Collection $collection */
        $collection = $this->collectionFactory->create();

        // if there is a searchCriteria defined, use it to add its creterias to the collection
        if (!is_null($searchCriteria)) {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        /** @var SourceSearchResultsInterface $searchResults */
        $searchResults = $this->sourceSearchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
