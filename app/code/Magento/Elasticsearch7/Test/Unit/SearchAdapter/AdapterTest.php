<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Elasticsearch7\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch7\SearchAdapter\Adapter;
use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Search\Model\Search\PageSizeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdapterTest extends TestCase
{
    /**
     * @var ConnectionManager|MockObject
     */
    private $connectionManagerMock;

    /**
     * @var Mapper|MockObject
     */
    private $mapperMock;

    /**
     * @var ResponseFactory|MockObject
     */
    private $responseFactoryMock;

    /**
     * @var AggregationBuilder|MockObject
     */
    private $aggregationBuilderMock;

    /**
     * @var QueryContainerFactory|MockObject
     */
    private $queryContainerFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var PageSizeProvider|MockObject
     */
    private $pageSizeProviderMock;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var ElasticsearchClient|MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $this->connectionManagerMock = $this->createMock(ConnectionManager::class);
        $this->mapperMock = $this->createMock(Mapper::class);
        $this->responseFactoryMock = $this->createMock(ResponseFactory::class);
        $this->aggregationBuilderMock = $this->createMock(AggregationBuilder::class);
        $this->queryContainerFactoryMock = $this->createMock(QueryContainerFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->pageSizeProviderMock = $this->createMock(PageSizeProvider::class);
        $this->adapter = new Adapter(
            $this->connectionManagerMock,
            $this->mapperMock,
            $this->responseFactoryMock,
            $this->aggregationBuilderMock,
            $this->queryContainerFactoryMock,
            $this->loggerMock,
            $this->pageSizeProviderMock
        );

        $this->connectionMock = $this->createMock(ElasticsearchClient::class);
        $this->connectionManagerMock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->pageSizeProviderMock->method('getMaxPageSize')
            ->willReturn(10000);
    }

    /**
     * @dataProvider queryDataProvider
     * @param int $from
     * @param int $size
     * @return void
     */
    public function testQuery(int $from, int $size): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getFrom')
            ->willReturn($from);
        $requestMock->method('getSize')
            ->willReturn($size);

        $query = [
            'index' => 'magento_product',
            'body' => [
                'from' => $from,
                'size' => $size,
                'query' => [],
            ],
        ];
        $this->mapperMock->expects($this->once())
            ->method('buildQuery')
            ->with($requestMock)
            ->willReturn($query);

        $response = [
            'hits' => [
                'total' => [
                    'value' => 2,
                ],
                'hits' => [
                    [
                        'fields' => ['_id' => ['111']],
                    ],
                    [
                        'fields' => ['_id' => ['222']],
                    ],
                ],
            ],
        ];
        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($response);
        $this->connectionMock->expects($this->never())
            ->method('openPointInTime');
        $this->connectionMock->expects($this->never())
            ->method('closePointInTime');

        $queryResponseMock = $this->mockQueryResponse($requestMock, $query, $response);
        $queryResponse = $this->adapter->query($requestMock);
        $this->assertEquals($queryResponseMock, $queryResponse);
    }

    public function queryDataProvider(): array
    {
        return [
            [0, 2],
            [0, 10000],
            [9998, 2],
        ];
    }

    /**
     * @dataProvider queryExceedingPageSizeLimitDataProvider
     * @param int $from
     * @param int $size
     * @return void
     */
    public function testQueryExceedingPageSizeLimit(int $from, int $size): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getFrom')->willReturn($from);
        $requestMock->method('getSize')->willReturn($size);

        $query = [
            'index' => 'magento_product',
            'body' => [
                'from' => $from,
                'size' => $size,
                'sort' => [
                    [ '_score' => ['order' => 'asc']],
                ],
                'query' => [],
            ],
        ];
        $this->mapperMock->expects($this->once())->method('buildQuery')->with($requestMock)->willReturn($query);

        $pit = ['id' => 'abc'];
        $this->connectionMock->expects($this->once())
            ->method('openPointInTime')
            ->with(
                [
                    'index' => 'magento_product',
                    'keep_alive' => '1m',
                ]
            )
            ->willReturn($pit);
        $firstQuery = [
            'body' => [
                'from' => 0,
                'size' => $from,
                'sort' => [
                    [ '_score' => ['order' => 'asc']],
                ],
                'query' => [],
                'pit' => $pit,
            ],
        ];
        $finalQuery = [
            'body' => [
                'from' => 0,
                'size' => $size,
                'sort' => [
                    [ '_score' => ['order' => 'asc']],
                ],
                'query' => [],
                'pit' => $pit,
                'search_after' => [2.0],
            ],
        ];
        $firstResponse = [
            'hits' => [
                'total' => [
                    'value' => 2,
                ],
                'hits' => [
                    [
                        'fields' => ['_id' => ['111']],
                        'sort' => [1.0],
                    ],
                    [
                        'fields' => ['_id' => ['222']],
                        'sort' => [2.0],
                    ],
                ],
            ],
        ];
        $finalResponse = [
            'hits' => [
                'total' => [
                    'value' => 2,
                ],
                'hits' => [
                    [
                        'fields' => ['_id' => ['333']],
                        'sort' => [3.0],
                    ],
                    [
                        'fields' => ['_id' => ['444']],
                        'sort' => [4.0],
                    ],
                ],
            ],
        ];
        $this->connectionMock->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(fn ($query) => match ($query) {
                $firstQuery => $firstResponse,
                $finalQuery => $finalResponse,
            });
        $this->connectionMock->expects($this->once())->method('closePointInTime')->with(['body' => $pit]);

        $queryResponseMock = $this->mockQueryResponse($requestMock, $finalQuery, $finalResponse);
        $queryResponse = $this->adapter->query($requestMock);
        $this->assertEquals($queryResponseMock, $queryResponse);
    }

    public function queryExceedingPageSizeLimitDataProvider(): array
    {
        return [
            [10000, 2],
            [9999, 2],
        ];
    }

    /**
     * @return void
     */
    public function testQueryWithException(): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getFrom')
            ->willReturn(0);
        $requestMock->method('getSize')
            ->willReturn(2);

        $query = [
            'index' => 'magento_product',
            'body' => [
                'from' => 0,
                'size' => 2,
                'query' => [],
            ],
        ];
        $this->mapperMock->expects($this->once())
            ->method('buildQuery')
            ->with($requestMock)
            ->willReturn($query);

        $exception = new \Exception('error');
        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($query)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $response = [
            'hits' => ['hits' => []],
            'aggregations' => [
                'price_bucket' => [],
                'category_bucket' => ['buckets' => []],
            ],
        ];
        $queryResponseMock = $this->mockQueryResponse($requestMock, $query, $response);
        $queryResponse = $this->adapter->query($requestMock);
        $this->assertEquals($queryResponseMock, $queryResponse);
    }

    private function mockQueryResponse(MockObject $requestMock, array $query, array $rawResponse): MockObject
    {
        $queryContainerMock = $this->createMock(QueryContainer::class);
        $this->queryContainerFactoryMock->expects($this->once())
            ->method('create')
            ->with(['query' => $query])
            ->willReturn($queryContainerMock);
        $this->aggregationBuilderMock->expects($this->once())
            ->method('setQuery')
            ->with($queryContainerMock)
            ->willReturnSelf();
        $aggregations = [];
        $this->aggregationBuilderMock->expects($this->once())
            ->method('build')
            ->with($requestMock, $rawResponse)
            ->willReturn($aggregations);
        $queryResponseMock = $this->createMock(QueryResponse::class);
        $this->responseFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'documents' => $rawResponse['hits']['hits'],
                    'aggregations' => $aggregations,
                    'total' => count($rawResponse['hits']['hits']),
                ]
            )
            ->willReturn($queryResponseMock);

        return $queryResponseMock;
    }
}
