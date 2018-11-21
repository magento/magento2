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
interface SourceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get sources list
     *
     * @return \Magento\InventoryApi\Api\Data\SourceInterface[]
     */
    public function getItems();

    /**
     * Set sources list
     *
     * @param \Magento\InventoryApi\Api\Data\SourceInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
