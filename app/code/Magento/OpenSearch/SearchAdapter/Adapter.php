<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Search\Model\Search\PageSizeProvider;
use Psr\Log\LoggerInterface;

/**
 * OpenSearch Search Adapter
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
     * Empty response from OpenSearch
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
     * @var PageSizeProvider
     */
    private $pageSizeProvider;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param QueryContainerFactory $queryContainerFactory
     * @param LoggerInterface $logger
     * @param PageSizeProvider $pageSizeProvider
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        QueryContainerFactory $queryContainerFactory,
        LoggerInterface $logger,
        PageSizeProvider $pageSizeProvider
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->logger = $logger;
        $this->pageSizeProvider = $pageSizeProvider;
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
        try {
            $maxPageSize = $this->pageSizeProvider->getMaxPageSize();
            if ($request->getFrom() + $request->getSize() > $maxPageSize) {
                $pit = $client->openPointInTime(
                    [
                        'index' => $query['index'],
                        'keep_alive' => '1m',
                    ]
                );
                $pitId = $pit['pit_id'];
                $query['body']['pit'] = [
                    'id' => $pitId,
                ];
                unset($query['index']);

                $query['body']['from'] = 0;
                $processed = 0;
                while ($processed < $request->getFrom()) {
                    $query['body']['size'] = min($request->getFrom() - $processed, $maxPageSize);
                    $processed += $query['body']['size'];
                    $rawResponse = $client->query($query);
                    $lastHit = end($rawResponse['hits']['hits']);
                    $query['body']['search_after'] = $lastHit['sort'];
                }
                $query['body']['size'] = $request->getSize();
            }

            $rawResponse = $client->query($query);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            // return empty search result in case an exception is thrown from OpenSearch
            $rawResponse = self::$emptyRawResponse;
        } finally {
            if (isset($pitId)) {
                $client->closePointInTime(['body' => ['pit_id' => [$pitId]]]);
            }
        }

        $rawDocuments = $rawResponse['hits']['hits'] ?? [];
        $this->aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $query]));
        $aggregations = $this->aggregationBuilder->build($request, $rawResponse);
        $queryResponse = $this->responseFactory->create(
            [
                'documents' => $rawDocuments,
                'aggregations' => $aggregations,
                'total' => $rawResponse['hits']['total']['value'] ?? 0
            ]
        );
        return $queryResponse;
    }
}
