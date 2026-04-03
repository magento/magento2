<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Api\Data;

/**
 * Interface for bookmark search results
 *
 * @api
 * @since 100.0.2
 */
interface BookmarkSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list
     *
     * @return \Magento\Ui\Api\Data\BookmarkInterface[]
     */
    public function getItems();

    /**
     * Set customers list
     *
     * @param \Magento\Ui\Api\Data\BookmarkInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
