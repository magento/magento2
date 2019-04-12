<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\InventoryExportStock\Model\ExportStockProcessor\StockExportProcessorPool;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterfaceFactory;
use Magento\InventoryExportStockApi\Api\ExportStockDataInterface;

/**
 * Class ExportStockData provides product stock information by search criteria
 */
class ExportStockData implements ExportStockDataInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ExportStockDataSearchResultInterfaceFactory
     */
    private $exportStockDataSearchResultFactory;

    /**
     * @var int
     */
    private $processorType;

    /**
     * @var StockExportProcessorPool
     */
    private $stockExportProcessorPool;

    /**
     * ExportStockData constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ExportStockDataSearchResultInterfaceFactory $exportStockDataSearchResultFactory
     * @param StockExportProcessorPool $stockExportProcessorPool
     * @param string $processor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ExportStockDataSearchResultInterfaceFactory $exportStockDataSearchResultFactory,
        StockExportProcessorPool $stockExportProcessorPool,
        string $processor
    ) {
        $this->productRepository = $productRepository;
        $this->exportStockDataSearchResultFactory = $exportStockDataSearchResultFactory;
        $this->stockExportProcessorPool = $stockExportProcessorPool;
        $this->processorType = $processor;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(
        SearchCriteriaInterface $searchCriteria,
        int $stockId
    ): ExportStockDataSearchResultInterface {
        $productSearchResult = $this->getProducts($searchCriteria);
        $processor = $this->stockExportProcessorPool->getStockExportProcessorByName($this->processorType);
        $items = $processor->execute($productSearchResult->getItems(), $stockId);
        $searchResult = $this->exportStockDataSearchResultFactory->create();
        $searchResult->setSearchCriteria($productSearchResult->getSearchCriteria());
        $searchResult->setItems($items);
        $searchResult->setTotalCount(count($items));

        return $searchResult;
    }

    /**
     * Provides product search result by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    private function getProducts(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        return $this->productRepository->getList($searchCriteria);
    }
}
