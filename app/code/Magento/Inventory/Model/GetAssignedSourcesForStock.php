<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\ResourceModel\SourceStockLink\Collection;
use Magento\Inventory\Model\ResourceModel\SourceStockLink\CollectionFactory;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;

/**
 * @inheritdoc
 */
class GetAssignedSourcesForStock implements GetAssignedSourcesForStockInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $stockLinkCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $stockLinkCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $stockLinkCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->stockLinkCollectionFactory = $stockLinkCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute($stockId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockInterface::STOCK_ID, $stockId)
            ->create();

        /** @var Collection $collection */
        $collection = $this->stockLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        return $collection->getItems();
    }
}
