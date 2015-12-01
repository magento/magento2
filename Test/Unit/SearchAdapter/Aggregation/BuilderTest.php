<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
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
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->model = new Builder();
    }

    /**
     * Test build() method
     */
    public function testBuild()
    {
        $this->requestInterface = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestBuckedInterface = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterface->expects($this->once())
            ->method('getAggregation')
            ->willReturn([$this->requestBuckedInterface]);

        $this->requestBuckedInterface->expects($this->any())
            ->method('getName')
            ->willReturn('price_bucket');

        $this->assertEquals(
            [
                'price_bucket' => [],
            ],
            $this->model->build($this->requestInterface, [])
        );
    }
}
