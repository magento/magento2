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
interface ReviewSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get review list
     *
     * @return \Magento\ReviewApi\Api\Data\ReviewInterface[]
     */
    public function getItems();

    /**
     * Set review list
     *
     * @param \Magento\ReviewApi\Api\Data\ReviewInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
