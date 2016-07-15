<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer address search results.
 * @api
 */
interface AddressSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer addresses list.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getItems();

    /**
     * Set customer addresses list.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
