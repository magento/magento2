<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for newsletter subscription search results.
 *
 * @api
 */
interface SubscriptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get newsletter subscription list.
     *
     * @return \Magento\NewsletterApi\Api\Data\SubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set newsletter subscription list.
     *
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
