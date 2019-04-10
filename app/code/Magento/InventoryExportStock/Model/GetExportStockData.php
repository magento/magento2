<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterfaceFactory;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * Class GetExportStockData
 */
class GetExportStockData
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
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * ExportStockData constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ExportStockDataSearchResultInterfaceFactory $exportStockDataSearchResultFactory
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ExportStockDataSearchResultInterfaceFactory $exportStockDataSearchResultFactory,
        GetProductSalableQtyInterface $getProductSalableQty,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->productRepository = $productRepository;
        $this->exportStockDataSearchResultFactory = $exportStockDataSearchResultFactory;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Provides product stock data according to search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param int $stockId
     * @param int $qtyForNotManageStock
     * @return ExportStockDataSearchResultInterface
     * @throws Exception
     */
    public function execute(
        SearchCriteriaInterface $searchCriteria,
        int $stockId=null,
        int $qtyForNotManageStock=1
    ): ExportStockDataSearchResultInterface {
        $productSearchResult = $this->getProducts($searchCriteria);
        $items = $this->getProductStockDataArray($productSearchResult->getItems(), $stockId, $qtyForNotManageStock);
        $searchResult = $this->exportStockDataSearchResultFactory->create();
        $searchResult->setSearchCriteria($productSearchResult->getSearchCriteria());
        $searchResult->setItems($items);
        $searchResult->setTotalCount($productSearchResult->getTotalCount());

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

    /**
     * Provides salable qty and sku in array
     *
     * @param Product[] $products
     * @param int $stockId
     * @param int $qtyForNotManageStock
     * @return array
     * @throws Exception
     */
    private function getProductStockDataArray(array $products, int $stockId=null, int $qtyForNotManageStock=1): array
    {
        if (!$stockId) {
            $stockId = $this->defaultStockProvider->getId();
        }
        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'sku' => $product->getSku(),
                'qty' => $this->getProductSalableQty->execute($product->getSku(), $stockId) ?: $qtyForNotManageStock
            ];
        }

        return $items;
    }
}
