<?php

namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface SourceItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get sources items list.
     *
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    public function getItems();
}