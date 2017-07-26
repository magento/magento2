<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\StockRepository;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\ResourceModel\Stock\Collection;
use Magento\Inventory\Model\ResourceModel\Stock\CollectionFactory;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterface;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterfaceFactory;

class GetList
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
     * @var StockSearchResultsInterfaceFactory
     */
    private $stockSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * SourceRepository constructor
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $stockCollectionFactory
     * @param StockSearchResultsInterfaceFactory $stockSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $stockCollectionFactory,
        StockSearchResultsInterfaceFactory $stockSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->stockSearchResultsFactory = $stockSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return StockSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var Collection $collection */
        $collection = $this->stockCollectionFactory->create();

        if (null === $searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        /** @var StockSearchResultsInterface $searchResult */
        $searchResult = $this->stockSearchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }
}
