<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\ResourceModel\Rule;

use Magento\CatalogRule\Model\ResourceModel\Rule\RuleIdProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RuleIdProvider
 */
class RuleIdProviderTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var RuleIdProvider
     */
    private $ruleIdProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);

        $this->resourceConnectionMock->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->ruleIdProvider = new RuleIdProvider($this->resourceConnectionMock);
    }

    /**
     * Test getActiveRuleIds returns active rule IDs
     *
     * @return void
     */
    public function testGetActiveRuleIdsReturnsActiveRules(): void
    {
        $expectedRuleIds = [1, 2, 3, 5, 8];
        $tableName = 'catalogrule';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('catalogrule')
            ->willReturn($tableName);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['rule_id'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('is_active = ?', 1)
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('sort_order ASC')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn($expectedRuleIds);

        $result = $this->ruleIdProvider->getActiveRuleIds();

        $this->assertEquals($expectedRuleIds, $result);
    }

    /**
     * Test getActiveRuleIds returns empty array when no active rules
     *
     * @return void
     */
    public function testGetActiveRuleIdsReturnsEmptyArrayWhenNoActiveRules(): void
    {
        $tableName = 'catalogrule';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('catalogrule')
            ->willReturn($tableName);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['rule_id'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('is_active = ?', 1)
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('sort_order ASC')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn([]);

        $result = $this->ruleIdProvider->getActiveRuleIds();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getAllRuleIds returns all rule IDs
     *
     * @return void
     */
    public function testGetAllRuleIdsReturnsAllRules(): void
    {
        $expectedRuleIds = [1, 2, 3, 4, 5, 6, 7, 8];
        $tableName = 'catalogrule';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('catalogrule')
            ->willReturn($tableName);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['rule_id'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('sort_order ASC')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn($expectedRuleIds);

        $result = $this->ruleIdProvider->getAllRuleIds();

        $this->assertEquals($expectedRuleIds, $result);
    }

    /**
     * Test getAllRuleIds returns empty array when no rules exist
     *
     * @return void
     */
    public function testGetAllRuleIdsReturnsEmptyArrayWhenNoRules(): void
    {
        $tableName = 'catalogrule';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('catalogrule')
            ->willReturn($tableName);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['rule_id'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('sort_order ASC')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn([]);

        $result = $this->ruleIdProvider->getAllRuleIds();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getActiveRuleIds returns rules sorted by sort_order
     *
     * @return void
     */
    public function testGetActiveRuleIdsReturnsSortedRules(): void
    {
        $expectedRuleIds = [3, 1, 2]; // Sorted by sort_order
        $tableName = 'catalogrule';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('catalogrule')
            ->willReturn($tableName);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['rule_id'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('is_active = ?', 1)
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('sort_order ASC')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn($expectedRuleIds);

        $result = $this->ruleIdProvider->getActiveRuleIds();

        $this->assertEquals($expectedRuleIds, $result);
    }

    /**
     * Test getAllRuleIds returns rules sorted by sort_order
     *
     * @return void
     */
    public function testGetAllRuleIdsReturnsSortedRules(): void
    {
        $expectedRuleIds = [5, 2, 1, 3, 4]; // Sorted by sort_order
        $tableName = 'catalogrule';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('catalogrule')
            ->willReturn($tableName);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['rule_id'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('sort_order ASC')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn($expectedRuleIds);

        $result = $this->ruleIdProvider->getAllRuleIds();

        $this->assertEquals($expectedRuleIds, $result);
    }

    /**
     * Test getActiveRuleIds with table prefix
     *
     * @return void
     */
    public function testGetActiveRuleIdsWithTablePrefix(): void
    {
        $expectedRuleIds = [1, 2, 3];
        $tableName = 'prefix_catalogrule';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('catalogrule')
            ->willReturn($tableName);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['rule_id'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('is_active = ?', 1)
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('sort_order ASC')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock)
            ->willReturn($expectedRuleIds);

        $result = $this->ruleIdProvider->getActiveRuleIds();

        $this->assertEquals($expectedRuleIds, $result);
    }
}
