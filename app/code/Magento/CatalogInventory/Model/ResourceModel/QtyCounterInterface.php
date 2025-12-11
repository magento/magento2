<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogInventory\Model\ResourceModel;

/**
 * Correct particular stock products qty
 */
interface QtyCounterInterface
{
    /**
     * Correct particular stock products qty based on operator
     *
     * @param int[] $items
     * @param int $websiteId
     * @param string $operator +/-
     * @return void
     */
    public function correctItemsQty(array $items, $websiteId, $operator);
}
