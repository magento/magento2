<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container as AggregationContainer;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\RequestInterface;

/**
 * MySQL search aggregation builder.
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 * @api
 */
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
     * @var AggregationResolverInterface
     */
    private $aggregationResolver;

    /**
     * @param ResourceConnection $resource
     * @param DataProviderContainer $dataProviderContainer
     * @param AggregationContainer $aggregationContainer
     * @param EntityMetadata $entityMetadata
     * @param AggregationResolverInterface $aggregationResolver
     */
    public function __construct(
        ResourceConnection $resource,
        DataProviderContainer $dataProviderContainer,
        AggregationContainer $aggregationContainer,
        EntityMetadata $entityMetadata,
        AggregationResolverInterface $aggregationResolver
    ) {
        $this->dataProviderContainer = $dataProviderContainer;
        $this->aggregationContainer = $aggregationContainer;
        $this->entityMetadata = $entityMetadata;
        $this->resource = $resource;
        $this->aggregationResolver = $aggregationResolver;
    }

    /**
     * Build aggregations.
     *
     * @param RequestInterface $request
     * @param Table $documentsTable
     * @param array $documents
     * @return array
     */
    public function build(RequestInterface $request, Table $documentsTable, array $documents = [])
    {
        return $this->processAggregations($request, $documentsTable, $documents);
    }

    /**
     * Process aggregations.
     *
     * @param RequestInterface $request
     * @param Table $documentsTable
     * @param array $documents
     * @return array
     */
    private function processAggregations(RequestInterface $request, Table $documentsTable, $documents)
    {
        $aggregations = [];
        $documentIds = $documents ? $this->extractDocumentIds($documents) : $this->getDocumentIds($documentsTable);
        $buckets = $this->aggregationResolver->resolve($request, $documentIds);
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

    /**
     * Extract document ids.
     *
     * @param array $documents
     * @return array
     */
    private function extractDocumentIds(array $documents)
    {
        return $documents ? array_keys($documents) : [];
    }

    /**
     * Get document ids.
     *
     * @param Table $documentsTable
     * @return array
     * @deprecated 100.1.0 Added for backward compatibility
     */
    private function getDocumentIds(Table $documentsTable)
    {
        $select = $this->getConnection()
            ->select()
            ->from($documentsTable->getName(), TemporaryStorage::FIELD_ENTITY_ID);
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Get Connection.
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
