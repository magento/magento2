<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterfaceFactory;

/**
 * @inheritdoc
 */
class GetList implements GetListInterface
{
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
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $sourceItemCollectionFactory
     * @param SourceItemSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        SourceItemSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->sourceItemSearchResultsFactory = $sourceItemSearchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria): SourceItemSearchResultsInterface
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
}
