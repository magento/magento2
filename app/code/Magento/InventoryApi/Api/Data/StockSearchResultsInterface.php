<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Search results of Repository::getList method
 *
 * @api
 */
interface StockSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get stocks list
     *
     * @return \Magento\InventoryApi\Api\Data\StockInterface[]
     */
    public function getItems();

    /**
     * Set stocks list
     *
     * @param \Magento\InventoryApi\Api\Data\StockInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
