<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterfaceFactory;
use Magento\InventoryExportStockApi\Api\ExportStockSalableQtyInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

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
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * ExportStockSalableQty constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory
     * @param PreciseExportStockProcessor $preciseExportStockProcessor
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory,
        PreciseExportStockProcessor $preciseExportStockProcessor,
        StockResolverInterface $stockResolver
    ) {
        $this->productRepository = $productRepository;
        $this->exportStockSalableQtySearchResultFactory = $exportStockSalableQtySearchResultFactory;
        $this->preciseExportStockProcessor = $preciseExportStockProcessor;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function execute(
        SearchCriteriaInterface $searchCriteria,
        string $websiteCode
    ): ExportStockSalableQtySearchResultInterface {
        $stockId = $this->stockResolver
            ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        $productSearchResult = $this->getProducts($searchCriteria);
        $items = $this->preciseExportStockProcessor
            ->execute($productSearchResult->getItems(), $stockId);
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
