<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Spi;

/**
 * Interface StockRegistryProviderInterface
 */
interface StockRegistryProviderInterface
{
    /**
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($scopeId);

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId);

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $scopeId);
}
