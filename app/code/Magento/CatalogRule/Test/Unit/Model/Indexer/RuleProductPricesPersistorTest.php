<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleProductPricesPersistorTest extends TestCase
{
    /**
     * @var RuleProductPricesPersistor
     */
    private $model;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var ActiveTableSwitcher|MockObject
     */
    private $activeTableSwitcherMock;

    /**
     * @var IndexerTableSwapperInterface|MockObject
     */
    private $tableSwapperMock;

    protected function setUp(): void
    {
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeTableSwitcherMock = $this->getMockBuilder(ActiveTableSwitcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tableSwapperMock = $this->getMockForAbstractClass(
            IndexerTableSwapperInterface::class
        );
        $this->model = new RuleProductPricesPersistor(
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

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

    public function testExecuteWithException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Insert error.');
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

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
