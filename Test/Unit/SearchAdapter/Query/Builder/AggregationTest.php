<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AggregationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Aggregation
     */
    protected $model;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestInterface;

    /**
     * @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBucketInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->fieldMapper = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterface = $this->getMockBuilder(\Magento\Framework\Search\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestBucketInterface = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation::class,
            [
                'fieldMapper' =>$this->fieldMapper,
            ]
        );
    }

    /**
     * Test build() method "dynamicBucket" with field "price"
     */
    public function testBuildDynamicPrice()
    {
        $query = [
            'index' => 'magento2',
            'type' => 'product',
            'body' => [],
        ];

        $this->requestInterface->expects($this->any())
            ->method('getAggregation')
            ->willReturn([$this->requestBucketInterface]);

        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('price');

        $this->requestBucketInterface->expects($this->any())
            ->method('getField')
            ->willReturn('price');

        $this->requestBucketInterface->expects($this->any())
            ->method('getType')
            ->willReturn('dynamicBucket');

        $this->requestBucketInterface->expects($this->any())
            ->method('getName')
            ->willReturn('price_bucket');

        $this->model->build($this->requestInterface, $query);
    }

    /**
     * Test build() method "dynamicBucket"
     */
    public function testBuildDynamic()
    {
        $query = [
            'index' => 'magento2',
            'type' => 'product',
            'body' => [],
        ];

        $this->requestInterface->expects($this->any())
            ->method('getAggregation')
            ->willReturn([$this->requestBucketInterface]);

        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('field');

        $this->requestBucketInterface->expects($this->any())
            ->method('getField')
            ->willReturn('field');

        $this->requestBucketInterface->expects($this->any())
            ->method('getType')
            ->willReturn('dynamicBucket');

        $this->requestBucketInterface->expects($this->any())
            ->method('getName')
            ->willReturn('price_bucket');

        $this->model->build($this->requestInterface, $query);
    }

    /**
     * Test build() method "dynamicBucket"
     */
    public function testBuildTerm()
    {
        $query = [
            'index' => 'magento2',
            'type' => 'product',
            'body' => [],
        ];

        $this->requestInterface->expects($this->any())
            ->method('getAggregation')
            ->willReturn([$this->requestBucketInterface]);

        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('price');

        $this->requestBucketInterface->expects($this->any())
            ->method('getField')
            ->willReturn('price');

        $this->requestBucketInterface->expects($this->any())
            ->method('getType')
            ->willReturn('termBucket');

        $this->requestBucketInterface->expects($this->any())
            ->method('getName')
            ->willReturn('price_bucket');

        $this->model->build($this->requestInterface, $query);
    }
}
