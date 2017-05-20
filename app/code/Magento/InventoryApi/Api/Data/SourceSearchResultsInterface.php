<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface SourceSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get sources list.
     *
     * @return \Magento\InventoryApi\Api\Data\SourceInterface[]
     */
    public function getItems();

    /**
     * Set sources list.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
