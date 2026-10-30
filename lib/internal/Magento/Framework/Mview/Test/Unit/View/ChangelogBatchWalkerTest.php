<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\ChangelogBatch;
use Magento\Framework\Mview\View\ChangelogBatchFactory;
use Magento\Framework\Mview\View\ChangelogBatchWalker;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsContext;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface;
use Magento\Framework\Mview\View\ChangelogInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /** @var ChangelogBatchFactory|MockObject */
    private $batchFactory;

    /** @var ChangelogBatchWalker */
    private ChangelogBatchWalker $walker;

    /** @var array */
    private array $whereCalls = [];

    /** @var array */
    private array $havingCalls = [];

    /** @var array */
    private array $groupedColumns = [];

    /** @var array */
    private array $limitCalls = [];

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createStub(ResourceConnection::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->idsContext = $this->createStub(IdsContext::class);
        $this->idsSelectBuilder = $this->createStub(IdsSelectBuilderInterface::class);
        $this->changelog = $this->createStub(ChangelogInterface::class);
        $this->select = $this->createStub(Select::class);
        $this->batchFactory = $this->createMock(ChangelogBatchFactory::class);

        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->willReturn('mview_cl');
        $this->changelog->method('getName')->willReturn('mview_cl');
        $this->changelog->method('getColumnName')->willReturn('entity_id');
        $this->idsContext->method('getSelectBuilder')->willReturn($this->idsSelectBuilder);

        $this->select->method('getPart')->willReturnCallback(fn (): array => $this->groupedColumns);
        foreach (['columns', 'group', 'order', 'reset'] as $method) {
            $this->select->method($method)->willReturnSelf();
        }
        $this->select->method('limit')->willReturnCallback(function (...$arguments) {
            $this->limitCalls[] = $arguments;
            return $this->select;
        });
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
            $this->idsContext,
            $this->batchFactory
        );
    }

    /**
     * @given Changelog entries span several pages within a version range.
     * @when The walker reads those pages.
     * @then Batches expose IDs and maximum versions without staging tables.
     */
    public function testWalkReportsMaxVersionWithoutCreatingTemporaryTables(): void
    {
        $this->connection->expects($this->once())
            ->method('isTableExists')
            ->with('mview_cl')
            ->willReturn(true);
        $this->batchFactory->expects($this->exactly(2))->method('create')->willReturnCallback(
            static fn (array $arguments): ChangelogBatch => new ChangelogBatch($arguments['data'])
        );
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

        $batches = iterator_to_array($this->walker->walk($this->changelog, 0, 40, 2), false);

        $this->assertCount(2, $batches);
        $this->assertInstanceOf(ChangelogBatch::class, $batches[0]);
        $this->assertSame(['4', '9'], $batches[0]->getIds());
        $this->assertSame(['4', '9'], iterator_to_array($batches[0]));
        $this->assertSame(22, $batches[0]->getMaxVersionId());
        $this->assertSame(['3'], $batches[1]->getIds());
        $this->assertSame(35, $batches[1]->getMaxVersionId());
        $this->assertSame(
            [
                ['version_id <= ?', 40],
                ['version_id > ?', 0], ['version_id > ?', 22], ['version_id > ?', 35],
            ],
            $this->whereCalls
        );
        $this->assertSame([], $this->havingCalls);
    }

    /**
     * @given A custom selector groups entities with attribute and store columns.
     * @when The walker pages through the grouped results.
     * @then Batches retain grouped fields and the input window without early checkpoints.
     */
    public function testWalkPreservesAdditionalColumnsFromCustomSelectBuilder(): void
    {
        $this->groupedColumns = ['entity_id', 'store_id'];
        $this->batchFactory->expects($this->once())->method('create')->willReturnCallback(
            static fn (array $arguments): ChangelogBatch => new ChangelogBatch($arguments['data'])
        );
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

        $this->assertCount(1, $batches);
        $this->assertInstanceOf(ChangelogBatch::class, $batches[0]);
        $this->assertFalse($batches[0]->canCheckpoint());
        $this->assertSame(
            [['entity_id' => '4', 'attribute_ids' => '1,2', 'store_id' => '0']],
            $batches[0]->getIds()
        );
        $this->assertSame(
            [
                ['version_id <= ?', 40], ['version_id > ?', 0],
            ],
            $this->whereCalls
        );
        $this->assertSame(
            [['MAX(version_id) > ?', 0], ['MAX(version_id) > ?', 12]],
            $this->havingCalls
        );
    }

    /**
     * @given The injected factory supplies a customized batch instance.
     * @when The walker builds a page.
     * @then It yields that same extensible and iterable instance.
     */
    public function testWalkYieldsTheCustomizedFactoryInstance(): void
    {
        $this->connection->method('isTableExists')->willReturn(true);
        $this->connection->expects($this->once())->method('fetchAll')->willReturn([
            ['entity_id' => '4', 'max_version_id' => '40'],
        ]);
        $batch = new ChangelogBatch(['ids' => ['4'], 'max_version_id' => 40, 'custom' => true]);
        $this->batchFactory->expects($this->once())->method('create')->with([
            'data' => ['ids' => ['4'], 'max_version_id' => 40, 'can_checkpoint' => true],
        ])->willReturn($batch);

        $batches = iterator_to_array($this->walker->walk($this->changelog, 0, 40, 2), false);

        $this->assertSame($batch, $batches[0]);
        $this->assertTrue($batches[0]->getData('custom'));
        $this->assertTrue($batch->canCheckpoint());
        $batch->setData('can_checkpoint', false);
        $this->assertFalse($batch->canCheckpoint());
        $batch->setData('ids', [9]);
        $this->assertSame([9], iterator_to_array($batch));
        $this->assertSame([9], $batch['ids']);
    }

    /**
     * @given A customized batch builder returns a cursor-aware batch.
     * @when The walker requests a page.
     * @then The hook receives the select and its result is yielded unchanged.
     */
    #[DataProvider('batchGroupingProvider')]
    public function testWalkPassesSelectToPublicBatchBuilder(bool $hasCustomGrouping): void
    {
        $this->groupedColumns = $hasCustomGrouping ? ['entity_id', 'store_id'] : [];
        $ids = $hasCustomGrouping
            ? [['entity_id' => '4', 'custom_metadata' => 'customized']]
            : ['4'];
        $batch = new ChangelogBatch([
            'ids' => $ids,
            'max_version_id' => 40,
            'custom_metadata' => 'customized',
            'can_checkpoint' => !$hasCustomGrouping,
        ]);
        $walker = $this->getMockBuilder(ChangelogBatchWalker::class)
            ->setConstructorArgs([
                $this->resourceConnection,
                $this->createStub(Generator::class),
                $this->idsContext,
                $this->batchFactory,
            ])
            ->onlyMethods(['buildBatch'])
            ->getMock();
        $walker->expects($this->once())->method('buildBatch')
            ->with($this->isInstanceOf(Select::class), 'entity_id', $hasCustomGrouping)
            ->willReturn($batch);
        $this->batchFactory->expects($this->never())->method('create');
        $this->connection->method('isTableExists')->willReturn(true);
        $this->connection->expects($this->never())->method('fetchAll');

        $this->assertSame(
            [$batch],
            iterator_to_array($walker->walk($this->changelog, 0, 40, 2), false)
        );
        $this->assertSame([[2, null]], $this->limitCalls);
    }

    /**
     * @given The select has no remaining rows.
     * @when The batch builder fetches the page.
     * @then It returns an empty array without calling the factory.
     */
    public function testBuildBatchReturnsEmptyIterableWhenNoRowsRemain(): void
    {
        $this->connection->expects($this->once())->method('fetchAll')->with($this->select)->willReturn([]);
        $this->batchFactory->expects($this->never())->method('create');

        $this->assertSame([], $this->walker->buildBatch($this->select, 'entity_id', false));
    }

    /**
     * @given A customized batch builder returns IDs without cursor metadata.
     * @when The walker requests its first page.
     * @then It rejects the result rather than paging without a version cursor.
     */
    public function testWalkRejectsBatchesWithoutCursorMetadata(): void
    {
        $walker = $this->getMockBuilder(ChangelogBatchWalker::class)
            ->setConstructorArgs([
                $this->resourceConnection,
                $this->createStub(Generator::class),
                $this->idsContext,
                $this->batchFactory,
            ])
            ->onlyMethods(['buildBatch'])
            ->getMock();
        $walker->expects($this->once())->method('buildBatch')->willReturn(['4']);
        $this->connection->method('isTableExists')->willReturn(true);
        $this->connection->expects($this->never())->method('fetchAll');

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('getMaxVersionId');

        iterator_to_array($walker->walk($this->changelog, 0, 40, 2), false);
    }

    /**
     * @given Rows contain duplicate IDs and additional metadata.
     * @when The builder extracts the configured ID column.
     * @then The payload contains only those IDs with duplicates preserved.
     */
    public function testBuildBatchExtractsConfiguredIdColumnWithoutDeduplication(): void
    {
        $rows = [
            ['product_id' => '4', 'custom_metadata' => 'first', 'max_version_id' => '12'],
            ['product_id' => '4', 'custom_metadata' => 'second', 'max_version_id' => '22'],
            ['product_id' => '9', 'custom_metadata' => 'third', 'max_version_id' => '40'],
        ];
        $this->connection->expects($this->once())->method('fetchAll')->with($this->select)->willReturn($rows);
        $this->batchFactory->expects($this->once())->method('create')->with([
            'data' => ['ids' => ['4', '4', '9'], 'max_version_id' => 40, 'can_checkpoint' => true],
        ])->willReturnCallback(static fn (array $arguments): ChangelogBatch => new ChangelogBatch($arguments['data']));

        $batch = $this->walker->buildBatch($this->select, 'product_id', false);

        $this->assertInstanceOf(ChangelogBatch::class, $batch);
        $this->assertSame(['4', '4', '9'], $batch->getIds());
        $this->assertSame(40, $batch->getMaxVersionId());
    }

    /**
     * @return array
     */
    public static function batchGroupingProvider(): array
    {
        return [
            'ordinary ID batches' => [false],
            'custom grouped payloads' => [true],
        ];
    }
}
