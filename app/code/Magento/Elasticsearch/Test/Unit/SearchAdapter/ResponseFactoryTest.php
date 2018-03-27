<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentFactory;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\AggregationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregationFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->documentFactory = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\DocumentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregationFactory = $this->getMockBuilder(
            \Magento\Elasticsearch\SearchAdapter\AggregationFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\ResponseFactory::class,
            [
                'objectManager' => $this->objectManager,
                'documentFactory' => $this->documentFactory,
                'aggregationFactory' => $this->aggregationFactory
            ]
        );
    }

    public function testCreate()
    {
        $documents = [
            ['title' => 'oneTitle', 'description' => 'oneDescription'],
            ['title' => 'twoTitle', 'description' => 'twoDescription'],
        ];
        $aggregations = [
            'aggregation1' => [
                'itemOne' => 10,
                'itemTwo' => 20,
            ],
            'aggregation2' => [
                'itemOne' => 5,
                'itemTwo' => 45,
            ]
        ];
        $rawResponse = ['documents' => $documents, 'aggregations' => $aggregations];

        $exceptedResponse = [
            'documents' => [
                [
                    ['name' => 'title', 'value' => 'oneTitle'],
                    ['name' => 'description', 'value' => 'oneDescription'],
                ],
                [
                    ['name' => 'title', 'value' => 'twoTitle'],
                    ['name' => 'description', 'value' => 'twoDescription'],
                ],
            ],
            'aggregations' => [
                'aggregation1' => [
                    'itemOne' => 10,
                    'itemTwo' => 20
                ],
                'aggregation2' => [
                    'itemOne' => 5,
                    'itemTwo' => 45
                ],
            ],
        ];

        $this->documentFactory->expects($this->at(0))->method('create')
            ->with($this->equalTo($documents[0]))
            ->will($this->returnValue('document1'));
        $this->documentFactory->expects($this->at(1))->method('create')
            ->with($documents[1])
            ->will($this->returnValue('document2'));

        $this->aggregationFactory->expects($this->at(0))->method('create')
            ->with($this->equalTo($exceptedResponse['aggregations']))
            ->will($this->returnValue('aggregationsData'));

        $this->objectManager->expects($this->once())->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Response\QueryResponse::class),
                $this->equalTo(['documents' => ['document1', 'document2'], 'aggregations' => 'aggregationsData'])
            )
            ->will($this->returnValue('QueryResponseObject'));

        $result = $this->model->create($rawResponse);
        $this->assertEquals('QueryResponseObject', $result);
    }
}
