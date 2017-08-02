<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Api\Data;

/**
 * Interface for bookmark search results
 *
 * @api
 * @since 2.0.0
 */
interface BookmarkSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list
     *
     * @return \Magento\Ui\Api\Data\BookmarkInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set customers list
     *
     * @api
     * @param \Magento\Ui\Api\Data\BookmarkInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
