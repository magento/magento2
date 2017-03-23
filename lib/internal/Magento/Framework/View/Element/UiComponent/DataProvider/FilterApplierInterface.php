<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;

/**
 * Interface FilterApplierInterface
 */
interface FilterApplierInterface
{
    /**
     * Apply filter
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(Collection $collection, Filter $filter);
}
