<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Api\Data;

/**
 * Interface for bookmark search results.
 * @api
 */
interface BookmarkSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list.
     *
     * @return \Magento\Ui\Api\Data\BookmarkInterface[]
     */
    public function getItems();

    /**
     * Set customers list.
     *
     * @api
     * @param \Magento\Ui\Api\Data\BookmarkInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
