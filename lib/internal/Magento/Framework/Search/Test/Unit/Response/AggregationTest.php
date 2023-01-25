<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Response;

use Magento\Framework\Search\Response\Aggregation;
use Magento\Framework\Search\Response\Bucket;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregationTest extends TestCase
{
    /**
     * @var Aggregation|MockObject
     */
    private $aggregation;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $buckets = [];
        $bucket = $this->getMockBuilder(Bucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $bucket->expects($this->any())->method('getName')->willReturn('1');
        $bucket->expects($this->any())->method('getValues')->willReturn(1);
        $buckets[1] = $bucket;

        $this->aggregation = $helper->getObject(
            Aggregation::class,
            [
                'buckets' => $buckets,
            ]
        );
    }

    public function testGetIterator()
    {
        foreach ($this->aggregation as $bucket) {
            $this->assertEquals($bucket->getName(), "1");
            $this->assertEquals($bucket->getValues(), 1);
        }
    }

    public function testGetBucketNames()
    {
        $this->assertEquals(
            $this->aggregation->getBucketNames(),
            ['1']
        );
    }

    public function testGetBucket()
    {
        $bucket = $this->aggregation->getBucket('1');
        $this->assertEquals($bucket->getName(), '1');
        $this->assertEquals($bucket->getValues(), 1);
    }
}
