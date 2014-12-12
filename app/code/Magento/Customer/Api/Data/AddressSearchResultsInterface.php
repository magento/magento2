<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer address search results.
 */
interface AddressSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer addresses list.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getItems();
}
