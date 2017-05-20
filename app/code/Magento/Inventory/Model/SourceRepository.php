<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Inventory\Model\Resource\Source as ResourceSource;
use Magento\Inventory\Model\Resource\Source\CollectionFactory;
use Magento\Inventory\Model\SourceFactory;

/**
 * Class SourceRepository
 * @package Magento\Inventory\Model
 */
class SourceRepository implements SourceRepositoryInterface
{
    /**
     * @var ResourceSource
     */
    protected $resource;

    /**
     * @var SourceFactory
     */
    protected $sourceFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SourceSearchResultsFactory
     */
    protected $sourceSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * SourceRepository constructor.
     * @param ResourceSource $resource
     * @param SourceFactory $sourceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param SourceSearchResultsFactory $sourceSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ResourceSource $resource,
        SourceFactory $sourceFactory,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        SourceSearchResultsFactory $sourceSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->sourceFactory = $sourceFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->sourceSearchResultsFactory = $sourceSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Save Source data.
     *
     * @param SourceInterface $source
     * @return SourceInterface
     *
     * @throws CouldNotSaveException
     */
    public function save(SourceInterface $source)
    {
        try {
            $this->resource->save($source);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $source;
    }

    /**
     * Load Source data by given sourceId.
     *
     * @param int $sourceId
     * @return SourceInterface
     */
    public function get($sourceId)
    {
        /** @var SourceInterface|AbstractModel $model */
        $model = $this->sourceFactory->create();
        $this->resource->load($model, SourceInterface::SOURCE_ID, $sourceId);
        return $model;
    }

    /**
     * Load source data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SourceSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var \Magento\Inventory\Model\Resource\Source\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SourceInterface[] $sources */
        $sources = [];
        /** @var SourceInterface $source */
        foreach ($collection->getItems() as $source) {
            $addresses[] = $source;
        }

        /** @var SourceSearchResultsInterface $searchResults */
        $searchResults = $this->sourceSearchResultsFactory->create();
        $searchResults->setItems($sources);
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
