<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Spi;

/**
 * Interface StockRegistryProviderInterface
 */
interface StockRegistryProviderInterface
{
    /**
     * @param int|null $stockId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($stockId);

    /**
     * @param int $productId
     * @param int $stockId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $stockId);

    /**
     * @param int $productId
     * @param int $stockId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $stockId);
}
