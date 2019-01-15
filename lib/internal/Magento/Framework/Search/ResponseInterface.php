<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Search Request
 */
interface ResponseInterface extends \IteratorAggregate, \Countable
{
    /**
     * Return Aggregation Collection
     *
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations();
}
