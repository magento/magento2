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
 */
interface StockStatusCollectionInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface[]
     */
    public function getItems();


    /**
     * Sets items
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get search criteria.
     *
     * @return \Magento\CatalogInventory\Api\StockStatusCriteriaInterface
     */
    public function getSearchCriteria();
}
