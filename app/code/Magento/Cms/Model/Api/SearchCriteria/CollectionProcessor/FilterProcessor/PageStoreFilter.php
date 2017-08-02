<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class \Magento\Cms\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\PageStoreFilter
 *
 * @since 2.2.0
 */
class PageStoreFilter implements CustomFilterInterface
{
    /**
     * Apply custom store filter to collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     * @since 2.2.0
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        /** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection */
        $collection->addStoreFilter($filter->getValue(), false);

        return true;
    }
}
