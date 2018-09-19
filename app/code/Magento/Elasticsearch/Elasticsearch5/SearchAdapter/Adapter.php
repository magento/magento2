<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\SearchAdapter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use \Magento\Elasticsearch\SearchAdapter\ResponseFactory;
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
     * @var \Magento\Elasticsearch\SearchAdapter\QueryContainerFactory
     */
    private $queryContainerFactory;

    /**
     * Empty response from Elasticsearch.
     *
     * @var array
     */
    private static $emptyRawResponse = [
        "hits" =>
            [
                "hits" => []
            ],
        "aggregations" =>
            [
                "price_bucket" => [],
                "category_bucket" =>
                    [
                        "buckets" => []

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
     * @param \Magento\Elasticsearch\SearchAdapter\QueryContainerFactory $queryContainerFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        \Magento\Elasticsearch\SearchAdapter\QueryContainerFactory $queryContainerFactory,
        LoggerInterface $logger = null
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
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
            ]
        );
        return $queryResponse;
    }
}
