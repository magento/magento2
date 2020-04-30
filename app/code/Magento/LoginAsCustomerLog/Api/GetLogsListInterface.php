<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\LoginAsCustomerLog\Api\Data\LogSearchResultsInterface;

/**
 * Get login as customer log list considering search criteria.
 *
 * @api
 */
interface GetLogsListInterface
{
    /**
     * Retrieve list of log entities.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return LogSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): LogSearchResultsInterface;
}
