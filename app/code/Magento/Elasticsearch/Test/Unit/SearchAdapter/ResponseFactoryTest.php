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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->documentFactory = $this->getMockBuilder(DocumentFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregationFactory = $this->getMockBuilder(AggregationFactory::class)
            ->onlyMethods(['create'])
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

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $documents = [
            [
                'title' => 'oneTitle',
                'description' => 'oneDescription',
                'fields' => [
                    '_id' => ['1']
                ]
            ],
            [
                'title' => 'twoTitle',
                'description' => 'twoDescription',
                'fields' => [
                    '_id' => ['2']
                ]
            ]
        ];
        $modifiedDocuments = [
            [
                'title' => 'oneTitle',
                'description' => 'oneDescription',
                '_id' => '1'
            ],
            [
                'title' => 'twoTitle',
                'description' => 'twoDescription',
                '_id' => '2'
            ]
        ];
        $aggregations = [
            'aggregation1' => [
                'itemOne' => 10,
                'itemTwo' => 20
            ],
            'aggregation2' => [
                'itemOne' => 5,
                'itemTwo' => 45
            ]
        ];
        $rawResponse = ['documents' => $documents, 'aggregations' => $aggregations, 'total' => 2];

        $exceptedResponse = [
            'documents' => [
                [
                    ['name' => 'title', 'value' => 'oneTitle'],
                    ['name' => 'description', 'value' => 'oneDescription']
                ],
                [
                    ['name' => 'title', 'value' => 'twoTitle'],
                    ['name' => 'description', 'value' => 'twoDescription']
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
            'total' => 2
        ];

        $this->documentFactory
            ->method('create')
            ->withConsecutive([$modifiedDocuments[0]], [$modifiedDocuments[1]])
            ->willReturnOnConsecutiveCalls('document1', 'document2');

        $this->aggregationFactory
            ->method('create')
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
