<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria;

use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * Interface used to apply each argument to a search criteria
 *
 * @api
 */
interface ArgumentApplierInterface
{
    /**
     * Apply a specific argument to a search criteria like filter, sort, etc.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string $fieldName
     * @param string $argumentName
     * @param array $argument
     * @return SearchCriteriaInterface
     */
    public function applyArgument(
        SearchCriteriaInterface $searchCriteria,
        string $fieldName,
        string $argumentName,
        array $argument
    ): SearchCriteriaInterface;
}
