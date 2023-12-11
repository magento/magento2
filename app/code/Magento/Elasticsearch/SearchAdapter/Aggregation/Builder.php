<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\BucketBuilderInterface;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * Elasticsearch aggregation builder
 */
class Builder
{
    /**
     * @var DataProviderInterface[]
     */
    private $dataProviderContainer;

    /**
     * @var BucketBuilderInterface[]
     */
    private $aggregationContainer;

    /**
     * @var DataProviderFactory
     */
    private $dataProviderFactory;

    /**
     * @var QueryContainer
     */
    private $query;

    /**
     * @param  DataProviderInterface[] $dataProviderContainer
     * @param  BucketBuilderInterface[] $aggregationContainer
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        array $dataProviderContainer,
        array $aggregationContainer,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->dataProviderContainer = array_map(
            static function (DataProviderInterface $dataProvider) {
                return $dataProvider;
            },
            $dataProviderContainer
        );
        $this->aggregationContainer = array_map(
            static function (BucketBuilderInterface $bucketBuilder) {
                return $bucketBuilder;
            },
            $aggregationContainer
        );
        $this->dataProviderFactory = $dataProviderFactory;
    }

    /**
     * Builds aggregations from the search request.
     *
     * This method iterates through buckets and builds all aggregations one by one, passing buckets and relative
     * data into bucket aggregation builders which are responsible for aggregation calculation.
     *
     * @param RequestInterface $request
     * @param array $queryResult
     * @return array
     * @throws \LogicException thrown by DataProviderFactory for validation issues
     * @see \Magento\Elasticsearch\SearchAdapter\Aggregation\DataProviderFactory
     */
    public function build(RequestInterface $request, array $queryResult)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();

        foreach ($buckets as $bucket) {
            $dataProvider = $this->dataProviderFactory->create(
                $this->dataProviderContainer[$request->getIndex()],
                $this->query,
                $bucket->getField()
            );
            $bucketAggregationBuilder = $this->aggregationContainer[$bucket->getType()];
            $aggregations[$bucket->getName()] = $bucketAggregationBuilder->build(
                $bucket,
                $request->getDimensions(),
                $queryResult,
                $dataProvider
            );
        }

        $this->query = null;

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
}
