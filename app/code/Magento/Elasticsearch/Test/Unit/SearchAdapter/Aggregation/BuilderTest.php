<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder;
use Magento\Elasticsearch\SearchAdapter\Aggregation\DataProviderFactory;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\BucketBuilderInterface;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProviderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderFactory;

    /**
     * @var Builder
     */
    private $model;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestInterface;

    /**
     * @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuckedInterface;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProviderContainer;

    /**
     * @var BucketBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aggregationContainer;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->dataProviderContainer = $this->getMockBuilder(
            \Magento\Framework\Search\Dynamic\DataProviderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationContainer = $this
            ->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\BucketBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProviderFactory = $this->getMockBuilder(
            \Magento\Elasticsearch\SearchAdapter\Aggregation\DataProviderFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder::class,
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
        $this->requestInterface = $this->getMockBuilder(\Magento\Framework\Search\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestBuckedInterface = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterface->expects($this->once())
            ->method('getIndex')
            ->willReturn('indexName');

        $dimensionMock = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
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
