<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Inventory\Model\ResourceModel\GetAssignedStockIdsBySku;
use Magento\InventoryApi\Api\GetSalableProductQtyInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

/**
 * Get assigned stocks data by sku
 */
class GetAssignedStocksDataBySku
{
    /**
     * @var GetSalableProductQtyInterface
     */
    private $getSalableProductQty;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var GetAssignedStockIdsBySku
     */
    private $getAssignedStockIdsBySku;

    /**
     * @param GetSalableProductQtyInterface $getSalableProductQty
     * @param StockRepositoryInterface $stockRepository
     * @param GetAssignedStockIdsBySku $getAssignedStockIdsBySku
     */
    public function __construct(
        GetSalableProductQtyInterface $getSalableProductQty,
        StockRepositoryInterface $stockRepository,
        GetAssignedStockIdsBySku $getAssignedStockIdsBySku
    ) {
        $this->getSalableProductQty = $getSalableProductQty;
        $this->stockRepository = $stockRepository;
        $this->getAssignedStockIdsBySku = $getAssignedStockIdsBySku;
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
                $stockInfo[] = [
                    'stock_name' => $stock->getName(),
                    'qty' => $this->getSalableProductQty->execute($sku, $stockId),
                ];
            }
        }
        return $stockInfo;
    }
}
