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
use Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterfaceFactory;
use Magento\InventoryExportStockApi\Api\ExportStockSalableQtyInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;

/**
 * Class ExportStockSalableQty provides product stock information by search criteria
 */
class ExportStockSalableQty implements ExportStockSalableQtyInterface
{
    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelInterfaceFactory;

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
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory
     * @param PreciseExportStockProcessor $preciseExportStockProcessor
     * @param GetStockBySalesChannelInterface $getStockBySalesChannel
     */
    public function __construct(
        SalesChannelInterfaceFactory $salesChannelInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        ExportStockSalableQtySearchResultInterfaceFactory $exportStockSalableQtySearchResultFactory,
        PreciseExportStockProcessor $preciseExportStockProcessor,
        GetStockBySalesChannelInterface $getStockBySalesChannel
    ) {
        $this->productRepository = $productRepository;
        $this->exportStockSalableQtySearchResultFactory = $exportStockSalableQtySearchResultFactory;
        $this->preciseExportStockProcessor = $preciseExportStockProcessor;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->salesChannelInterfaceFactory = $salesChannelInterfaceFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        string $salesChannelCode,
        SearchCriteriaInterface $searchCriteria
    ): ExportStockSalableQtySearchResultInterface {
        $salesChannel = $this->salesChannelInterfaceFactory->create(
            [
                'data' => [
                    SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    SalesChannelInterface::CODE => $salesChannelCode
                ]
            ]
        );
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
