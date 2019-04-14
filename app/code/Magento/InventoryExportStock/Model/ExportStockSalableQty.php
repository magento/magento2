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
use Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterfaceFactory;
use Magento\InventoryExportStockApi\Api\ExportStockSalableQtyInterface;

/**
 * Class ExportStockSalableQty provides product stock information by search criteria
 */
class ExportStockSalableQty implements ExportStockSalableQtyInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ExportStockSalableQtySearchResultInterfaceFactory
     */
    private $exportStockSalableQtySearchResultFactory;

    /**
     * @var PreciseExportStockProcessor
     */
    private $preciseExportStockProcessor;

    /**
     * ExportStockSalableQty constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory
     * @param PreciseExportStockProcessor $preciseExportStockProcessor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory,
        PreciseExportStockProcessor $preciseExportStockProcessor
    ) {
        $this->productRepository = $productRepository;
        $this->exportStockSalableQtySearchResultFactory = $exportStockSalableQtySearchResultFactory;
        $this->preciseExportStockProcessor = $preciseExportStockProcessor;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(
        SearchCriteriaInterface $searchCriteria,
        int $stockId
    ): ExportStockSalableQtySearchResultInterface {
        $productSearchResult = $this->getProducts($searchCriteria);
        $items = $this->preciseExportStockProcessor->execute($productSearchResult->getItems(), $stockId);
        /** @var ExportStockSalableQtySearchResultInterface $searchResult */
        $searchResult = $this->exportStockSalableQtySearchResultFactory->create();
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
