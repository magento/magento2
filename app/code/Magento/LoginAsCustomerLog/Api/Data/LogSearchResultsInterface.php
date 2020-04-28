<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

interface LogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get sources list
     *
     * @return \Magento\LoginAsCustomerLog\Api\Data\LogInterface[]
     */
    public function getItems();

    /**
     * Set sources list
     *
     * @param \Magento\LoginAsCustomerLog\Api\Data\LogInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
