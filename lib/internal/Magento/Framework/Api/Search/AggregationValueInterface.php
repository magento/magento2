<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface Aggregation Value
 *
 * @api
 */
interface AggregationValueInterface
{
    /**
     * Get aggregation
     *
     * @return string|array
     */
    public function getValue();

    /**
     * Get metrics
     *
     * @return mixed[]
     */
    public function getMetrics();
}
