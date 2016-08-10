<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class AttributeIdFilter implements CustomFilterInterface
{
    /**
     * Apply attribute ID filter to collection
     *
     * Prevent ambiguity during filtration
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        if ($filter->getField() == AttributeInterface::ATTRIBUTE_ID) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter(
                'main_table.' . $filter->getField(),
                [$conditionType => $filter->getValue()]
            );
            return true;
        }
        return false;
    }
}
