<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

class ReindexRuleGroupWebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite
     */
    private $model;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeTableSwitcherMock;

    protected function setUp()
    {
        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeTableSwitcherMock =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite(
            $this->dateTimeMock,
            $this->resourceMock,
            $this->activeTableSwitcherMock
        );
    }

    public function testExecute()
    {
        $timeStamp = (int)gmdate('U');
        $insertString = 'insert_string';
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)->getMock();
        $this->resourceMock->expects($this->at(0))->method('getConnection')->willReturn($connectionMock);
        $this->dateTimeMock->expects($this->once())->method('gmtTimestamp')->willReturn($timeStamp);

        $this->activeTableSwitcherMock->expects($this->at(0))
            ->method('getAdditionalTableName')
            ->with('catalogrule_group_website')
            ->willReturn('catalogrule_group_website_replica');
        $this->activeTableSwitcherMock->expects($this->at(1))
            ->method('getAdditionalTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product_replica');

        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with('catalogrule_group_website')
            ->willReturn('catalogrule_group_website');
        $this->resourceMock->expects($this->at(2))
            ->method('getTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product');
        $this->resourceMock->expects($this->at(3))
            ->method('getTableName')
            ->with('catalogrule_group_website_replica')
            ->willReturn('catalogrule_group_website_replica');
        $this->resourceMock->expects($this->at(4))
            ->method('getTableName')
            ->with('catalogrule_product_replica')
            ->willReturn('catalogrule_product_replica');

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
