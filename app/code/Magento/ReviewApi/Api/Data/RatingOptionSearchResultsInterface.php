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
interface RatingOptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get rating option list
     *
     * @return \Magento\ReviewApi\Api\Data\RatingOptionInterface[]
     */
    public function getItems();

    /**
     * Set rating option list
     *
     * @param \Magento\ReviewApi\Api\Data\RatingOptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
