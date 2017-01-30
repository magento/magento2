<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Api\Data;

/**
 * Interface for bookmark search results.
 */
interface BookmarkSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list.
     *
     * @api
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
