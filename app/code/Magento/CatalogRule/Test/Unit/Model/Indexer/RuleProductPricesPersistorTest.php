<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface;

class RuleProductPricesPersistorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor
     */
    private $model;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var ActiveTableSwitcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeTableSwitcherMock;

    /**
     * @var IndexerTableSwapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tableSwapperMock;

    protected function setUp()
    {
        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeTableSwitcherMock = $this->getMockBuilder(ActiveTableSwitcher::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->tableSwapperMock = $this->getMockForAbstractClass(
            IndexerTableSwapperInterface::class
        );
        $this->model = new \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor(
            $this->dateTimeMock,
            $this->resourceMock,
            $this->activeTableSwitcherMock,
            $this->tableSwapperMock
        );
    }

    public function testExecuteWithEmptyPriceData()
    {
        $this->assertFalse($this->model->execute([]));
    }

    public function testExecute()
    {
        $priceData = [
            [
               'product_id' => 1,
                'rule_date' => '2017-05-01',
                'latest_start_date' => '2017-05-10',
                'earliest_end_date' => '2017-05-20',
            ]
        ];
        $tableName = 'catalogrule_product_price_replica';

        $this->tableSwapperMock->expects($this->once())
            ->method('getWorkingTableName')
            ->with('catalogrule_product_price')
            ->willReturn($tableName);

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with('catalogrule_product_price')
            ->willReturn('catalogrule_product_price');
        $this->resourceMock->expects($this->at(2))
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($tableName);

        $this->dateTimeMock->expects($this->at(0))
            ->method('formatDate')
            ->with($priceData[0]['rule_date'], false)
            ->willReturn($priceData[0]['rule_date']);

        $this->dateTimeMock->expects($this->at(1))
            ->method('formatDate')
            ->with($priceData[0]['latest_start_date'], false)
            ->willReturn($priceData[0]['latest_start_date']);

        $this->dateTimeMock->expects($this->at(2))
            ->method('formatDate')
            ->with($priceData[0]['earliest_end_date'], false)
            ->willReturn($priceData[0]['earliest_end_date']);

        $connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($tableName, $priceData);

        $this->assertTrue($this->model->execute($priceData, true));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Insert error.
     */
    public function testExecuteWithException()
    {
        $priceData = [
            [
                'product_id' => 1,
                'rule_date' => '2017-05-5',
                'latest_start_date' => '2017-05-10',
                'earliest_end_date' => '2017-05-22',
            ]
        ];
        $tableName = 'catalogrule_product_price_replica';

        $this->tableSwapperMock->expects($this->once())
            ->method('getWorkingTableName')
            ->with('catalogrule_product_price')
            ->willReturn($tableName);

        $this->dateTimeMock->expects($this->at(0))
            ->method('formatDate')
            ->with($priceData[0]['rule_date'], false)
            ->willReturn($priceData[0]['rule_date']);

        $this->dateTimeMock->expects($this->at(1))
            ->method('formatDate')
            ->with($priceData[0]['latest_start_date'], false)
            ->willReturn($priceData[0]['latest_start_date']);

        $this->dateTimeMock->expects($this->at(2))
            ->method('formatDate')
            ->with($priceData[0]['earliest_end_date'], false)
            ->willReturn($priceData[0]['earliest_end_date']);

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($tableName, $priceData)
            ->willThrowException(new \Exception('Insert error.'));

        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with('catalogrule_product_price')
            ->willReturn('catalogrule_product_price');
        $this->resourceMock->expects($this->at(2))
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($tableName);

        $this->assertTrue($this->model->execute($priceData, true));
    }
}
