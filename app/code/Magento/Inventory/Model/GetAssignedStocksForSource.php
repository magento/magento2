<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;


use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\Collection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\CollectionFactory;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\GetAssignedStocksForSourceInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class GetAssignedStocksForSource implements GetAssignedStocksForSourceInterface
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
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $stockLinkCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StockRepositoryInterface $sourceRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $stockLinkCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockRepositoryInterface $stockRepository,
        LoggerInterface $logger
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->stockLinkCollectionFactory = $stockLinkCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockRepository = $stockRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute($sourceId)
    {
        if (!is_numeric($sourceId)) {
            throw new InputException(__('Input data is invalid'));
        }

        try {
            $stockIds = $this->getAssignedStockIds($sourceId);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(StockInterface::STOCK_ID, $stockIds, 'in')
                ->create();
            $searchResult = $this->stockRepository->getList($searchCriteria);
            return $searchResult->getItems();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Sources for Stock'), $e);
        }
    }

    /**
     * Get all linked StockIds by given sourceId.
     *
     * @param $sourceId
     * @return array
     */
    private function getAssignedStockIds($sourceId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLink::SOURCE_ID, (int)$sourceId)
            ->create();
        /** @var Collection $collection */
        $collection = $this->stockLinkCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $data = $collection->getData();
        return $data ? array_column($data, StockSourceLink::STOCK_ID) : [];
    }
}
