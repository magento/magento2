<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * MySQL Search Adapter
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
        /** @var Select $query */
        $query = $this->mapper->buildQuery($request);
        $documents = $this->executeQuery($query);

        $aggregations = $this->aggregationBuilder->build($request, $documents);
        $response = [
            'documents' => $documents,
            'aggregations' => $aggregations,
        ];
        return $this->responseFactory->create($response);
    }

    /**
     * Executes query and return raw response
     *
     * @param Select $select
     * @return array
     */
    private function executeQuery(Select $select)
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE)->fetchAssoc($select);
    }
}
