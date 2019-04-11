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
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForSkuInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterfaceFactory;
use Magento\InventoryExportStockApi\Api\ExportStockDataInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

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
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var int
     */
    private $qtyForNotManageStock;
    /**
     * @var IsSourceItemManagementAllowedForSkuInterface
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * ExportStockData constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ExportStockDataSearchResultInterfaceFactory $exportStockDataSearchResultFactory
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku
     * @param int $qtyForNotManageStock
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ExportStockDataSearchResultInterfaceFactory $exportStockDataSearchResultFactory,
        GetProductSalableQtyInterface $getProductSalableQty,
        IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku,
        int $qtyForNotManageStock
    ) {
        $this->productRepository = $productRepository;
        $this->exportStockDataSearchResultFactory = $exportStockDataSearchResultFactory;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
        $this->qtyForNotManageStock = $qtyForNotManageStock;
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
        $items = $this->getProductStockDataArray($productSearchResult->getItems(), $stockId);
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
     * @return array
     * @throws InputException
     * @throws LocalizedException
     */
    private function getProductStockDataArray(array $products, int $stockId): array
    {
        $items = [];
        foreach ($products as $product) {
            $sku = $product->getSku();
            if ($this->isSourceItemManagementAllowedForSku->execute($sku)) {
                $items[] = [
                    'sku' => $sku,
                    'qty' => $this->getProductSalableQty->execute(
                        $sku,
                        $stockId
                    ) ?: $this->qtyForNotManageStock
                ];
            }
        }

        return $items;
    }
}
