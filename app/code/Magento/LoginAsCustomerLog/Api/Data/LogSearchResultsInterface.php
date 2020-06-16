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
 */
interface LogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get log list.
     *
     * @return \Magento\LoginAsCustomerLog\Api\Data\LogInterface[]
     */
    public function getItems();

    /**
     * Set log list.
     *
     * @param \Magento\LoginAsCustomerLog\Api\Data\LogInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
