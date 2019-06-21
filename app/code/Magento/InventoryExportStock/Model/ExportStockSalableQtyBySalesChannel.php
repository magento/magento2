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
use Magento\InventoryExportStockApi\Api\ExportStockSalableQtyBySalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;

/**
 * Class ExportStockSalableQty provides product stock information by search criteria
 */
class ExportStockSalableQtyBySalesChannel implements ExportStockSalableQtyBySalesChannelInterface
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
     * @var GetStockBySalesChannelInterface
     */
    private $getStockBySalesChannel;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelInterfaceFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory
     * @param PreciseExportStockProcessor $preciseExportStockProcessor
     * @param GetStockBySalesChannelInterface $getStockBySalesChannel
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory,
        PreciseExportStockProcessor $preciseExportStockProcessor,
        GetStockBySalesChannelInterface $getStockBySalesChannel,
        SalesChannelInterfaceFactory $salesChannelInterfaceFactory
    ) {
        $this->productRepository = $productRepository;
        $this->exportStockSalableQtySearchResultFactory = $exportStockSalableQtySearchResultFactory;
        $this->preciseExportStockProcessor = $preciseExportStockProcessor;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->salesChannelInterfaceFactory = $salesChannelInterfaceFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function execute(
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): ExportStockSalableQtySearchResultInterface {
        $stock = $this->getStockBySalesChannel->execute($salesChannel);
        $productSearchResult = $this->getProducts($searchCriteria);
        $items = $this->preciseExportStockProcessor->execute($productSearchResult->getItems(), $stock->getStockId());
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
     *
     * @return SearchResultsInterface
     */
    private function getProducts(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        return $this->productRepository->getList($searchCriteria);
    }
}
