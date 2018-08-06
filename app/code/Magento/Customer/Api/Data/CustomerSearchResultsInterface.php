<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer search results.
 * @api
 * @since 100.0.2
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
