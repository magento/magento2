<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Request\Mapper
     */
    protected $mapper;

    public function setUp()
    {
        $config = include __DIR__ . '/../_files/search_request_config.php';
        $request = reset($config);
        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $this->mapper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                \Magento\Framework\Search\Request\Mapper::class,
                [
                    'queries' => $request['queries'],
                    'rootQueryName' => 'suggested_search_container',
                    'filters' => $request['filters'],
                    'aggregations' => $request['aggregations'],
                ]
            );
    }

    public function testGet()
    {
        $this->assertInstanceOf(
            \Magento\Framework\Search\Request\QueryInterface::class,
            $this->mapper->getRootQuery()
        );
    }

    public function testGetBuckets()
    {
        $buckets = $this->mapper->getBuckets();
        $this->assertEquals(2, count($buckets));

        $this->assertInstanceOf(\Magento\Framework\Search\Request\Aggregation\TermBucket::class, $buckets[0]);
        $this->assertEquals('category_bucket', $buckets[0]->getName());
        $this->assertEquals('category', $buckets[0]->getField());
        $this->assertEquals(\Magento\Framework\Search\Request\BucketInterface::TYPE_TERM, $buckets[0]->getType());
        $metrics = $buckets[0]->getMetrics();
        $this->assertInstanceOf(\Magento\Framework\Search\Request\Aggregation\Metric::class, $metrics[0]);

        $this->assertInstanceOf(\Magento\Framework\Search\Request\Aggregation\RangeBucket::class, $buckets[1]);
        $this->assertEquals('price_bucket', $buckets[1]->getName());
        $this->assertEquals('price', $buckets[1]->getField());
        $this->assertEquals(\Magento\Framework\Search\Request\BucketInterface::TYPE_RANGE, $buckets[1]->getType());
        $metrics = $buckets[1]->getMetrics();
        $ranges = $buckets[1]->getRanges();
        $this->assertInstanceOf(\Magento\Framework\Search\Request\Aggregation\Metric::class, $metrics[0]);
        $this->assertInstanceOf(\Magento\Framework\Search\Request\Aggregation\Range::class, $ranges[0]);
    }
}
