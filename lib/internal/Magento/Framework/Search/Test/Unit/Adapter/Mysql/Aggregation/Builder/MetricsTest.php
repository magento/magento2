<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics;
use Magento\Framework\Search\Request\Aggregation\Metric;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetricsTest extends TestCase
{
    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * @var RequestBucketInterface|MockObject
     */
    private $requestBucket;

    /**
     * @var Metric|MockObject
     */
    private $metric;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->requestBucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->setMethods(['getMetrics'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->metric = $this->getMockBuilder(Metric::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->metrics = $helper->getObject(Metrics::class);
    }

    public function testBuild()
    {
        $expectedResult = ['count' => 'count(main_table.value)'];
        $this->requestBucket->expects($this->once())->method('getMetrics')->willReturn([$this->metric]);
        $this->metric->expects($this->once())->method('getType')->willReturn('count');
        $metrics = $this->metrics->build($this->requestBucket);

        $this->assertEquals($expectedResult, $metrics);
    }
}
