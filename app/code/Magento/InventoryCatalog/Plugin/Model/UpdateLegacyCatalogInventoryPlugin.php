<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Plugin\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Plugin help to fill the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item to don't break the backward compatible.
 */
class UpdateLegacyCatalogInventoryPlugin
{

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Plugin method to fill the legacy tables.
     *
     * @param ReservationsAppendInterface $subject
     * @param void $result
     * @param ReservationInterface[] $reservations
     *
     * @see ReservationsAppendInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function afterExecute(ReservationsAppendInterface $subject, $result, array $reservations)
    {
        $this->updateStockItemAndStatusTable($reservations);
        return $result;
    }

    /**
     * Updates cataloginventory_stock_item and cataloginventory_stock_status qty with reservation information.
     *
     * @param ReservationInterface[] $reservations
     * @return void
     */
    private function updateStockItemAndStatusTable(array $reservations)
    {
        foreach ($reservations as $reservation) {
            $sku = $reservation->getSku();
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $stockItem->setQty($stockItem->getQty() + $reservation->getQuantity());
            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

            $stockStatus = $this->stockRegistry->getStockStatus($stockItem->getProductId());
            $stockStatus->setQty($stockStatus->getQty() + $reservation->getQuantity());
            $stockStatus->save();
        }
    }
}
