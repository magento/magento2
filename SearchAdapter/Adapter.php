<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;

/**
 * Elasticsearch Search Adapter
 * @since 2.1.0
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     *
     * @var Mapper
     * @since 2.1.0
     */
    protected $mapper;

    /**
     * Response Factory
     *
     * @var ResponseFactory
     * @since 2.1.0
     */
    protected $responseFactory;

    /**
     * @var ConnectionManager
     * @since 2.1.0
     */
    protected $connectionManager;

    /**
     * @var AggregationBuilder
     * @since 2.1.0
     */
    protected $aggregationBuilder;

    /**
     * @var QueryContainerFactory
     * @since 2.2.0
     */
    private $queryContainerFactory;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param QueryContainerFactory $queryContainerFactory
     * @since 2.1.0
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        QueryContainerFactory $queryContainerFactory = null
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryContainerFactory = $queryContainerFactory
            ?: ObjectManager::getInstance()->get(QueryContainerFactory::class);
    }

    /**
     * @param RequestInterface $request
     * @return QueryResponse
     * @since 2.1.0
     */
    public function query(RequestInterface $request)
    {
        $client = $this->connectionManager->getConnection();
        $aggregationBuilder = $this->aggregationBuilder;

        $query = $this->mapper->buildQuery($request);
        $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $query]));
        $rawResponse = $client->query($query);

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
