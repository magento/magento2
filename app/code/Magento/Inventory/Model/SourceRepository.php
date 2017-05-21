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

/**
 * Class SourceRepository
 * @package Magento\Inventory\Model
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
        SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory
    ) {
        $this->resource = $resource;
        $this->sourceFactory = $sourceFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->sourceSearchResultsFactory = $sourceSearchResultsFactory;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function get($sourceId)
    {
        /** @var SourceInterface|AbstractModel $model */
        $model = $this->sourceFactory->create();
        $this->resource->load($model, $sourceId, SourceInterface::SOURCE_ID);

        if (!$model->getSourceId()) {
            throw NoSuchEntityException::singleField(SourceInterface::SOURCE_ID, $sourceId);
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var \Magento\Inventory\Model\Resource\Source\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SourceSearchResultsInterface $searchResults */
        $searchResults = $this->sourceSearchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
