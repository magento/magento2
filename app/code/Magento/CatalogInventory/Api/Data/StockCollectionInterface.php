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
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://devdocs.magento.com/guides/v2.3/rest/modules/inventory/inventory.html
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
