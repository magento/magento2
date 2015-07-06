<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class RegularFilter
 */
class RegularFilter implements FilterApplierInterface
{
    /**
     * Apply regular filters like collection filters
     *
     * @param AbstractDb $collection
     * @param array $filters
     * @return void
     */
    public function apply(AbstractDb $collection, $filters)
    {
        foreach ($filters as $filter) {
            $collection->addFieldToFilter($filter['field'], $filter['condition']);
        }
    }
}
