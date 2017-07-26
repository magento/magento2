<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class SourceItemRepository implements SourceItemRepositoryInterface
{
    /**
     * @var SourceItemResourceModel
     */
    private $sourceItemResource;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var SourceItemSearchResultsInterfaceFactory
     */
    private $sourceItemSearchResultsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceRepository constructor
     *
     * @param SourceItemResourceModel $sourceItemResource
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $sourceItemCollectionFactory
     * @param SourceItemSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceItemResourceModel $sourceItemResource,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        SourceItemSearchResultsInterfaceFactory $sourceItemSearchResultsFactory,
        LoggerInterface $logger
    ) {
        $this->sourceItemResource = $sourceItemResource;
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->sourceItemSearchResultsFactory = $sourceItemSearchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SourceItemSearchResultsInterface $searchResult */
        $searchResult = $this->sourceItemSearchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function delete(SourceItemInterface $sourceItem)
    {
        try {
            $this->sourceItemResource->delete($sourceItem);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete Source Item'), $e);
        }
    }
}
