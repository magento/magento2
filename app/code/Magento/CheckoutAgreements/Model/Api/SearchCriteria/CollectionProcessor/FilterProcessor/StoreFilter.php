<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

class StoreFilter implements
    \Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface
{
    /**
     * Apply custom store filter to collection
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @return bool
     */
    public function apply(
        \Magento\Framework\Api\Filter $filter,
        \Magento\Framework\Data\Collection\AbstractDb $collection
    ) {
        /** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection */
        $collection->addStoreFilter($filter->getValue(), false);
        return true;
    }
}

