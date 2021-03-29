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
    private const ADMIN_WEBSITE_ID = 0;

    /**
     * @var ReindexRuleProduct
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var IndexerTableSwapperInterface|MockObject
     */
    private $tableSwapperMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Rule|MockObject
     */
    private $ruleMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $activeTableSwitcherMock = $this->createMock(ActiveTableSwitcher::class);
        $this->tableSwapperMock = $this->getMockForAbstractClass(IndexerTableSwapperInterface::class);
        $this->localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->ruleMock = $this->createMock(Rule::class);

        $this->model = new ReindexRuleProduct(
            $this->resourceMock,
            $activeTableSwitcherMock,
            $this->tableSwapperMock,
            $this->localeDateMock,
            true
        );
    }

    public function testExecuteIfRuleInactive(): void
    {
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects(self::once())
            ->method('getIsActive')
            ->willReturn(false);
        self::assertFalse($this->model->execute($ruleMock, 100, true));
    }

    public function testExecuteIfRuleWithoutWebsiteIds(): void
    {
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects(self::once())
            ->method('getIsActive')
            ->willReturn(true);
        $ruleMock->expects(self::once())
            ->method('getWebsiteIds')
            ->willReturn(null);
        self::assertFalse($this->model->execute($ruleMock, 100, true));
    }

    public function testExecute(): void
    {
        $websiteId = 3;
        $adminTimeZone = 'America/Chicago';
        $websiteTz = 'America/Los_Angeles';
        $productIds = [
            4 => [$websiteId => 1],
            5 => [$websiteId => 1],
            6 => [$websiteId => 1],
        ];

        $this->prepareResourceMock();
        $this->prepareRuleMock([3], $productIds, [10]);

        $this->localeDateMock->method('getConfigTimezone')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITE, self::ADMIN_WEBSITE_ID, $adminTimeZone],
                [ScopeInterface::SCOPE_WEBSITE, $websiteId, $websiteTz],
            ]);

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

        $this->connectionMock->expects(self::at(0))
            ->method('insertMultiple')
            ->with('catalogrule_product_replica', $batchRows);
        $this->connectionMock->expects(self::at(1))
            ->method('insertMultiple')
            ->with('catalogrule_product_replica', $rowsNotInBatch);

        self::assertTrue($this->model->execute($this->ruleMock, 2, true));
    }

    public function testExecuteWithExcludedWebsites(): void
    {
        $websitesIds = [1, 2, 3];
        $adminTimeZone = 'America/Chicago';
        $websiteTz = 'America/Los_Angeles';
        $productIds = [
            1 => [1 => 1],
            2 => [2 => 1],
            3 => [3 => 1],
        ];

        $this->prepareResourceMock();
        $this->prepareRuleMock($websitesIds, $productIds, [10, 20]);

        $extensionAttributes = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesInterface::class)
            ->setMethods(['getExtensionAttributes', 'getExcludeWebsiteIds'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->ruleMock->expects(self::once())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects(self::exactly(2))->method('getExcludeWebsiteIds')
            ->willReturn([10 => [1, 2]]);

        $this->localeDateMock->method('getConfigTimezone')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITE, self::ADMIN_WEBSITE_ID, $adminTimeZone],
                [ScopeInterface::SCOPE_WEBSITE, 1, $websiteTz],
                [ScopeInterface::SCOPE_WEBSITE, 2, $websiteTz],
                [ScopeInterface::SCOPE_WEBSITE, 3, $websiteTz],
            ]);

        $batchRows = [
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => 1,
                'customer_group_id' => 20,
                'product_id' => 1,
                'action_operator' => 'simple_action',
                'action_amount' => 43,
                'action_stop' => true,
                'sort_order' => 1,
            ],
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => 2,
                'customer_group_id' => 20,
                'product_id' => 2,
                'action_operator' => 'simple_action',
                'action_amount' => 43,
                'action_stop' => true,
                'sort_order' => 1,
            ],
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => 3,
                'customer_group_id' => 10,
                'product_id' => 3,
                'action_operator' => 'simple_action',
                'action_amount' => 43,
                'action_stop' => true,
                'sort_order' => 1,
            ],
            [
                'rule_id' => 100,
                'from_time' => 1498028400,
                'to_time' => 1498892399,
                'website_id' => 3,
                'customer_group_id' => 20,
                'product_id' => 3,
                'action_operator' => 'simple_action',
                'action_amount' => 43,
                'action_stop' => true,
                'sort_order' => 1,
            ]
        ];

        $this->connectionMock->expects(self::at(0))
            ->method('insertMultiple')
            ->with('catalogrule_product_replica', $batchRows);

        self::assertTrue($this->model->execute($this->ruleMock, 100, true));
    }

    private function prepareResourceMock(): void
    {
        $this->tableSwapperMock->expects(self::once())
            ->method('getWorkingTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product_replica');
        $this->resourceMock->expects(self::at(0))
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->expects(self::at(1))
            ->method('getTableName')
            ->with('catalogrule_product')
            ->willReturn('catalogrule_product');
        $this->resourceMock->expects(self::at(2))
            ->method('getTableName')
            ->with('catalogrule_product_replica')
            ->willReturn('catalogrule_product_replica');
    }

    /**
     * @param array $websiteId
     * @param array $productIds
     * @param array $customerGroupIds
     */
    private function prepareRuleMock(array $websiteId, array $productIds, array $customerGroupIds): void
    {
        $this->ruleMock->expects(self::once())->method('getIsActive')->willReturn(true);
        $this->ruleMock->expects(self::exactly(2))->method('getWebsiteIds')->willReturn($websiteId);
        $this->ruleMock->expects(self::once())->method('getMatchingProductIds')->willReturn($productIds);
        $this->ruleMock->expects(self::once())->method('getId')->willReturn(100);
        $this->ruleMock->expects(self::once())->method('getCustomerGroupIds')->willReturn($customerGroupIds);
        $this->ruleMock->expects(self::atLeastOnce())->method('getFromDate')->willReturn('2017-06-21');
        $this->ruleMock->expects(self::atLeastOnce())->method('getToDate')->willReturn('2017-06-30');
        $this->ruleMock->expects(self::once())->method('getSortOrder')->willReturn(1);
        $this->ruleMock->expects(self::once())->method('getSimpleAction')->willReturn('simple_action');
        $this->ruleMock->expects(self::once())->method('getDiscountAmount')->willReturn(43);
        $this->ruleMock->expects(self::once())->method('getStopRulesProcessing')->willReturn(true);
    }
}
