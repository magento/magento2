<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Observer;

class ItemsForReindex
{
    /**
     * @var array
     */
    protected $itemsForReindex = [];

    /**
     * @param array $items
     * @return void
     */
    public function setItems(array $items)
    {
        $this->itemsForReindex = $items;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->itemsForReindex;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->itemsForReindex = [];
    }
}
