<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Command;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\ResourceModel\SourceStockLink\Collection as StockLinkCollection;
use Magento\Inventory\Model\ResourceModel\SourceStockLink\CollectionFactory as StockLinkCollectionFactory;
use Magento\InventoryApi\Api\Command\GetAssignedSourcesByStockIdInterface;
use Magento\InventoryApi\Api\Data\StockInterface;


/**
 * @inheritdoc
 */
class GetAssignedSourcesByStockId implements GetAssignedSourcesByStockIdInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var StockLinkCollectionFactory
     */
    private $stockLinkCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * SourceStockLinkManagement constructor.
     * @param CollectionProcessorInterface $collectionProcessor
     * @param StockLinkCollectionFactory $stockLinkCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        StockLinkCollectionFactory $stockLinkCollectionFactory,
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

        /** @var StockLinkCollection $collection */
        $collection = $this->stockLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        return $collection->getItems();
    }
}
