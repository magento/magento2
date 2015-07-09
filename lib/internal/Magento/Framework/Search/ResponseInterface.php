<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

use Magento\Framework\Api\Search\AggregationInterface;

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
