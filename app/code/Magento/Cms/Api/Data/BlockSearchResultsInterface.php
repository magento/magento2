<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms block search results.
 * @api
 */
interface BlockSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \Magento\Cms\Api\Data\BlockInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \Magento\Cms\Api\Data\BlockInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
