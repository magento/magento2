<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\ElasticAdapter\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientException;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Elasticsearch8\Model\Client\Elasticsearch;
use Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Mapper;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Adapter;

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

        $this->adapter = new Adapter(
            $this->connectionManager,
            $this->mapper,
            $this->responseFactory,
            $this->aggregationBuilder,
            $this->queryContainerFactory,
            $this->logger
        );
    }

    /**
     * @return void
     * @throws ClientException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testQueryException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $query = ['query'];
        $this->mapper->expects($this->once())->method('buildQuery')->with($request)->willReturn($query);
        $this->aggregationBuilder->expects($this->once())->method('setQuery');
        $this->queryContainerFactory->expects($this->once())
            ->method('create')
            ->with(['query' => $query])
            ->willReturn($this->createMock(QueryContainer::class));
        $client = $this->createMock(Elasticsearch::class);
        $exception = new \Exception('error');
        $client->expects($this->once())->method('query')->willThrowException($exception);
        $this->connectionManager->expects($this->once())->method('getConnection')->willReturn($client);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->expectException(ClientException::class);
        $this->adapter->query($request);
    }
}
