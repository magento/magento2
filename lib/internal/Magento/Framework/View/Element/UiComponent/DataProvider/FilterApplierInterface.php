<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Interface FilterApplierInterface
 */
interface FilterApplierInterface
{
    /**
     * Apply filter
     *
     * @param AbstractDb $collection
     * @param array $filters
     * @return void
     */
    public function apply(AbstractDb $collection, \Magento\Framework\Api\Filter $filters);
}
