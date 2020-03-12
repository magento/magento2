<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;

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
     * @var QueryContainerFactory
     */
    private $queryContainerFactory;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param QueryContainerFactory $queryContainerFactory
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        QueryContainerFactory $queryContainerFactory
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryContainerFactory = $queryContainerFactory;
    }

    /**
     * @inheritdoc
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
                'total' => isset($rawResponse['hits']['total']) ? $rawResponse['hits']['total'] : 0
            ]
        );
        return $queryResponse;
    }
}
