<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer address search results.
 * @api
 * @since 2.0.0
 */
interface AddressSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer addresses list.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set customer addresses list.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
