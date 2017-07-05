<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

class ReindexRuleProductTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeTableSwitcherMock;

    protected function setUp()
    {
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeTableSwitcherMock =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\CatalogRule\Model\Indexer\ReindexRuleProduct(
            $this->resourceMock,
            $this->activeTableSwitcherMock
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

        $this->activeTableSwitcherMock->expects($this->once())
            ->method('getAdditionalTableName')
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
