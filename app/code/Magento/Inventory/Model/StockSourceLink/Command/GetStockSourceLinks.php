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
use Magento\Inventory\Model\ResourceModel\StockSourceLink\Collection as StockSourceLinkCollection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\CollectionFactory as StockSourceLinkCollectionFactory;
use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;

/**
 * @inheritdoc
 */
class GetStockSourceLinks implements GetStockSourceLinksInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var StockSourceLinkCollectionFactory
     */
    private $stockSourceLinkCollectionFactory;

    /**
     * @var StockSourceLinkSearchResultsInterfaceFactory
     */
    private $stockSourceLinkSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param StockSourceLinkCollectionFactory $stockSourceLinkCollectionFactory
     * @param StockSourceLinkSearchResultsInterfaceFactory $stockSourceLinkSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        StockSourceLinkCollectionFactory $stockSourceLinkCollectionFactory,
        StockSourceLinkSearchResultsInterfaceFactory $stockSourceLinkSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->stockSourceLinkCollectionFactory = $stockSourceLinkCollectionFactory;
        $this->stockSourceLinkSearchResultsFactory = $stockSourceLinkSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria): StockSourceLinkSearchResultsInterface
    {
        /** @var StockSourceLinkCollection $collection */
        $collection = $this->stockSourceLinkCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var StockSourceLinkSearchResultsInterface $searchResult */
        $searchResult = $this->stockSourceLinkSearchResultsFactory->create();

        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
