<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver as TableResolver;
use Magento\Framework\Search\Request\Dimension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TableMaintainerTest extends TestCase
{
    /** @var ResourceConnection|MockObject */
    private $resource;

    /** @var TableResolver|MockObject */
    private $tableResolver;

    /** @var AdapterInterface|MockObject */
    private $adapter;

    /** @var TableMaintainer */
    private $maintainer;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->tableResolver = $this->createMock(TableResolver::class);
        $this->adapter = $this->createMock(AdapterInterface::class);

        $this->resource->method('getConnection')->willReturn($this->adapter);
        $this->resource->method('getTableName')->willReturnCallback(
            static function (string $name) {
                return 'pref_' . $name;
            }
        );

        $this->maintainer = new TableMaintainer($this->resource, $this->tableResolver);
    }

    public function testGetMainTableAndReplicaTableName(): void
    {
        $storeId = 3;
        $resolvedMain = 'catalog_category_product_index_store3';

        $this->tableResolver->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(AbstractAction::MAIN_INDEX_TABLE, $this->callback(function (array $dims): bool {
                return isset($dims[0]) && $dims[0] instanceof Dimension;
            }))
            ->willReturn($resolvedMain);

        $this->assertSame($resolvedMain, $this->maintainer->getMainTable($storeId));
        $this->assertSame($resolvedMain . '_replica', $this->maintainer->getMainReplicaTable($storeId));
    }

    public function testCreateMainTmpTableAndGetMainTmpTable(): void
    {
        $storeId = 7;
        $resolvedMain = 'index_7';
        $tmpName = $resolvedMain . '_tmp';

        $this->tableResolver->expects($this->any())
            ->method('resolve')
            ->willReturn($resolvedMain);

        $this->adapter->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($tmpName, $resolvedMain, true);

        // First call creates and caches temp table
        $this->maintainer->createMainTmpTable($storeId);
        // Second call should be a no-op (still once)
        $this->maintainer->createMainTmpTable($storeId);

        self::assertSame($tmpName, $this->maintainer->getMainTmpTable($storeId));

        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        // Different store id should not have tmp table created
        $this->maintainer->getMainTmpTable($storeId + 1);
    }

    public function testGetSameAdapterConnectionReturnsSameInstance(): void
    {
        $same = $this->maintainer->getSameAdapterConnection();
        $this->assertSame($this->adapter, $same);
    }

    public function testCreateTablesForStoreCreatesMainAndReplicaWhenMissing(): void
    {
        $storeId = 2;
        $resolvedMain = 'index_2';
        $baseReplica = 'pref_' . AbstractAction::MAIN_INDEX_TABLE . '_replica';

        $this->tableResolver->expects($this->any())
            ->method('resolve')
            ->willReturn($resolvedMain);

        $expectedIsTableArgs = [$resolvedMain, $resolvedMain . '_replica'];
        $this->adapter->expects($this->exactly(2))
            ->method('isTableExists')
            ->willReturnCallback(function ($arg) use (&$expectedIsTableArgs) {
                $expected = array_shift($expectedIsTableArgs);
                \PHPUnit\Framework\Assert::assertSame($expected, $arg);
                return false;
            });

        $ddlMain = $this->createMock(DdlTable::class);
        $ddlReplica = $this->createMock(DdlTable::class);

        $createByDdlCall = 0;
        $this->adapter->expects($this->exactly(2))
            ->method('createTableByDdl')
            ->willReturnCallback(function (
                $base,
                $new
            ) use (
                $baseReplica,
                $resolvedMain,
                $ddlMain,
                $ddlReplica,
                &$createByDdlCall
            ) {
                if ($createByDdlCall === 0) {
                    \PHPUnit\Framework\Assert::assertSame($baseReplica, $base);
                    \PHPUnit\Framework\Assert::assertSame($resolvedMain, $new);
                    $createByDdlCall++;
                    return $ddlMain;
                }
                \PHPUnit\Framework\Assert::assertSame($baseReplica, $base);
                \PHPUnit\Framework\Assert::assertSame($resolvedMain . '_replica', $new);
                $createByDdlCall++;
                return $ddlReplica;
            });

        $expectedCreateArgs = [$ddlMain, $ddlReplica];
        $this->adapter->expects($this->exactly(2))
            ->method('createTable')
            ->willReturnCallback(function ($ddl) use (&$expectedCreateArgs) {
                $expected = array_shift($expectedCreateArgs);
                \PHPUnit\Framework\Assert::assertSame($expected, $ddl);
                return null;
            });

        $this->maintainer->createTablesForStore($storeId);
    }

    public function testDropTablesForStoreDropsWhenExists(): void
    {
        $storeId = 4;
        $resolvedMain = 'index_4';

        $this->tableResolver->expects($this->any())
            ->method('resolve')
            ->willReturn($resolvedMain);

        $expectedIsTableArgs = [$resolvedMain, $resolvedMain . '_replica'];
        $this->adapter->expects($this->exactly(2))
            ->method('isTableExists')
            ->willReturnCallback(function ($arg) use (&$expectedIsTableArgs) {
                $expected = array_shift($expectedIsTableArgs);
                \PHPUnit\Framework\Assert::assertSame($expected, $arg);
                return true;
            });

        $expectedDropArgs = [$resolvedMain, $resolvedMain . '_replica'];
        $this->adapter->expects($this->exactly(2))
            ->method('dropTable')
            ->willReturnCallback(function ($arg) use (&$expectedDropArgs) {
                $expected = array_shift($expectedDropArgs);
                \PHPUnit\Framework\Assert::assertSame($expected, $arg);
                return null;
            });

        $this->maintainer->dropTablesForStore($storeId);
    }
}
