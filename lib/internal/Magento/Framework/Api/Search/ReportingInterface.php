<?php
/**
 * Created by PhpStorm.
 * User: akaplya
 * Date: 08.07.15
 * Time: 18:55
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