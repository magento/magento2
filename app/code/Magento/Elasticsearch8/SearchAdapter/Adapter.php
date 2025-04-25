<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\SearchAdapter;

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
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    private Mapper $mapper;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * @var ConnectionManager
     */
    private ConnectionManager $connectionManager;

    /**
     * @var AggregationBuilder
     */
    private AggregationBuilder $aggregationBuilder;

    /**
     * @var QueryContainerFactory
     */
    private QueryContainerFactory $queryContainerFactory;

    /**
     * Empty response from Elasticsearch
     *
     * @var array
     */
    private static array $emptyRawResponse = [
        "hits" => [
            "hits" => []
        ],
        "aggregations" => [
            "price_bucket" => [],
            "category_bucket" => ["buckets" => []],
        ]
    ];

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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
    public function query(RequestInterface $request) : QueryResponse
    {
        $client = $this->connectionManager->getConnection();
        $query = $this->mapper->buildQuery($request);
        $aggregationBuilder = $this->aggregationBuilder;
        $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $query]));

        try {
            $rawResponse = $client->query($query);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            // return empty search result in case an exception is thrown from Elasticsearch
            $rawResponse = self::$emptyRawResponse;
        }

        $rawDocuments = $rawResponse['hits']['hits'] ?? [];
        return $this->responseFactory->create(
            [
                'documents' => $rawDocuments,
                'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                'total' => $rawResponse['hits']['total']['value'] ?? 0
            ]
        );
    }
}
