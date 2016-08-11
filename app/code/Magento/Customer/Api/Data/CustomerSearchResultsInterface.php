<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer search results.
 * @api
 */
interface CustomerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface[]
     */
    public function getItems();

    /**
     * Set customers list.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
