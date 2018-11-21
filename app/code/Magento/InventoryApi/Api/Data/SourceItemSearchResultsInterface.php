<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

/**
 * Search results of Repository::getList method
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get source items list
     *
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    public function getItems();

    /**
     * Set source items list
     *
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
