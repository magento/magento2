<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Indexer\Model\NoDdlMode;
use Magento\Framework\Indexer\NoDdlModeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoDdlModeTest extends TestCase
{
    private const TABLE_NAME = 'indexer_no_ddl_state';

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var PoisonPillPutInterface|MockObject
     */
    private $poisonPillPutMock;

    /**
     * @var NoDdlMode
     */
    private $noDdlMode;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceConnectionMock->method('getTableName')->willReturnArgument(0);
        $this->poisonPillPutMock = $this->createMock(PoisonPillPutInterface::class);

        $this->noDdlMode = new NoDdlMode(
            $this->scopeConfigMock,
            $this->resourceConnectionMock,
            $this->poisonPillPutMock
        );
    }

    /**
     * @return void
     */
    public function testIsEnabledReadsConfigPathForIndexerId(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('indexer/catalogpermissions_category/no_ddl_reindex')
            ->willReturn('1');

        $this->assertTrue($this->noDdlMode->isEnabled('catalogpermissions_category'));
    }

    /**
     * @return void
     */
    public function testIsEnabledReturnsFalseWhenConfigDisabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('indexer/catalogpermissions_product/no_ddl_reindex')
            ->willReturn('0');

        $this->assertFalse($this->noDdlMode->isEnabled('catalogpermissions_product'));
    }

    /**
     * @return void
     */
    public function testIsMainTableActiveDefaultsToTrueWhenNoStateRowExists(): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn(false);

        $this->assertTrue($this->noDdlMode->isMainTableActive('catalogpermissions_category'));
    }

    /**
     * @return void
     */
    public function testIsMainTableActiveReturnsPersistedValueWhenRowExists(): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn('0');

        $this->assertFalse($this->noDdlMode->isMainTableActive('catalogpermissions_category'));
    }

    /**
     * @return void
     */
    public function testIsMainTableActiveMemoizesAndDoesNotReQueryForSameIndexerId(): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn('1');

        $this->assertTrue($this->noDdlMode->isMainTableActive('catalogpermissions_category'));
        $this->assertTrue($this->noDdlMode->isMainTableActive('catalogpermissions_category'));
    }

    /**
     * @return void
     */
    public function testFlipActiveTableSeedsRowAndTogglesAtomically(): void
    {
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                self::TABLE_NAME,
                [
                    'indexer_id' => 'catalogpermissions_category',
                    'is_main_active' => true,
                ]
            );

        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                self::TABLE_NAME,
                $this->callback(function (array $data) {
                    return isset($data['is_main_active'])
                        && $data['is_main_active'] instanceof \Zend_Db_Expr
                        && (string)$data['is_main_active'] === 'NOT is_main_active';
                }),
                ['indexer_id = ?' => 'catalogpermissions_category']
            );

        $this->poisonPillPutMock->expects($this->once())->method('put');

        $this->noDdlMode->flipActiveTable('catalogpermissions_category');
    }

    /**
     * @return void
     */
    public function testFlipActiveTableInvalidatesCacheSoNextReadReQueriesDb(): void
    {
        $this->connectionMock->method('insertOnDuplicate');
        $this->connectionMock->method('update');

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn('0');

        $this->noDdlMode->flipActiveTable('catalogpermissions_category');

        $this->assertFalse($this->noDdlMode->isMainTableActive('catalogpermissions_category'));
    }

    /**
     * @return void
     */
    public function testFlipActiveTableInvalidatesCachePerIndexerIdIndependently(): void
    {
        $this->connectionMock->method('insertOnDuplicate');
        $this->connectionMock->method('update');

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturn(false);

        $this->noDdlMode->flipActiveTable('catalogpermissions_category');
        $this->noDdlMode->flipActiveTable('catalogpermissions_product');

        $this->assertTrue($this->noDdlMode->isMainTableActive('catalogpermissions_category'));
        $this->assertTrue($this->noDdlMode->isMainTableActive('catalogpermissions_product'));
    }

    /**
     * @return void
     */
    public function testConstant(): void
    {
        $this->assertSame('indexer/%s/no_ddl_reindex', NoDdlModeInterface::XML_PATH_NO_DDL_REINDEX_MASK);
    }
}
