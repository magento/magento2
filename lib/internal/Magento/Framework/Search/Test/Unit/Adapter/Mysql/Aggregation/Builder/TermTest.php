<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Term;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TermTest extends TestCase
{
    /**
     * @var Term
     */
    private $term;

    /**
     * @var Metrics|MockObject
     */
    private $metricsBuilder;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var RequestBucketInterface|MockObject
     */
    private $bucket;

    /**
     * @var DataProviderInterface|MockObject
     */
    private $dataProvider;

    /**
     * SetUP method
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->metricsBuilder = $this->getMockBuilder(
            Metrics::class
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder(Select::class)
            ->setMethods(['where', 'columns', 'group'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProvider = $this->getMockBuilder(
            DataProviderInterface::class
        )
            ->setMethods(['getDataSet', 'execute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->term = $helper->getObject(
            Term::class,
            ['metricsBuilder' => $this->metricsBuilder]
        );
    }

    /**
     * Test for method "build"
     */
    public function testBuild()
    {
        $metrics = ['count' => 'count(*)'];

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

        /** @var Table|MockObject $table */
        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->term->build($this->dataProvider, [], $this->bucket, $table);

        $this->assertEquals($this->select, $result);
    }
}
