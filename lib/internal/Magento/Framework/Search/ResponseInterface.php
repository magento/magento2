<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations();
}
