<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Search Request
 */
interface ResponseInterface
{
    /**
     * Return Aggregation Collection
     *
     * @return AggregationInterface
     */
    public function getAggregations();
}
