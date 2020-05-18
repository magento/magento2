<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregationTest extends TestCase
{
    /**
     * @var Aggregation
     */
    protected $model;

    /**
     * @var FieldMapperInterface|MockObject
     */
    protected $fieldMapper;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestInterface;

    /**
     * @var BucketInterface|MockObject
     */
    protected $requestBucketInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestInterface = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestBucketInterface = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $helper->getObject(
            Aggregation::class,
            [
                'fieldMapper' => $this->fieldMapper,
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
        $this->assertArrayHasKey(
            'size',
            $result['body']['aggregations'][$bucketName]['terms'],
            'The size have to be specified since by default, ' .
            'the terms aggregation will return only the buckets for the top ten terms ordered by the doc_count'
        );
    }
}
