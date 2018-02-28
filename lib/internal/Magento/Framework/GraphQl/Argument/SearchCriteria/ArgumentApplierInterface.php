<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria;

use Magento\Framework\GraphQl\ArgumentInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * Interface used to apply each argument to a search criteria
 */
interface ArgumentApplierInterface
{
    /**
     * Apply a specific argument to a search criteria like filter, currentPage, etc.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param ArgumentInterface $argument
     * @return SearchCriteriaInterface
     */
    public function applyArgument(SearchCriteriaInterface $searchCriteria, ArgumentInterface $argument);
}
