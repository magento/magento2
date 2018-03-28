<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface;

class ReindexRuleProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\ReindexRuleProduct
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var IndexerTableSwapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tableSwapperMock;

    protected function setUp()
    {
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ActiveTableSwitcher|\PHPUnit_Framework_MockObject_MockObject $activeTableSwitcherMock */
        $activeTableSwitcherMock =
            $this->getMockBuilder(ActiveTableSwitcher::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->tableSwapperMock = $this->getMockForAbstractClass(
            IndexerTableSwapperInterface::class
        );
        $this->model = new \Magento\CatalogRule\Model\Indexer\ReindexRuleProduct(
            $this->resourceMock,
            $activeTableSwitcherMock,
            $this->tableSwapperMock
        );
    }

    public function testExecuteIfRuleInactive()
    {
        $ruleMock = $this->getMockBuilder(\Magento\CatalogRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->once())->method('getIsActive')->willReturn(false);
        $this->assertFalse($this->model->execute($ruleMock, 100, true));
    }

    public function testExecuteIfRuleWithoutWebsiteIds()
    {
        $ruleMock = $this->getMockBuilder(\Magento\CatalogRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->once())->method('getIsActive')->willReturn(true);
        $ruleMock->expects($this->once())->method('getWebsiteIds')->willReturn(null);
        $this->assertFalse($this->model->execute($ruleMock, 100, true));
    }

    public function testExecute()
    {
        $productIds = [
            4 => [1 => 1],
            5 => [1 => 1],
            6 => [1 => 1],
        ];
        $ruleMock = $this->getMockBuilder(\Magento\CatalogRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->once())->method('getIsActive')->willReturn(true);
        $ruleMock->expects($this->exactly(2))->method('getWebsiteIds')->willReturn(1);
        $ruleMock->expects($this->once())->method('getMatchingProductIds')->willReturn($productIds);

        $this->tableSwapperMock->expects($this->once())
            ->method('getWorkingTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product_replica');

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->at(0))->method('getConnection')->willReturn($connectionMock);
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product');
        $this->resourceMock->expects($this->at(2))
            ->method('getTableName')
            ->with('catalogrule_product_replica')
            ->willReturn('catalogrule_product_replica');

        $ruleMock->expects($this->once())->method('getId')->willReturn(100);
        $ruleMock->expects($this->once())->method('getCustomerGroupIds')->willReturn([10]);
        $ruleMock->expects($this->once())->method('getFromDate')->willReturn('2017-06-21');
        $ruleMock->expects($this->once())->method('getToDate')->willReturn('2017-06-30');
        $ruleMock->expects($this->once())->method('getSortOrder')->willReturn(1);
        $ruleMock->expects($this->once())->method('getSimpleAction')->willReturn('simple_action');
        $ruleMock->expects($this->once())->method('getDiscountAmount')->willReturn(43);
        $ruleMock->expects($this->once())->method('getStopRulesProcessing')->willReturn(true);

        $batchRows = [
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => 1,
                'customer_group_id' => 10,
                'product_id' => 4,
                'action_operator' => 'simple_action',
                'action_amount' => 43,
                'action_stop' => true,
                'sort_order' => 1,
            ],
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => 1,
                'customer_group_id' => 10,
                'product_id' => 5,
                'action_operator' => 'simple_action',
                'action_amount' => 43,
                'action_stop' => true,
                'sort_order' => 1,
            ]
        ];

        $rowsNotInBatch = [
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => 1,
                'customer_group_id' => 10,
                'product_id' => 6,
                'action_operator' => 'simple_action',
                'action_amount' => 43,
                'action_stop' => true,
                'sort_order' => 1,
            ]
        ];

        $connectionMock->expects($this->at(0))
            ->method('insertMultiple')
            ->with('catalogrule_product_replica', $batchRows);
        $connectionMock->expects($this->at(1))
            ->method('insertMultiple')
            ->with('catalogrule_product_replica', $rowsNotInBatch);

        $this->assertTrue($this->model->execute($ruleMock, 2, true));
    }
}
