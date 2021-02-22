<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Adapter;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AdapterTest
 */
class AdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueryContainerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryContainerFactory;

    /**
     * @var Adapter
     */
    protected $model;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\ConnectionManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionManager;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\Mapper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mapper;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\ResponseFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aggregationBuilder;

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
    {
        $this->connectionManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\Mapper::class)
            ->setMethods([
                'buildQuery',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseFactory = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ResponseFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\Search\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregationBuilder = $this->getMockBuilder(
            \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder::class
        )
            ->setMethods([
                'build',
                'setQuery'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryContainerFactory = $this->getMockBuilder(QueryContainerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\SearchAdapter\Adapter::class,
            [
                'connectionManager' => $this->connectionManager,
                'mapper' => $this->mapper,
                'responseFactory' => $this->responseFactory,
                'aggregationBuilder' => $this->aggregationBuilder,
                'queryContainerFactory' => $this->queryContainerFactory,
            ]
        );
    }

    /**
     * Test query() method
     *
     * @return void
     */
    public function testQuery()
    {
        $searchQuery = [
            'index' => 'indexName',
            'type' => 'product',
            'body' => [
                'from' => 0,
                'size' => 1000,
                'fields' => ['_id', '_score'],
                'query' => [],
            ],
        ];

        $client = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionManager->expects($this->once())
            ->method('getConnection')
            ->willReturn($client);

        $queryContainer = $this->getMockBuilder(QueryContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryContainerFactory->expects($this->once())
            ->method('create')
            ->with(['query' => $searchQuery])
            ->willReturn($queryContainer);

        $this->aggregationBuilder->expects($this->once())
            ->method('setQuery')
            ->with($queryContainer);

        $this->mapper->expects($this->once())
            ->method('buildQuery')
            ->with($this->request)
            ->willReturn($searchQuery);

        $client->expects($this->once())
            ->method('query')
            ->willReturn([
                'hits' => [
                    'total' => 1,
                    'hits' => [
                        [
                            '_index' => 'indexName',
                            '_type' => 'product',
                            '_id' => 1,
                            '_score' => 1.0,
                        ],
                    ],
                ],
            ]);
        $this->aggregationBuilder->expects($this->once())
            ->method('build')
            ->willReturn($client);

        $this->model->query($this->request);
    }
}
