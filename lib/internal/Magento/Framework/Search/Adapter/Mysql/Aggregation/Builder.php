<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container as AggregationContainer;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\RequestInterface;

class Builder
{
    /**
     * @var DataProviderContainer
     */
    private $dataProviderContainer;

    /**
     * @var Builder\Container
     */
    private $aggregationContainer;

    /**
     * @var EntityMetadata
     */
    private $entityMetadata;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     * @param DataProviderContainer $dataProviderContainer
     * @param AggregationContainer $aggregationContainer
     * @param EntityMetadata $entityMetadata
     */
    public function __construct(
        ResourceConnection $resource,
        DataProviderContainer $dataProviderContainer,
        AggregationContainer $aggregationContainer,
        EntityMetadata $entityMetadata
    ) {
        $this->dataProviderContainer = $dataProviderContainer;
        $this->aggregationContainer = $aggregationContainer;
        $this->entityMetadata = $entityMetadata;
        $this->resource = $resource;
    }

    /**
     * @param RequestInterface $request
     * @param Table|Select|Table $documentsTable
     * @return array
     */
    public function build(RequestInterface $request, Table $documentsTable)
    {
        return $this->processAggregations($request, $documentsTable);
    }

    /**
     * @param RequestInterface $request
     * @param Table $documentsTable
     * @return array
     */
    private function processAggregations(RequestInterface $request, Table $documentsTable)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();
        $dataProvider = $this->dataProviderContainer->get($request->getIndex());
        foreach ($buckets as $bucket) {
            $aggregationBuilder = $this->aggregationContainer->get($bucket->getType());
            $aggregations[$bucket->getName()] = $aggregationBuilder->build(
                $dataProvider,
                $request->getDimensions(),
                $bucket,
                $documentsTable
            );
        }

        return $aggregations;
    }
}
