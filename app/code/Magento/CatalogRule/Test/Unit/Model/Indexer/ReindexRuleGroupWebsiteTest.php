<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface;
use Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReindexRuleGroupWebsiteTest extends TestCase
{
    /**
     * @var ReindexRuleGroupWebsite
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeTableSwitcherMock =
            $this->getMockBuilder(ActiveTableSwitcher::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->tableSwapperMock = $this->getMockForAbstractClass(
            IndexerTableSwapperInterface::class
        );
        $this->model = new ReindexRuleGroupWebsite(
            $this->dateTimeMock,
            $this->resourceMock,
            $this->activeTableSwitcherMock,
            $this->tableSwapperMock
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $timeStamp = (int)gmdate('U');
        $insertString = 'insert_string';
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $this->resourceMock
            ->method('getConnection')
            ->willReturn($connectionMock);
        $this->dateTimeMock->expects($this->once())->method('gmtTimestamp')->willReturn($timeStamp);

        $this->tableSwapperMock->expects($this->any())
            ->method('getWorkingTableName')
            ->willReturnMap(
                [
                    ['catalogrule_group_website', 'catalogrule_group_website_replica'],
                    ['catalogrule_product', 'catalogrule_product_replica']
                ]
            );

        $this->resourceMock->expects($this->any())
            ->method('getTableName')
            ->willReturnMap(
                [
                    ['catalogrule_group_website', 'default', 'catalogrule_group_website'],
                    ['catalogrule_product', 'default', 'catalogrule_product'],
                    ['catalogrule_group_website_replica', 'default', 'catalogrule_group_website_replica'],
                    ['catalogrule_product_replica', 'default', 'catalogrule_product_replica']
                ]
            );

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->once())->method('delete')->with('catalogrule_group_website_replica');
        $connectionMock->expects($this->once())->method('select')->willReturn($selectMock);

        $selectMock->expects($this->once())->method('distinct')->with(true)->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('from')
            ->with('catalogrule_product_replica', ['rule_id', 'customer_group_id', 'website_id'])
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with("{$timeStamp} >= from_time AND (({$timeStamp} <= to_time AND to_time > 0) OR to_time = 0)")
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('insertFromSelect')
            ->with('catalogrule_group_website_replica', ['rule_id', 'customer_group_id', 'website_id'])
            ->willReturn($insertString);
        $connectionMock->expects($this->once())->method('query')->with($insertString);

        $this->assertTrue($this->model->execute(true));
    }
}
