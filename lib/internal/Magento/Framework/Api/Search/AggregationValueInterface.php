<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

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
