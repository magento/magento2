<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\BucketBuilderInterface;

class Builder
{
    /**
     * @var DataProviderInterface[]
     */
    protected $dataProviderContainer;

    /**
     * @var BucketBuilderInterface[]
     */
    protected $aggregationContainer;

    /**
     * @param  DataProviderInterface[] $dataProviderContainer
     * @param  BucketBuilderInterface[] $aggregationContainer
     */
    public function __construct(
        array $dataProviderContainer,
        array $aggregationContainer
    ) {
        $this->dataProviderContainer = $dataProviderContainer;
        $this->aggregationContainer = $aggregationContainer;
    }

    /**
     * @param RequestInterface $request
     * @param array $queryResult
     * @return array
     */
    public function build(RequestInterface $request, array $queryResult)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();
        $dataProvider = $this->dataProviderContainer[$request->getIndex()];
        foreach ($buckets as $bucket) {
            $aggregationBuilder = $this->aggregationContainer[$bucket->getType()];
            $aggregations[$bucket->getName()] = $aggregationBuilder->build(
                $bucket,
                $request->getDimensions(),
                $queryResult,
                $dataProvider
            );
        }

        return $aggregations;
    }
}
