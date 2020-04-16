<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RangeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Metrics|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metricsBuilder;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    private $select;

    /**
     * @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $bucket;

    /**
     * @var \Magento\Framework\Search\Request\Aggregation\Range|\PHPUnit\Framework\MockObject\MockObject
     */
    private $range;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Range
     */
    private $builder;

    /**
     * @var DataProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataProvider;

    /**
     * SetUP method
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->metricsBuilder = $this->getMockBuilder(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics::class
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->setMethods(['fetchAssoc', 'select', 'getCaseSql'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->setMethods(['getName', 'getRanges'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->range = $this->getMockBuilder(\Magento\Framework\Search\Request\Aggregation\Range::class)
            ->setMethods(['getFrom', 'getTo'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = $this->getMockBuilder(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface::class
        )
            ->setMethods(['getDataSet', 'execute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->builder = $helper->getObject(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Range::class,
            ['metricsBuilder' => $this->metricsBuilder, 'resource' => $this->resource]
        );
    }

    /**
     * Test for method "build"
     */
    public function testBuild()
    {
        $this->metricsBuilder->expects($this->once())
            ->method('build')
            ->willReturn(['metrics']);
        $this->bucket->expects($this->once())
            ->method('getRanges')
            ->willReturn(
                [$this->range, $this->range, $this->range]
            );
        $this->range->expects($this->at(0))
            ->method('getFrom')
            ->willReturn('');
        $this->range->expects($this->at(1))
            ->method('getTo')
            ->willReturn(50);
        $this->range->expects($this->at(2))
            ->method('getFrom')
            ->willReturn(50);
        $this->range->expects($this->at(3))
            ->method('getTo')
            ->willReturn(100);
        $this->range->expects($this->at(4))
            ->method('getFrom')
            ->willReturn(100);
        $this->range->expects($this->at(5))
            ->method('getTo')
            ->willReturn('');
        $this->connectionMock->expects($this->once())
            ->method('getCaseSql')
            ->withConsecutive(
                [''],
                [
                    [
                        '`value` < 50' => "'*_50'",
                        '`value` BETWEEN 50 AND 100' => "'50_100'",
                        '`value` >= 100' => "'100_*'",
                    ]
                ]
            );
        $this->dataProvider->expects($this->once())->method('getDataSet')->willReturn($this->select);
        $this->dataProvider->expects($this->once())->method('execute')->willReturn($this->select);

        /** @var \Magento\Framework\DB\Ddl\Table|\PHPUnit\Framework\MockObject\MockObject $table */
        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->builder->build($this->dataProvider, [], $this->bucket, $table);
        $this->assertEquals($this->select, $result);
    }
}
