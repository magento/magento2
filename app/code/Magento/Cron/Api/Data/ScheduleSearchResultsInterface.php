<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cron\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cron schedule search results
 * @api
 */
interface ScheduleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Magento\Cron\Api\Data\ScheduleInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Magento\Cron\Api\Data\ScheduleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
