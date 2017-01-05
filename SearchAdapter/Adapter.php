<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;

/**
 * Elasticsearch Search Adapter
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
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var AggregationBuilder
     */
    protected $aggregationBuilder;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    /**
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        $client = $this->connectionManager->getConnection();
        $query = $this->mapper->buildQuery($request);
        $rawResponse = $client->query($query);
        $rawDocuments = isset($rawResponse['hits']['hits']) ? $rawResponse['hits']['hits'] : [];
        return $this->responseFactory->create([
            'documents' => $rawDocuments,
            'aggregations' => $this->aggregationBuilder->build($request, $rawResponse),
        ]);
    }
}
