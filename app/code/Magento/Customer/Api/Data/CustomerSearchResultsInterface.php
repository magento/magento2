<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Interface for customer search results.
 * @api
 * @since 2.0.0
 */
interface CustomerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set customers list.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
