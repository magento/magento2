<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Stock Status collection interface
 * @api
 * @since 2.0.0
 */
interface StockStatusCollectionInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Sets items
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);

    /**
     * Get search criteria.
     *
     * @return \Magento\CatalogInventory\Api\StockStatusCriteriaInterface
     * @since 2.0.0
     */
    public function getSearchCriteria();
}
