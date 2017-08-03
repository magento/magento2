<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer groups search results.
 * @api
 * @since 2.0.0
 */
interface GroupSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer groups list.
     *
     * @return \Magento\Customer\Api\Data\GroupInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set customer groups list.
     *
     * @api
     * @param \Magento\Customer\Api\Data\GroupInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
