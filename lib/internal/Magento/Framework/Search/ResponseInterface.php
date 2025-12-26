<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Search;

/**
 * Search Request
 *
 * @api
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
