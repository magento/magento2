<?php

namespace Magento\CatalogInventory\Api;
use Magento\CatalogInventory\Api\Data\InventoryInterface;

/**
 * Index interface for API
 *
 */
interface InventoryIndexInterface
{
    /**
     * @param InventoryInterface $indexInterface
     * @param int[]|int|null $productIds
     * @return mixed
     */
    public function rebuild(InventoryInterface $indexInterface, $productIds = null);
}
