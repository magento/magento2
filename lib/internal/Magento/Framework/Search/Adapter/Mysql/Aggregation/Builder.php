<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\Framework\App\Resource;
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
     * @param Resource $resource
     * @param DataProviderContainer $dataProviderContainer
     * @param Builder\Container $aggregationContainer
     * @param EntityMetadata $entityMetadata
     */
    public function __construct(
        Resource $resource,
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
     * @param int[] $documents
     * @return array
     */
    public function build(RequestInterface $request, array $documents)
    {
        $entityIds = $this->getEntityIds($documents);

        return $this->processAggregations($request, $entityIds);
    }

    /**
     * @param array $documents
     * @return int[]
     */
    private function getEntityIds($documents)
    {
        $fieldName = $this->entityMetadata->getEntityId();
        $entityIds = [];
        foreach ($documents as $document) {
            $entityIds[] = $document[$fieldName];
        }

        return $entityIds;
    }

    /**
     * @param RequestInterface $request
     * @param int[] $entityIds
     * @return array
     */
    private function processAggregations(RequestInterface $request, array $entityIds)
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
                $entityIds
            );
        }

        return $aggregations;
    }
}
