<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\ResourceModel\SourceStockLink\CollectionFactory as StockLinkCollectionFactory;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\SourceStockLinkManagementInterface;

/**
 * Class to get assign sources by stock.
 */
class SourceStockLinkManagement implements SourceStockLinkManagementInterface
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
    public function getAssignedSources($stockId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockInterface::STOCK_ID, $stockId)
            ->create();

        /** @var  $collection */
        $collection = $this->stockLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        return $collection->getItems();
    }
}
