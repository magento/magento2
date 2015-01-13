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
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($websiteId);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $websiteId);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $websiteId);
}
