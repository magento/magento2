<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\Collection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\CollectionFactory;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\GetSourceLinkListInterface;

/**
 * @inheritdoc
 */
class GetSourceLinkList implements GetSourceLinkListInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $stockCollectionFactory;

    /**
     * @var StockSourceLinkSearchResultsInterfaceFactory
     */
    private $stockSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $stockCollectionFactory
     * @param StockSourceLinkSearchResultsInterfaceFactory $stockSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $stockCollectionFactory,
        StockSourceLinkSearchResultsInterfaceFactory $stockSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->stockSearchResultsFactory = $stockSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria): StockSourceLinkSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->stockCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var StockSearchResultsInterface $searchResult */
        $searchResult = $this->stockSearchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
