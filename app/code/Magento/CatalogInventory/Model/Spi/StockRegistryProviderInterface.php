<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Spi;

/**
 * Interface StockRegistryProviderInterface
 * @since 2.0.0
 */
interface StockRegistryProviderInterface
{
    /**
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     * @since 2.0.0
     */
    public function getStock($scopeId);

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @since 2.0.0
     */
    public function getStockItem($productId, $scopeId);

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @since 2.0.0
     */
    public function getStockStatus($productId, $scopeId);
}
