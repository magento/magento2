<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Login as customer log entity search results interface.
 *
 * @api
 * @since 100.4.0
 */
interface LogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get log list.
     *
     * @return \Magento\LoginAsCustomerLog\Api\Data\LogInterface[]
     * @since 100.4.0
     */
    public function getItems();

    /**
     * Set log list.
     *
     * @param \Magento\LoginAsCustomerLog\Api\Data\LogInterface[] $items
     * @return void
     * @since 100.4.0
     */
    public function setItems(array $items);
}
