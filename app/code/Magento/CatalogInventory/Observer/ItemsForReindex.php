<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

/**
 * Class \Magento\CatalogInventory\Observer\ItemsForReindex
 *
 * @since 2.0.0
 */
class ItemsForReindex
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $itemsForReindex;

    /**
     * @param array $items
     * @return void
     * @since 2.0.0
     */
    public function setItems(array $items)
    {
        $this->itemsForReindex = $items;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->itemsForReindex;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function clear()
    {
        $this->itemsForReindex = [];
    }
}
