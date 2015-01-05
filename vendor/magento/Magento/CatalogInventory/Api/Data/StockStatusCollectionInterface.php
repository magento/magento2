<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Stock Status collection interface
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
     * Get search criteria.
     *
     * @return \Magento\CatalogInventory\Api\StockStatusCriteriaInterface
     */
    public function getSearchCriteria();
}
