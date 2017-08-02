<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * MySQL Search Adapter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     *
     * @var Mapper
     * @since 2.0.0
     */
    protected $mapper;

    /**
     * Response Factory
     *
     * @var ResponseFactory
     * @since 2.0.0
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var AggregationBuilder
     * @since 2.0.0
     */
    private $aggregationBuilder;

    /**
     * @var TemporaryStorageFactory
     * @since 2.0.0
     */
    private $temporaryStorageFactory;

    /**
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param ResourceConnection $resource
     * @param AggregationBuilder $aggregationBuilder
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @since 2.0.0
     */
    public function __construct(
        Mapper $mapper,
        ResponseFactory $responseFactory,
        ResourceConnection $resource,
        AggregationBuilder $aggregationBuilder,
        TemporaryStorageFactory $temporaryStorageFactory
    ) {
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->resource = $resource;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     * @since 2.0.0
     */
    public function query(RequestInterface $request)
    {
        $query = $this->mapper->buildQuery($request);
        $temporaryStorage = $this->temporaryStorageFactory->create();
        $table = $temporaryStorage->storeDocumentsFromSelect($query);

        $documents = $this->getDocuments($table);

        $aggregations = $this->aggregationBuilder->build($request, $table, $documents);
        $response = [
            'documents' => $documents,
            'aggregations' => $aggregations,
        ];
        return $this->responseFactory->create($response);
    }

    /**
     * Executes query and return raw response
     *
     * @param Table $table
     * @return array
     * @throws \Zend_Db_Exception
     * @since 2.0.0
     */
    private function getDocuments(Table $table)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($table->getName(), ['entity_id', 'score']);
        return $connection->fetchAssoc($select);
    }

    /**
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
