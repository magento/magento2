<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation\Builder;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic;
use Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider;
use Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DynamicTest extends TestCase
{
    /**
     * @var Dynamic
     */
    private $model;

    /**
     * @var BucketInterface|MockObject
     */
    protected $requestBuckedInterface;

    /**
     * @var DataProviderInterface|MockObject
     */
    protected $dataProviderContainer;

    /**
     * @var AlgorithmInterface|MockObject
     */
    protected $algorithmRepository;

    /**
     * @var EntityStorageFactory|MockObject
     */
    protected $entityStorageFactory;

    /**
     * @var AlgorithmInterface|MockObject
     */
    protected $algorithmInterface;

    /**
     * @var DynamicBucket|MockObject
     */
    protected $bucket;

    /**
     * @var EntityStorage|MockObject
     */
    protected $entityStorage;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->requestBuckedInterface = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProviderContainer = $this
            ->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->algorithmRepository = $this->getMockBuilder(
            Repository::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityStorageFactory = $this->getMockBuilder(
            EntityStorageFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->algorithmInterface = $this
            ->getMockBuilder(AlgorithmInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->bucket = $this->getMockBuilder(DynamicBucket::class)
            ->setMethods(['getMethod'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityStorage = $this->getMockBuilder(EntityStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityStorageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->entityStorage);

        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $objectManagerHelper->getObject(
            Dynamic::class,
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
