<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * CatalogInventory Stock source model
 * @api
 * @since 100.0.2
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.3/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.3/inventory/catalog-inventory-replacements.html
 */
class Stock extends AbstractSource
{
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        return [
            ['value' => \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK, 'label' => __('In Stock')],
            ['value' => \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK, 'label' => __('Out of Stock')]
        ];
    }

    /**
     * Add Value Sort To Collection Select.
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir
     *
     * @return $this
     * @since 100.2.4
     */
    public function addValueSortToCollection($collection, $dir = \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
    {
        $collection->getSelect()->joinLeft(
            ['stock_item_table' => $collection->getTable('cataloginventory_stock_item')],
            "e.entity_id=stock_item_table.product_id",
            []
        );
        $collection->getSelect()->order("stock_item_table.qty $dir");
        return $this;
    }
}
