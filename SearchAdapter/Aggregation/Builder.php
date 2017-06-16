<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\QueryContainer;
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
     * @var DataProviderFactory
     */
    private $dataProviderFactory;

    /**
     * @var QueryContainer
     */
    private $query = null;

    /**
     * @param  DataProviderInterface[] $dataProviderContainer
     * @param  BucketBuilderInterface[] $aggregationContainer
     */
    public function __construct(
        array $dataProviderContainer,
        array $aggregationContainer,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->dataProviderContainer = array_map(
            function (DataProviderInterface $dataProvider) {
                return $dataProvider;
            },
            $dataProviderContainer
        );
        $this->aggregationContainer = array_map(
            function (BucketBuilderInterface $bucketBuilder) {
                return $bucketBuilder;
            },
            $aggregationContainer
        );
        $this->dataProviderFactory = $dataProviderFactory;
    }

    /**
     * @param RequestInterface $request
     * @param array $queryResult
     * @return array
     * @throws \LogicException for the case when not required fields are filled
     */
    public function build(RequestInterface $request, array $queryResult)
    {
        if (null === $this->query) {
            throw new \LogicException('Query is the required field and must be set to the builder');
        }

        $aggregations = [];
        $buckets = $request->getAggregation();

        $dataProvider = $this->dataProviderFactory->create(
            $this->dataProviderContainer[$request->getIndex()],
            $this->query
        );
        foreach ($buckets as $bucket) {
            $bucketAggregationBuilder = $this->aggregationContainer[$bucket->getType()];
            $aggregations[$bucket->getName()] = $bucketAggregationBuilder->build(
                $bucket,
                $request->getDimensions(),
                $queryResult,
                $dataProvider
            );
        }

        $this->clean();

        return $aggregations;
    }

    /**
     * Sets the QueryContainer instance to the internal property in order to use it in build process
     *
     * @param QueryContainer $query
     * @return $this
     */
    public function setQuery(QueryContainer $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Resets an internal state of the current builder
     * @return void
     */
    private function clean()
    {
        $this->query = null;
    }
}
