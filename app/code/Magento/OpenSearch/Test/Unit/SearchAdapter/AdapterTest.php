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
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\Search\Request\EmptyRequestDataException;
use Magento\Framework\Search\RequestInterface;
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
