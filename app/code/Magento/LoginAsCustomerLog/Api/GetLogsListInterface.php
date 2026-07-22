<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\LoginAsCustomerLog\Api\Data\LogSearchResultsInterface;

/**
 * Get login as customer log list considering search criteria.
 *
 * @api
 * @since 100.4.0
 */
interface GetLogsListInterface
{
    /**
     * Retrieve list of log entities.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return LogSearchResultsInterface
     * @since 100.4.0
     */
    public function execute(SearchCriteriaInterface $searchCriteria): LogSearchResultsInterface;
}
