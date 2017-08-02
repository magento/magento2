<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface \Magento\Framework\Api\Search\AggregationValueInterface
 *
 * @since 2.0.0
 */
interface AggregationValueInterface
{
    /**
     * Get aggregation
     *
     * @return string|array
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Get metrics
     *
     * @return mixed[]
     * @since 2.0.0
     */
    public function getMetrics();
}
