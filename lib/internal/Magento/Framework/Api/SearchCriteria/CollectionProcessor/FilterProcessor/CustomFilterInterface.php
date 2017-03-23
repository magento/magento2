<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @api
 */
interface CustomFilterInterface
{
    /**
     * Apply Custom Filter to Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter was applied
     */
    public function apply(Filter $filter, AbstractDb $collection);
}
