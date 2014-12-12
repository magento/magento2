<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\TestFramework\Helper\ObjectManager;

class MetricsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics
     */
    private $metrics;

    /**
     * @var RequestBucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestBucket;

    /**
     * @var \Magento\Framework\Search\Request\Aggregation\Metric|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metric;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->requestBucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->setMethods(['getMetrics'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->metric = $this->getMockBuilder('Magento\Framework\Search\Request\Aggregation\Metric')
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->metrics = $helper->getObject('Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics');
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
