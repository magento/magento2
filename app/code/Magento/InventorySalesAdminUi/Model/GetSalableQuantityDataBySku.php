<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Model;

use Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

/**
 * Get salable quantity data by sku
 */
class GetSalableQuantityDataBySku
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var GetAssignedStockIdsBySku
     */
    private $getAssignedStockIdsBySku;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param StockRepositoryInterface $stockRepository
     * @param GetAssignedStockIdsBySku $getAssignedStockIdsBySku
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        StockRepositoryInterface $stockRepository,
        GetAssignedStockIdsBySku $getAssignedStockIdsBySku,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->stockRepository = $stockRepository;
        $this->getAssignedStockIdsBySku = $getAssignedStockIdsBySku;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * @param string $sku
     * @return array
     */
    public function execute(string $sku): array
    {
        $stockInfo = [];
        $stockIds = $this->getAssignedStockIdsBySku->execute($sku);
        if (count($stockIds)) {
            foreach ($stockIds as $stockId) {
                $stockId = (int)$stockId;
                $stock = $this->stockRepository->get($stockId);
                $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
                $isManageStock = $stockItemConfiguration->isManageStock();
                $stockInfo[] = [
                    'stock_name' => $stock->getName(),
                    'qty' => $isManageStock ? $this->getProductSalableQty->execute($sku, $stockId) : null,
                    'manage_stock' => $isManageStock,
                ];
            }
        }
        return $stockInfo;
    }
}
