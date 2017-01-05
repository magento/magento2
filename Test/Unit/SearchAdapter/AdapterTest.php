<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Adapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AdapterTest
 */
class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter
     */
    protected $model;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionManager;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapper;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aggregationBuilder;

    /**
     * Setup method
     * @return void
     */
    protected function setUp()
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
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\SearchAdapter\Adapter::class,
            [
                'connectionManager' => $this->connectionManager,
                'mapper' => $this->mapper,
                'responseFactory' => $this->responseFactory,
                'aggregationBuilder' => $this->aggregationBuilder
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
        $client = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionManager->expects($this->once())
            ->method('getConnection')
            ->willReturn($client);

        $this->mapper
            ->expects($this->once())
            ->method('buildQuery')
            ->with($this->request)
            ->willReturn([
                'index' => 'indexName',
                'type' => 'product',
                'body' => [
                    'from' => 0,
                    'size' => 1000,
                    'fields' => ['_id', '_score'],
                    'query' => [],
                ],
            ]);

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
