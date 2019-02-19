<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\Filter;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection;

/**
 * Custom store filter to collection
 */
class StoreFilter implements CustomFilterInterface
{
    /**
     * Apply custom store filter to collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        /** @var Collection $collection */
        $collection->addStoreFilter($filter->getValue());
        return true;
    }
}
