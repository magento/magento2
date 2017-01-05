<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation\Builder;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DynamicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dynamic
     */
    private $model;

    /**
     * @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuckedInterface;

    /**
     * @var \Magento\Framework\Search\Dynamic\DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProviderContainer;

    /**
     * @var \Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $algorithmRepository;

    /**
     * @var \Magento\Framework\Search\Dynamic\EntityStorageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityStorageFactory;

    /**
     * @var \Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $algorithmInterface;

    /**
     * @var \Magento\Framework\Search\Request\Aggregation\DynamicBucket|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bucket;

    /**
     * @var \Magento\Framework\Search\Dynamic\EntityStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityStorage;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requestBuckedInterface = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProviderContainer = $this
            ->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->algorithmRepository = $this->getMockBuilder(
            \Magento\Framework\Search\Dynamic\Algorithm\Repository::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityStorageFactory = $this->getMockBuilder(
            \Magento\Framework\Search\Dynamic\EntityStorageFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->algorithmInterface = $this
            ->getMockBuilder(\Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\Aggregation\DynamicBucket::class)
            ->setMethods(['getMethod'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityStorage = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\EntityStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityStorageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->entityStorage);

        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic::class,
            [
                'algorithmRepository' => $this->algorithmRepository,
                'entityStorageFactory' => $this->entityStorageFactory,
            ]
        );
    }

    /**
     * Test build() method
     */
    public function testBuild()
    {
        $dimensions = [
            'scope' => [
                'name' => 'scope',
                'value' => 1,
            ],
        ];

        $queryResult = [
            'took' => 1,
            'timed_out' => false,
            '_shards' => [],
            'hits' => [
                'total' => 1,
                'max_score' => 1,
                'hits' => [
                    [
                        '_id' => 1,
                    ]
                ],
            ],
            'aggregations' => [],
        ];

        $this->bucket->expects($this->once())
            ->method('getMethod')
            ->willReturn('auto');

        $this->algorithmRepository->expects($this->any())
            ->method('get')
            ->with('auto', ['dataProvider' => $this->dataProviderContainer])
            ->willReturn($this->algorithmInterface);

        $this->algorithmInterface->expects($this->once())
            ->method('getItems')
            ->with(
                $this->bucket,
                $dimensions,
                $this->entityStorage
            )
            ->willReturn([
                0 => [
                    'from' => '',
                    'to' => 22,
                    'count' => 2,
                ],
                1 => [
                    'from' => 22,
                    'to' => 24,
                    'count' => 4,
                ],
                2 => [
                    'from' => 24,
                    'to' => '',
                    'count' => 6,
                ],
            ]);

        $this->model->build(
            $this->bucket,
            $dimensions,
            $queryResult,
            $this->dataProviderContainer
        );
    }
}
