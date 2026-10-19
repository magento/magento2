<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\ChangelogBatchWalker;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsContext;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface;
use Magento\Framework\Mview\View\ChangelogInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for bounded changelog walking.
 */
class ChangelogBatchWalkerTest extends TestCase
{
    /** @var ResourceConnection|MockObject */
    private $resourceConnection;

    /** @var AdapterInterface|MockObject */
    private $connection;

    /** @var IdsContext|MockObject */
    private $idsContext;

    /** @var IdsSelectBuilderInterface|MockObject */
    private $idsSelectBuilder;

    /** @var ChangelogInterface|MockObject */
    private $changelog;

    /** @var Select|MockObject */
    private $select;

    /** @var ChangelogBatchWalker */
    private ChangelogBatchWalker $walker;

    /** @var array */
    private array $whereCalls = [];

    /** @var array */
    private array $havingCalls = [];

    /** @var array */
    private array $groupedColumns = [];

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createStub(ResourceConnection::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->idsContext = $this->createStub(IdsContext::class);
        $this->idsSelectBuilder = $this->createStub(IdsSelectBuilderInterface::class);
        $this->changelog = $this->createStub(ChangelogInterface::class);
        $this->select = $this->createStub(Select::class);

        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->willReturn('mview_cl');
        $this->changelog->method('getName')->willReturn('mview_cl');
        $this->changelog->method('getColumnName')->willReturn('entity_id');
        $this->idsContext->method('getSelectBuilder')->willReturn($this->idsSelectBuilder);

        $this->select->method('getPart')->willReturnCallback(fn (): array => $this->groupedColumns);
        foreach (['columns', 'group', 'order', 'reset', 'limit'] as $method) {
            $this->select->method($method)->willReturnSelf();
        }
        $this->select->method('where')->willReturnCallback(function (...$arguments) {
            $this->whereCalls[] = array_slice($arguments, 0, 2);
            return $this->select;
        });
        $this->select->method('having')->willReturnCallback(function (...$arguments) {
            $this->havingCalls[] = array_slice($arguments, 0, 2);
            return $this->select;
        });
        $this->idsSelectBuilder->method('build')->willReturn($this->select);

        $this->walker = new ChangelogBatchWalker(
            $this->resourceConnection,
            $this->createStub(Generator::class),
            $this->idsContext
        );
    }

    public function testWalkReportsMaxVersionWithoutCreatingTemporaryTables(): void
    {
        $this->connection->expects($this->once())
            ->method('isTableExists')
            ->with('mview_cl')
            ->willReturn(true);
        $this->connection->expects($this->never())->method('createTable');
        $this->connection->expects($this->never())->method('dropTable');
        $this->connection->expects($this->exactly(3))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [
                    ['entity_id' => '4', 'max_version_id' => '12'],
                    ['entity_id' => '9', 'max_version_id' => '22'],
                ],
                [['entity_id' => '3', 'max_version_id' => '35']],
                []
            );

        $batches = iterator_to_array($this->walker->walkWithCursor($this->changelog, 0, 40, 2), false);

        $this->assertSame(
            [
                ['ids' => ['4', '9'], 'version_id' => 22],
                ['ids' => ['3'], 'version_id' => 35],
            ],
            $batches
        );
        $this->assertSame(
            [
                ['version_id > ?', 0], ['version_id <= ?', 40],
                ['version_id > ?', 22], ['version_id <= ?', 40],
                ['version_id > ?', 35], ['version_id <= ?', 40],
            ],
            $this->whereCalls
        );
        $this->assertSame([], $this->havingCalls);
    }

    public function testWalkPreservesAdditionalColumnsFromCustomSelectBuilder(): void
    {
        $this->groupedColumns = ['entity_id', 'store_id'];
        $this->connection->method('isTableExists')->willReturn(true);
        $this->connection->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [[
                    'entity_id' => '4',
                    'attribute_ids' => '1,2',
                    'store_id' => '0',
                    'max_version_id' => '12',
                ]],
                []
            );

        $batches = iterator_to_array($this->walker->walk($this->changelog, 0, 40, 10), false);

        $this->assertSame(
            [[['entity_id' => '4', 'attribute_ids' => '1,2', 'store_id' => '0']],],
            $batches
        );
        $this->assertSame(
            [
                ['version_id > ?', 0], ['version_id <= ?', 40],
                ['version_id > ?', 0], ['version_id <= ?', 40],
            ],
            $this->whereCalls
        );
        $this->assertSame(
            [['MAX(version_id) > ?', 0], ['MAX(version_id) > ?', 12]],
            $this->havingCalls
        );
    }
}
