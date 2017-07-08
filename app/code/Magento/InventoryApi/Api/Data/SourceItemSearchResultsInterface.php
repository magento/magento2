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
interface SourceItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get sources items list.
     *
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    public function getItems();
}
