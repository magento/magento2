<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class ReviewCustomerFilter
 */
class ReviewCustomerFilter implements CustomFilterInterface
{
    /**
     * Apply customer_id Filter to Review Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter is applied
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $collection */
        $collection->addCustomerFilter($filter->getValue());

        return true;
    }
}
