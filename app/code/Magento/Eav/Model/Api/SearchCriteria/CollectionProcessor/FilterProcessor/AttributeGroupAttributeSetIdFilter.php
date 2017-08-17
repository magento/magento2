<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class \Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AttributeGroupAttributeSetIdFilter
 *
 */
class AttributeGroupAttributeSetIdFilter implements CustomFilterInterface
{
    /**
     * Apply attribute set ID filter to collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $collection */
        $collection->setAttributeSetFilter($filter->getValue());

        return true;
    }
}
