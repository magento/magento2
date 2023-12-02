<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\ElasticAdapter\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Psr\Log\LoggerInterface;

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
    private $mapper;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var QueryContainerFactory
     */
    private $queryContainerFactory;

    /**
     * Empty response from Elasticsearch.
     *
     * @var array
     */
    private static $emptyRawResponse = [
        'hits' => [
            'hits' => []
        ],
        'aggregations' => [
            'price_bucket' => [],
            'category_bucket' => [
                'buckets' => []
            ]
        ]
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param QueryContainerFactory $queryContainerFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        QueryContainerFactory $queryContainerFactory,
        LoggerInterface $logger
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->logger = $logger;
    }

    /**
     * Search query
     *
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        $client = $this->connectionManager->getConnection();
        $aggregationBuilder = $this->aggregationBuilder;
        $query = $this->mapper->buildQuery($request);
        $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $query]));

        try {
            $rawResponse = $client->query($query);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            // return empty search result in case an exception is thrown from Elasticsearch
            $rawResponse = self::$emptyRawResponse;
        }

        $rawDocuments = isset($rawResponse['hits']['hits']) ? $rawResponse['hits']['hits'] : [];
        $queryResponse = $this->responseFactory->create(
            [
                'documents' => $rawDocuments,
                'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                'total' => isset($rawResponse['hits']['total']) ? $rawResponse['hits']['total'] : 0
            ]
        );
        return $queryResponse;
    }
}
