<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AggregationTest extends \PHPUnit\Framework\TestCase
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
     * @var BucketInterface|\PHPUnit_Framework_MockObject_MockObject
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

        $this->requestBucketInterface = $this->getMockBuilder(BucketInterface::class)
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

        $result = $this->model->build($this->requestInterface, $query);
        $this->assertNotNull($result);
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

        $result = $this->model->build($this->requestInterface, $query);
        $this->assertNotNull($result);
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
        $bucketName = 'price_bucket';

        $this->requestInterface
            ->method('getAggregation')
            ->willReturn([$this->requestBucketInterface]);

        $this->fieldMapper
            ->method('getFieldName')
            ->willReturn('price');

        $this->requestBucketInterface
            ->method('getField')
            ->willReturn('price');

        $this->requestBucketInterface
            ->method('getType')
            ->willReturn(BucketInterface::TYPE_TERM);

        $this->requestBucketInterface
            ->method('getName')
            ->willReturn($bucketName);

        $result = $this->model->build($this->requestInterface, $query);

        $this->assertNotNull($result);
        $this->assertTrue(
            isset($result['body']['aggregations'][$bucketName]['terms']['size']),
            'The size have to be specified since by default, ' .
            'the terms aggregation will return only the buckets for the top ten terms ordered by the doc_count'
        );
    }
}
