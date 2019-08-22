<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Stock collection interface
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface StockCollectionInterface
 * @api
 * @since 100.0.2
 *
 * @deprecated 2.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.3/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.3/inventory/catalog-inventory-replacements.html
 */
interface StockCollectionInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\CatalogInventory\Api\Data\StockInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Magento\CatalogInventory\Api\Data\StockInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
