<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProduct;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReindexRuleProductTest extends TestCase
{
    /**
     * @var ReindexRuleProduct
     */
    private $model;

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
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->activeTableSwitcherMock = $this->createMock(ActiveTableSwitcher::class);
        $this->tableSwapperMock = $this->getMockForAbstractClass(IndexerTableSwapperInterface::class);
        $this->localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);

        $this->model = new ReindexRuleProduct(
            $this->resourceMock,
            $this->activeTableSwitcherMock,
            $this->tableSwapperMock,
            $this->localeDateMock
        );
    }

    public function testExecuteIfRuleInactive()
    {
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->once())
            ->method('getIsActive')
            ->willReturn(false);
        $this->assertFalse($this->model->execute($ruleMock, 100, true));
    }

    public function testExecuteIfRuleWithoutWebsiteIds()
    {
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->once())
            ->method('getIsActive')
            ->willReturn(true);
        $ruleMock->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn(null);
        $this->assertFalse($this->model->execute($ruleMock, 100, true));
    }

    public function testExecute()
    {
        $websiteId = 3;
        $websiteTz = 'America/Los_Angeles';
        $productIds = [
            4 => [$websiteId => 1],
            5 => [$websiteId => 1],
            6 => [$websiteId => 1],
        ];

        $this->tableSwapperMock->expects($this->once())
            ->method('getWorkingTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product_replica');

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceMock->expects($this->at(0))
            ->method('getConnection')
            ->willReturn($connectionMock);
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product');
        $this->resourceMock->expects($this->at(2))
            ->method('getTableName')
            ->with('catalogrule_product_replica')
            ->willReturn('catalogrule_product_replica');

        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->once())->method('getIsActive')->willReturn(true);
        $ruleMock->expects($this->exactly(2))->method('getWebsiteIds')->willReturn([$websiteId]);
        $ruleMock->expects($this->once())->method('getMatchingProductIds')->willReturn($productIds);
        $ruleMock->expects($this->once())->method('getId')->willReturn(100);
        $ruleMock->expects($this->once())->method('getCustomerGroupIds')->willReturn([10]);
        $ruleMock->expects($this->atLeastOnce())->method('getFromDate')->willReturn('2017-06-21');
        $ruleMock->expects($this->atLeastOnce())->method('getToDate')->willReturn('2017-06-30');
        $ruleMock->expects($this->once())->method('getSortOrder')->willReturn(1);
        $ruleMock->expects($this->once())->method('getSimpleAction')->willReturn('simple_action');
        $ruleMock->expects($this->once())->method('getDiscountAmount')->willReturn(43);
        $ruleMock->expects($this->once())->method('getStopRulesProcessing')->willReturn(true);

        $this->localeDateMock->expects($this->once())
            ->method('getConfigTimezone')
            ->with(ScopeInterface::SCOPE_WEBSITE, $websiteId)
            ->willReturn($websiteTz);

        $batchRows = [
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => $websiteId,
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
                'website_id' => $websiteId,
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
                'website_id' => $websiteId,
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
