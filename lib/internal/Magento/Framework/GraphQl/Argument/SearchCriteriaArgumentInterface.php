<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument;

use Magento\Framework\Api\SearchCriteriaInterface;

use Magento\Framework\GraphQl\ArgumentInterface;

/**
 * Interface used for the actual filtering using SearchCriteriaInterface and one argument
 */
interface SearchCriteriaArgumentInterface extends ArgumentInterface
{
    /**
     * Implementation would affect SearchCriteria by populating filters, pagination etc.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    public function populateSearchCriteria(SearchCriteriaInterface $searchCriteria);
}
