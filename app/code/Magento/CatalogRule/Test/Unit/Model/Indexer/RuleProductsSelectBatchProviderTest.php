<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use InvalidArgumentException;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBatchProvider;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Convert\DataSize;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Zend_Db_Statement_Interface;

class RuleProductsSelectBatchProviderTest extends TestCase
{
    /**
     * @var IndexerTableSwapperInterface|MockObject
     */
    private $tableSwapper;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var DataSize|MockObject
     */
    private $dataSize;

    /**
     * @var RuleProductsSelectBuilder|MockObject
     */
    private $selectBuilder;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    protected function setUp(): void
    {
        $this->tableSwapper = $this->createMock(IndexerTableSwapperInterface::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->dataSize = $this->createMock(DataSize::class);
        $this->selectBuilder = $this->createMock(RuleProductsSelectBuilder::class);
        $this->connection = $this->createMock(AdapterInterface::class);
    }

    #[DataProvider('invalidConfigurationProvider')]
    public function testInvalidConfiguration(array $arguments, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        new RuleProductsSelectBatchProvider(
            $this->tableSwapper,
            $this->resource,
            $this->dataSize,
            $this->selectBuilder,
            ...$arguments
        );
    }

    public static function invalidConfigurationProvider(): array
    {
        $shareMessage = 'resultBufferMemoryShare must be finite, greater than zero and less than or equal to one.';
        return [
            'zero minimum' => [[0, 2, 256, 0.4], 'minProductsPerBatch must be greater than zero.'],
            'negative minimum' => [[-1, 2, 256, 0.4], 'minProductsPerBatch must be greater than zero.'],
            'zero maximum' => [[1, 0, 256, 0.4], 'maxProductsPerBatch must be greater than zero.'],
            'negative maximum' => [[1, -1, 256, 0.4], 'maxProductsPerBatch must be greater than zero.'],
            'inverted bounds' => [
                [3, 2, 256, 0.4],
                'maxProductsPerBatch must be greater than or equal to minProductsPerBatch.'
            ],
            'zero row bytes' => [[1, 2, 0, 0.4], 'resultBufferBytesPerRow must be greater than zero.'],
            'negative row bytes' => [[1, 2, -1, 0.4], 'resultBufferBytesPerRow must be greater than zero.'],
            'zero share' => [[1, 2, 256, 0.0], $shareMessage],
            'negative share' => [[1, 2, 256, -0.1], $shareMessage],
            'share above one' => [[1, 2, 256, 1.01], $shareMessage],
            'infinite share' => [[1, 2, 256, INF], $shareMessage],
            'negative infinite share' => [[1, 2, 256, -INF], $shareMessage],
            'nan share' => [[1, 2, 256, NAN], $shareMessage]
        ];
    }

    public function testMemoryLimitIsInitializedAndCached(): void
    {
        $this->dataSize->expects($this->once())
            ->method('convertSizeToBytes')
            ->with((string)ini_get('memory_limit'))
            ->willReturnCallback(static fn (): int => memory_get_usage(true) + 1024 * 1024);
        $this->resource->method('getConnection')->willReturn($this->connection);
        $this->resource->method('getTableName')->with('catalogrule_product')->willReturn('catalogrule_product');
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $this->connection->method('select')->willReturn($select);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('group')->willReturnSelf();
        $select->method('order')->willReturnSelf();
        $select->method('limit')->willReturnSelf();
        $this->connection->expects($this->exactly(2))->method('fetchAll')->with($select)->willReturn([]);

        $provider = new RuleProductsSelectBatchProvider(
            $this->tableSwapper,
            $this->resource,
            $this->dataSize,
            $this->selectBuilder
        );

        self::assertSame([], iterator_to_array($provider->execute(7), false));
        self::assertSame([], iterator_to_array($provider->execute(7), false));
    }

    public function testUnlimitedMemoryUsesFinitePlanningBaseline(): void
    {
        $rows = [
            ['product_id' => 4, 'relation_rows' => 1],
            ['product_id' => 9, 'relation_rows' => 1]
        ];
        $this->configureCountPages($rows, 2, null, false);
        $this->dataSize->method('convertSizeToBytes')->willReturn(-1);
        $statement = $this->createMock(Zend_Db_Statement_Interface::class);
        $this->selectBuilder->expects($this->once())->method('buildSelect')
            ->with(7, [4, 9], false)->willReturn($statement);
        $provider = new RuleProductsSelectBatchProvider(
            $this->tableSwapper,
            $this->resource,
            $this->dataSize,
            $this->selectBuilder,
            2,
            2,
            128 * 1024 * 1024,
            1.0
        );

        self::assertSame(
            [$statement],
            iterator_to_array($provider->execute(7), false),
            'An unlimited PHP memory limit still uses the finite planning baseline.'
        );

        $memoryLimit = new ReflectionProperty(RuleProductsSelectBatchProvider::class, 'memoryLimit');
        $this->assertSame(
            756 * 1024 * 1024,
            $memoryLimit->getValue($provider),
            'The memory limit is set to 756 MiB (default unlimited memory limit budget).'
        );
    }

    #[DataProvider('batchProvider')]
    public function testBatchesPreserveCompleteProducts(
        array $fanOut,
        int $minimum,
        int $maximum,
        int $capacity,
        float $share,
        array $expectedBatches
    ): void {
        $rows = [];
        foreach ($fanOut as $id => $count) {
            $rows[] = ['product_id' => $id, 'relation_rows' => $count];
        }
        $this->configureCountPages($rows, $maximum, null, false);
        $provider = $this->providerWithCapacity($minimum, $maximum, $capacity, $share);
        $actual = [];
        $statements = [];
        $this->selectBuilder->method('buildSelect')
            ->willReturnCallback(function (int $websiteId, array $ids, bool $working) use (&$actual, &$statements) {
                self::assertSame(7, $websiteId);
                self::assertFalse($working);
                $actual[] = $ids;
                $statement = $this->createMock(Zend_Db_Statement_Interface::class);
                $statement->expects($this->never())->method('closeCursor');
                $statements[] = $statement;
                return $statement;
            });

        $generator = $provider->execute(7);
        self::assertSame([], $actual, 'Statement execution must remain lazy.');
        $results = iterator_to_array($generator, false);
        self::assertSame($statements, $results);
        self::assertSame($expectedBatches, $actual);
        self::assertCount(count($expectedBatches), $statements);
        self::assertSame(array_keys($fanOut), array_merge([], ...$actual), 'No product loss or duplication.');
    }

    public static function batchProvider(): array
    {
        return [
            'exact row boundary' => [[1 => 4, 3 => 6, 8 => 1], 1, 5, 10, 1.0, [[1, 3], [8]]],
            'floor exceeds row budget' => [[1 => 8, 2 => 8, 3 => 8, 4 => 8, 5 => 8], 2, 5, 10, 1.0,
                [[1, 2], [3, 4], [5]]],
            'zero headroom still honors floor' => [[1 => 100, 2 => 100, 3 => 100], 2, 5, 0, 0.4,
                [[1, 2], [3]]],
            'max and page boundaries' => [[1 => 1, 2 => 1, 7 => 1, 9 => 1, 20 => 1], 1, 2, 100, 1.0,
                [[1, 2], [7, 9], [20]]],
            'minimum equals maximum' => [[1 => 4, 2 => 4, 3 => 4], 2, 2, 1, 1.0, [[1, 2], [3]]],
            'fraction applied to remaining memory' => [[1 => 2, 2 => 2, 3 => 2], 1, 5, 10, 0.4,
                [[1, 2], [3]]],
            'empty relation yields no statements' => [[], 1, 2, 10, 1.0, []]
        ];
    }

    #[DataProvider('filterProvider')]
    public function testTableAndProductFilters(?array $filter, bool $working): void
    {
        $rows = [['product_id' => 4, 'relation_rows' => 1], ['product_id' => 9, 'relation_rows' => 1]];
        $this->configureCountPages($rows, 3, $filter, $working);
        $statement = $this->createMock(Zend_Db_Statement_Interface::class);
        $this->selectBuilder->expects($this->once())->method('buildSelect')
            ->with(7, [4, 9], $working)->willReturn($statement);

        self::assertSame(
            [$statement],
            iterator_to_array($this->providerWithCapacity(1, 3, 10, 1.0)->execute(7, $filter, $working), false)
        );
    }

    public static function filterProvider(): array
    {
        return [
            'main unrestricted null' => [null, false],
            'main unrestricted empty' => [[], false],
            'main selected ids' => [[4, 9], false],
            'working unrestricted null' => [null, true],
            'working unrestricted empty' => [[], true],
            'working selected ids' => [[4, 9], true]
        ];
    }

    private function providerWithCapacity(
        int $minimum,
        int $maximum,
        int $capacity,
        float $share
    ): RuleProductsSelectBatchProvider {
        // Isolate packing from the separately tested memory-limit initialization.
        $this->dataSize->method('convertSizeToBytes')->willReturnCallback(
            static fn (): int => memory_get_usage(true) + $capacity * 1024 * 1024
        );
        return new RuleProductsSelectBatchProvider(
            $this->tableSwapper,
            $this->resource,
            $this->dataSize,
            $this->selectBuilder,
            $minimum,
            $maximum,
            1024 * 1024,
            $share
        );
    }

    private function configureCountPages(array $rows, int $maximum, ?array $filter, bool $working): void
    {
        $this->resource->method('getConnection')->willReturn($this->connection);
        $this->resource->method('getTableName')->willReturnCallback(static fn (string $name): string => $name);
        if ($working) {
            $this->tableSwapper->expects($this->once())->method('getWorkingTableName')
                ->with('catalogrule_product')->willReturn('catalogrule_product__working');
        } else {
            $this->tableSwapper->expects($this->never())->method('getWorkingTableName');
        }
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $this->connection->method('select')->willReturn($select);
        $select->method('from')->with(
            $working ? 'catalogrule_product__working' : 'catalogrule_product',
            $this->callback(static fn (array $columns): bool =>
                $columns[0] === 'product_id' && (string)$columns['relation_rows'] === 'COUNT(*)')
        )->willReturnSelf();
        $predicates = [];
        $select->method('where')->willReturnCallback(
            static function (string $condition, $value) use (&$predicates, $select) {
                $predicates[$condition] = $value;
                return $select;
            }
        );
        $select->method('group')->with('product_id')->willReturnSelf();
        $select->method('order')->with('product_id')->willReturnSelf();
        $select->method('limit')->with($maximum)->willReturnSelf();
        $pages = array_chunk($rows, $maximum);
        if ($rows === [] || count($rows) % $maximum === 0) {
            $pages[] = [];
        }
        $lastId = 0;
        $this->connection->expects($this->exactly(count($pages)))->method('fetchAll')->with($select)
            ->willReturnCallback(static function () use (&$pages, &$lastId, &$predicates, $filter): array {
                $expected = ['website_id = ?' => 7, 'product_id > ?' => $lastId];
                if (!empty($filter)) {
                    $expected['product_id IN (?)'] = $filter;
                }
                self::assertSame($expected, $predicates);
                $predicates = [];
                $page = array_shift($pages);
                if ($page) {
                    $lastId = (int)$page[array_key_last($page)]['product_id'];
                }
                return $page;
            });
    }
}
