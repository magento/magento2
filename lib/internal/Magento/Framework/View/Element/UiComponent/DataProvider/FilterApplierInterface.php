<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;

/**
 * Interface FilterApplierInterface
 * @since 2.0.0
 */
interface FilterApplierInterface
{
    /**
     * Apply filter
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     * @since 2.0.0
     */
    public function apply(Collection $collection, Filter $filter);
}
