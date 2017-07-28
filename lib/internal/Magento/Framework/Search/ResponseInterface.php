<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Search Request
 * @since 2.0.0
 */
interface ResponseInterface extends \IteratorAggregate, \Countable
{
    /**
     * Return Aggregation Collection
     *
     * @return \Magento\Framework\Api\Search\AggregationInterface
     * @since 2.0.0
     */
    public function getAggregations();
}
