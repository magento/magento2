<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Test\Unit\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientException;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\Search\Request\EmptyRequestDataException;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\OpenSearch\Model\SearchClient;
use Magento\OpenSearch\SearchAdapter\Adapter;
use Magento\OpenSearch\SearchAdapter\Mapper;
use Magento\Search\Model\Search\PageSizeProvider;
use OpenSearch\Common\Exceptions\BadRequest400Exception;
use OpenSearch\Common\Exceptions\Missing404Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdapterTest extends TestCase
{
    /**
     * Mapper instance
     *
     * @var Mapper|MockObject
     */
    private Mapper $mapper;

    /**
     * @var ResponseFactory|MockObject
     */
    private ResponseFactory $responseFactory;

    /**
     * @var ConnectionManager|MockObject
     */
    private ConnectionManager $connectionManager;

    /**
     * @var AggregationBuilder|MockObject
     */
    private AggregationBuilder $aggregationBuilder;

    /**
     * @var QueryContainerFactory|MockObject
     */
    private QueryContainerFactory $queryContainerFactory;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var PageSizeProvider|MockObject
     */
    private PageSizeProvider $pageSizeProvider;

    /**
     * @var Adapter
     */
    private Adapter $adapter;

    protected function setUp(): void
    {
        $this->mapper = $this->createMock(Mapper::class);
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->connectionManager = $this->createMock(ConnectionManager::class);
        $this->aggregationBuilder = $this->createMock(AggregationBuilder::class);
        $this->queryContainerFactory = $this->createMock(QueryContainerFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pageSizeProvider = $this->createMock(PageSizeProvider::class);

        $this->adapter = new Adapter(
            $this->connectionManager,
            $this->mapper,
            $this->responseFactory,
            $this->aggregationBuilder,
            $this->queryContainerFactory,
            $this->logger,
            $this->pageSizeProvider
        );
    }

    /**
     * @param $exception
     * @param $throws
     * @return void
     * @throws ClientException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[DataProvider('exceptionDataProvider')]
    public function testQuery($exception, $throws): void
    {
        $request = $this->createMock(RequestInterface::class);
        $query = ['query'];
        $client = $this->createMock(SearchClient::class);
        $client->expects($this->once())->method('query')->willThrowException(new $exception('error'));
        $this->connectionManager->expects($this->once())->method('getConnection')->willReturn($client);
        $this->mapper->expects($this->once())->method('buildQuery')->with($request)->willReturn($query);

        $this->expectException($throws);
        $this->adapter->query($request);
    }

    /**
     * A page offset past the last hit must not page through the rest of the result set.
     *
     * @return void
     */
    public function testQueryStopsWhenOffsetIsPastLastHit(): void
    {
        $maxPageSize = 3;
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getFrom')->willReturn(10);
        $request->expects($this->atLeastOnce())->method('getSize')->willReturn(2);

        $this->pageSizeProvider->expects($this->atLeastOnce())->method('getMaxPageSize')->willReturn($maxPageSize);
        $this->mapper->expects($this->once())->method('buildQuery')->with($request)->willReturn([
            'index' => 'magento2_product_1',
            'body' => [],
        ]);

        $client = $this->createMock(SearchClient::class);
        $client->expects($this->once())
            ->method('openPointInTime')
            ->willReturn(['pit_id' => 'pit-1']);
        $client->expects($this->once())->method('closePointInTime');
        $client->expects($this->exactly(3))
            ->method('query')
            ->willReturnCallback(function (array $query) use ($maxPageSize): array {
                $size = $query['body']['size'];
                $this->assertLessThanOrEqual($maxPageSize, $size);
                static $call = 0;
                $call++;
                if ($call < 3) {
                    return $this->openSearchHits($size, 6);
                }
                return $this->openSearchHits(0, 6);
            });
        $this->connectionManager->expects($this->atLeastOnce())->method('getConnection')->willReturn($client);
        $this->logger->expects($this->never())->method('critical');

        $queryContainer = $this->createStub(QueryContainer::class);
        $this->queryContainerFactory->expects($this->once())->method('create')->willReturn($queryContainer);
        $this->aggregationBuilder->expects($this->once())->method('setQuery')->with($queryContainer)->willReturnSelf();
        $this->aggregationBuilder->expects($this->once())->method('build')->willReturn([]);

        $queryResponse = $this->createStub(QueryResponse::class);
        $this->responseFactory->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $response): bool {
                return $response['documents'] === [] && $response['total'] === 6;
            }))
            ->willReturn($queryResponse);

        $this->assertSame($queryResponse, $this->adapter->query($request));
    }

    /**
     * A failing deep-page chunk query must still close the point in time.
     *
     * @return void
     */
    public function testDeepPageClosesPointInTimeWhenChunkQueryFails(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getFrom')->willReturn(10);
        $request->expects($this->atLeastOnce())->method('getSize')->willReturn(2);

        $this->pageSizeProvider->expects($this->atLeastOnce())->method('getMaxPageSize')->willReturn(3);
        $this->mapper->expects($this->once())->method('buildQuery')->with($request)->willReturn([
            'index' => 'magento2_product_1',
            'body' => [],
        ]);

        $client = $this->createMock(SearchClient::class);
        $client->expects($this->once())
            ->method('openPointInTime')
            ->willReturn(['pit_id' => 'pit-1']);
        $client->expects($this->once())
            ->method('closePointInTime')
            ->with(['body' => ['pit_id' => ['pit-1']]]);
        $client->expects($this->once())
            ->method('query')
            ->willThrowException(new \RuntimeException('error'));
        $this->connectionManager->expects($this->atLeastOnce())->method('getConnection')->willReturn($client);
        $this->logger->expects($this->once())->method('critical');

        $this->expectException(ClientException::class);
        $this->adapter->query($request);
    }

    /**
     * Build an OpenSearch hits payload.
     *
     * @param int $count
     * @param int $total
     * @return array
     */
    private function openSearchHits(int $count, int $total): array
    {
        $hits = [];
        for ($i = 0; $i < $count; $i++) {
            $hits[] = ['_id' => (string)$i, 'sort' => [$i]];
        }

        return [
            'hits' => [
                'total' => ['value' => $total],
                'hits' => $hits,
            ],
        ];
    }

    /**
     * @return \class-string[][]
     */
    public static function exceptionDataProvider(): array
    {
        return [
            'missing_exception' => [
                'exception' => Missing404Exception::class,
                'throws' => EmptyRequestDataException::class
            ],
            'bad_request_exception' => [
                'exception' => BadRequest400Exception::class,
                'throws' => EmptyRequestDataException::class
            ],
            'client_exception' => [
                'exception' => \Exception::class,
                'throws' => ClientException::class
            ],
        ];
    }
}
