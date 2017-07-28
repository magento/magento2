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
 * @api
 * @since 2.0.0
 */
class Builder
{
    /**
     * @var DataProviderContainer
     * @since 2.0.0
     */
    private $dataProviderContainer;

    /**
     * @var Builder\Container
     * @since 2.0.0
     */
    private $aggregationContainer;

    /**
     * @var EntityMetadata
     * @since 2.0.0
     */
    private $entityMetadata;

    /**
     * @var Resource
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var AggregationResolverInterface
     * @since 2.1.0
     */
    private $aggregationResolver;

    /**
     * @param ResourceConnection $resource
     * @param DataProviderContainer $dataProviderContainer
     * @param AggregationContainer $aggregationContainer
     * @param EntityMetadata $entityMetadata
     * @param AggregationResolverInterface $aggregationResolver
     * @since 2.0.0
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
     * @param RequestInterface $request
     * @param Table $documentsTable
     * @param array $documents
     * @return array
     * @since 2.0.0
     */
    public function build(RequestInterface $request, Table $documentsTable, array $documents = [])
    {
        return $this->processAggregations($request, $documentsTable, $documents);
    }

    /**
     * @param RequestInterface $request
     * @param Table $documentsTable
     * @param array $documents
     * @return array
     * @since 2.0.0
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
     * Extract document ids
     *
     * @param array $documents
     * @return array
     * @since 2.1.0
     */
    private function extractDocumentIds(array $documents)
    {
        return $documents ? array_keys($documents) : [];
    }

    /**
     * Get document ids
     *
     * @param Table $documentsTable
     * @return array
     * @deprecated 2.1.0 Added for backward compatibility
     * @since 2.1.0
     */
    private function getDocumentIds(Table $documentsTable)
    {
        $select = $this->getConnection()
            ->select()
            ->from($documentsTable->getName(), TemporaryStorage::FIELD_ENTITY_ID);
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Get Connection
     *
     * @return AdapterInterface
     * @since 2.1.0
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
