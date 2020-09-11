<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\AggregationFactory;
use Magento\Elasticsearch\SearchAdapter\DocumentFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    /**
     * @var ResponseFactory|MockObject
     */
    private $model;

    /**
     * @var DocumentFactory|MockObject
     */
    private $documentFactory;

    /**
     * @var AggregationFactory|MockObject
     */
    private $aggregationFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->documentFactory = $this->getMockBuilder(DocumentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregationFactory = $this->getMockBuilder(
            AggregationFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            ResponseFactory::class,
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
        $rawResponse = ['documents' => $documents, 'aggregations' => $aggregations, 'total' => 2];

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
            'total' => 2,
        ];

        $this->documentFactory->expects($this->at(0))->method('create')
            ->with($documents[0])
            ->willReturn('document1');
        $this->documentFactory->expects($this->at(1))->method('create')
            ->with($documents[1])
            ->willReturn('document2');

        $this->aggregationFactory->expects($this->at(0))->method('create')
            ->with($exceptedResponse['aggregations'])
            ->willReturn('aggregationsData');

        $this->objectManager->expects($this->once())->method('create')
            ->with(
                QueryResponse::class,
                [
                    'documents' => ['document1', 'document2'],
                    'aggregations' => 'aggregationsData',
                    'total' => 2
                ]
            )
            ->willReturn('QueryResponseObject');

        $result = $this->model->create($rawResponse);
        $this->assertEquals('QueryResponseObject', $result);
    }
}
