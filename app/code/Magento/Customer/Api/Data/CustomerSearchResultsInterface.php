<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer search results.
 */
interface CustomerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list.
     *
     * @api
     * @return \Magento\Customer\Api\Data\CustomerInterface[]
     */
    public function getItems();

    /**
     * Set customers list.
     *
     * @api
     * @param \Magento\Customer\Api\Data\CustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
