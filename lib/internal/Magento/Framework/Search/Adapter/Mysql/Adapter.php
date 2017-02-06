<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * Response Factory
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param ResourceConnection $resource
     * @param AggregationBuilder $aggregationBuilder
     * @param TemporaryStorageFactory $temporaryStorageFactory
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
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
