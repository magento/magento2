<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer groups search results.
 * @api
 * @since 100.0.2
 */
interface GroupSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer groups list.
     *
     * @return \Magento\Customer\Api\Data\GroupInterface[]
     */
    public function getItems();

    /**
     * Set customer groups list.
     *
     * @api
     * @param \Magento\Customer\Api\Data\GroupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
