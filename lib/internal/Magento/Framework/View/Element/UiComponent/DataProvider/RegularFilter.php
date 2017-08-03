<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Filter;

/**
 * Class RegularFilter
 * @since 2.0.0
 */
class RegularFilter implements FilterApplierInterface
{
    /**
     * Apply regular filters like collection filters
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     * @since 2.0.0
     */
    public function apply(Collection $collection, Filter $filter)
    {
        $collection->addFieldToFilter($filter->getField(), [$filter->getConditionType() => $filter->getValue()]);
    }
}
