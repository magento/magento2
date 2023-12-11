<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\BucketBuilderInterface;
use Magento\Elasticsearch\SearchAdapter\Aggregation\DataProviderFactory;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var DataProviderFactory|MockObject
     */
    private $dataProviderFactory;

    /**
     * @var Builder
     */
    private $model;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestInterface;

    /**
     * @var BucketInterface|MockObject
     */
    protected $requestBuckedInterface;

    /**
     * @var DataProviderInterface|MockObject
     */
    protected $dataProviderContainer;

    /**
     * @var BucketBuilderInterface|MockObject
     */
    protected $aggregationContainer;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->dataProviderContainer = $this->getMockBuilder(
            DataProviderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationContainer = $this
            ->getMockBuilder(BucketBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProviderFactory = $this->getMockBuilder(
            DataProviderFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            Builder::class,
            [
                'dataProviderContainer' => ['indexName' => $this->dataProviderContainer],
                'aggregationContainer' => ['bucketType' => $this->aggregationContainer],
                'dataProviderFactory' => $this->dataProviderFactory,
            ]
        );
    }

    /**
     * Test build() method
     */
    public function testBuild()
    {
        $this->requestInterface = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestBuckedInterface = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestInterface->expects($this->once())
            ->method('getIndex')
            ->willReturn('indexName');

        $dimensionMock = $this->getMockBuilder(Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterface->expects($this->once())
            ->method('getDimensions')
            ->willReturn([$dimensionMock]);

        $this->requestInterface->expects($this->once())
            ->method('getAggregation')
            ->willReturn([$this->requestBuckedInterface]);

        $this->requestBuckedInterface->expects($this->any())
            ->method('getName')
            ->willReturn('price_bucket');

        $this->requestBuckedInterface->expects($this->any())
            ->method('getType')
            ->willReturn('bucketType');

        $this->aggregationContainer->expects($this->any())
            ->method('build')
            ->willReturn([]);

        $this->dataProviderFactory->expects($this->once())
            ->method('create')
            ->willReturnArgument(0);

        $queryContainer = $this->getMockBuilder(QueryContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setQuery($queryContainer);

        $this->assertEquals(
            [
                'price_bucket' => [],
            ],
            $this->model->build($this->requestInterface, [])
        );
    }
}
