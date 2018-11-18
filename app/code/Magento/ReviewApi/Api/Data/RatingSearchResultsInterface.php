<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReviewApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface RatingSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get rating list
     *
     * @return \Magento\ReviewApi\Api\Data\RatingInterface[]
     */
    public function getItems();

    /**
     * Set rating list
     *
     * @param \Magento\ReviewApi\Api\Data\RatingInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
