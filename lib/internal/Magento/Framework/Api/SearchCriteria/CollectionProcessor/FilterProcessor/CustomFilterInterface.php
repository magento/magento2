<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @api
 * @since 101.0.0
 */
interface CustomFilterInterface
{
    /**
     * Apply Custom Filter to Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter was applied
     * @since 101.0.0
     */
    public function apply(Filter $filter, AbstractDb $collection);
}
