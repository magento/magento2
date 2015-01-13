<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\TestFramework\Helper\ObjectManager;

class TermTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Term
     */
    private $term;

    /**
     * @var Metrics|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metricsBuilder;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var RequestBucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucket;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProvider;

    /**
     * SetUP method
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->metricsBuilder = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics'
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['where', 'columns', 'group'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProvider = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface'
        )
            ->setMethods(['getDataSet', 'execute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->term = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Term',
            ['metricsBuilder' => $this->metricsBuilder]
        );
    }

    /**
     * Test for method "build"
     */
    public function testBuild()
    {
        $productIds = [1, 2, 3];
        $metrics = ['count' => 'count(*)'];

        $this->select->expects($this->once())
            ->method('where')
            ->withConsecutive(
                ['main_table.entity_id IN (?)', $productIds]
            );
        $this->select->expects($this->once())
            ->method('columns')
            ->withConsecutive([$metrics]);
        $this->select->expects($this->once())
            ->method('group')
            ->withConsecutive(['value']);

        $this->metricsBuilder->expects($this->once())
            ->method('build')
            ->willReturn($metrics);

        $this->dataProvider->expects($this->once())->method('getDataSet')->willReturn($this->select);
        $this->dataProvider->expects($this->once())->method('execute')->willReturn($this->select);

        $result = $this->term->build($this->dataProvider, [], $this->bucket, $productIds);

        $this->assertEquals($this->select, $result);
    }
}
