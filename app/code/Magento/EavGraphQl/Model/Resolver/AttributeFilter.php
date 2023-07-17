<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Creates a SearchCriteriaBuilder object from the provided arguments
 */
class AttributeFilter
{
    /**
     * Returns a SearchCriteriaBuilder object with filters from the passed args
     *
     * @param array $filterArgs
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @return SearchCriteriaBuilder SearchCriteriaBuilder
     */
    public function execute(array $filterArgs, $searchCriteriaBuilder): SearchCriteriaBuilder
    {
        foreach ($filterArgs as $key => $value) {
            $searchCriteriaBuilder->addFilter($key, $value);
        }

        return $searchCriteriaBuilder;
    }
}
