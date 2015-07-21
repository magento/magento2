<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * MySQL Search Adapter
 */
class Adapter implements AdapterInterface
{
    const TEMPORARY_TABLE_NAME = 'search_tmp';

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
     * @var \Magento\Framework\App\Resource
     */
    private $resource;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param Resource $resource
     * @param AggregationBuilder $aggregationBuilder
     */
    public function __construct(
        Mapper $mapper,
        ResponseFactory $responseFactory,
        Resource $resource,
        AggregationBuilder $aggregationBuilder
    ) {
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->resource = $resource;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request)
    {
        $query = $this->mapper->buildQuery($request);
        $table = $this->storeDocuments($query);

        $documents = $this->getDocuments($table);

        $aggregations = $this->aggregationBuilder->build($request, $table);
        $response = [
            'documents' => $documents,
            'aggregations' => $aggregations,
        ];
        return $this->responseFactory->create($response);
    }

    /**
     * @param Select $select
     * @return Table
     */
    private function storeDocuments(Select $select)
    {
        $table = $this->createTemporaryTable();
        $this->getConnection()->query($this->getConnection()->insertFromSelect($select, $table->getName()));
        return $table;
    }

    /**
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTemporaryTable()
    {
        $connection = $this->getConnection();
        $table = $connection->newTable($this->resource->getTableName(self::TEMPORARY_TABLE_NAME));
        $table->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Entity ID'
        );
        $table->addColumn(
            'relevance',
            Table::TYPE_FLOAT,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Relevance'
        );
        $connection->createTemporaryTable($table);
        return $table;
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
        $select = $this->getConnection()->select();
        $select->from($table->getName(), ['entity_id', 'relevance']);
        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }
}
