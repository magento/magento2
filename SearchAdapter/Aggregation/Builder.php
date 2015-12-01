<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation;

use Magento\Framework\Search\RequestInterface;

class Builder
{
    /**
     * @param RequestInterface $request
     * @param array $queryResult
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(
        RequestInterface $request,
        array $queryResult
    ) {
        $aggregations = [];
        $buckets = $request->getAggregation();
        foreach ($buckets as $bucket) {
            $aggregations[$bucket->getName()] = [];
        }

        return $aggregations;
    }
}
