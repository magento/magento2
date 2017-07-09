<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\ResourceModel\Stock as ResourceStock;
use Magento\Inventory\Model\ResourceModel\Stock\CollectionFactory;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * The Repository Model for stock
 */
class StockRepository implements StockRepositoryInterface
{
    /**
     * @var ResourceStock
     */
    private $resourceStock;

    /**
     * @var StockInterfaceFactory
     */
    private $stockFactory;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceRepository constructor
     *
     * @param ResourceStock $resourceStock
     * @param StockInterfaceFactory $stockFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $stockCollectionFactory
     * @param StockSearchResultsInterfaceFactory $stockSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceStock $resourceStock,
        StockInterfaceFactory $stockFactory,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $stockCollectionFactory,
        StockSearchResultsInterfaceFactory $stockSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->resourceStock = $resourceStock;
        $this->stockFactory = $stockFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->stockSearchResultsFactory = $stockSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(StockInterface $stock)
    {
        try {
            $this->resourceStock->save($stock);
            return $stock->getStockId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save stock'), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function get($stockId)
    {
        $stock = $this->stockFactory->create();
        $this->resourceStock->load($stock, $stockId, StockInterface::STOCK_ID);

        if (!$stock->getStockId()) {
            throw NoSuchEntityException::singleField(StockInterface::STOCK_ID, $stockId);
        }
        return $stock;
    }

    /**
     * @inheritdoc
     */
    public function delete($stockId)
    {
        $stockItem = $this->get($stockId);

        try {
            $this->resourceStock->delete($stockItem);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete source item'), $e);
        }

    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        $collection = $this->stockCollectionFactory->create();

        if (null === $searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        $searchResult = $this->stockSearchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }
}
