<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface ReportingInterface
 */
interface ReportingInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function search(SearchCriteriaInterface $searchCriteria);
}
