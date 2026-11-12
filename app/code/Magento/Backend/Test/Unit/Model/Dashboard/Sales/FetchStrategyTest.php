<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Dashboard\Sales;

use Magento\Backend\Model\Dashboard\Sales\FetchStrategy;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FetchStrategyTest extends TestCase
{
    /**
     * @var Query|MockObject
     */
    private Query $query;

    /**
     * @var Block|MockObject
     */
    private Block $cache;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var Select
     */
    private Select $select;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->query = $this->createMock(Query::class);
        $this->cache = $this->createMock(Block::class);
        $this->serializer = new Json();
        $this->select = $this->createStub(Select::class);
    }

    public function testCacheIsDisabledByDefault(): void
    {
        $data = [['lifetime' => '125.0000', 'average' => '25.0000']];
        $bindParams = ['store_id' => 1];
        $this->query->expects($this->once())->method('fetchAll')
            ->with($this->select, $bindParams)->willReturn($data);
        $this->cache->expects($this->never())->method('load');
        $this->cache->expects($this->never())->method('save');

        $strategy = new FetchStrategy($this->query, $this->cache, $this->serializer);

        $this->assertSame($data, $strategy->fetchAll($this->select, $bindParams));
    }

    #[DataProvider('salesDataProvider')]
    public function testCacheMissSavesRawTotalsWithConfiguredTtl(array $data): void
    {
        $cacheId = null;
        $this->select->method('__toString')->willReturn('SELECT SUM(amount), AVG(amount) FROM sales_order');
        $this->cache->expects($this->once())->method('load')
            ->willReturnCallback(static function ($id) use (&$cacheId) {
                $cacheId = $id;
                return false;
            });
        $this->query->expects($this->once())->method('fetchAll')->with($this->select, [])->willReturn($data);
        $this->cache->expects($this->once())->method('save')->with(
            $this->serializer->serialize($data),
            $this->callback(static function ($id) use (&$cacheId) {
                return $id === $cacheId;
            }),
            [],
            30
        );

        $strategy = new FetchStrategy($this->query, $this->cache, $this->serializer, 30);

        $this->assertSame($data, $strategy->fetchAll($this->select));
    }

    #[DataProvider('salesDataProvider')]
    public function testCacheHitSkipsDatabaseQuery(array $data): void
    {
        $this->select->method('__toString')->willReturn('SELECT SUM(amount), AVG(amount) FROM sales_order');
        $this->cache->expects($this->once())->method('load')
            ->willReturn($this->serializer->serialize($data));
        $this->query->expects($this->never())->method('fetchAll');
        $this->cache->expects($this->never())->method('save');

        $strategy = new FetchStrategy($this->query, $this->cache, $this->serializer, 30);

        $this->assertSame($data, $strategy->fetchAll($this->select));
    }

    public static function salesDataProvider(): array
    {
        return [
            'decimal strings' => [[['lifetime' => '1234567890123456.7890', 'average' => '12.34567890']]],
            'no matching orders' => [[['lifetime' => null, 'average' => null]]],
            'zero totals' => [[['lifetime' => '0.0000', 'average' => '0.00000000']]]
        ];
    }

    #[DataProvider('invalidCacheDataProvider')]
    public function testInvalidCacheEntryIsReplaced(string $cachedData): void
    {
        $data = [['lifetime' => '125.0000', 'average' => '25.0000']];
        $this->select->method('__toString')->willReturn('SELECT SUM(amount), AVG(amount) FROM sales_order');
        $this->cache->expects($this->once())->method('load')->willReturn($cachedData);
        $this->query->expects($this->once())->method('fetchAll')->with($this->select, [])->willReturn($data);
        $this->cache->expects($this->once())->method('save')
            ->with($this->serializer->serialize($data), $this->isString(), [], 30);

        $strategy = new FetchStrategy($this->query, $this->cache, $this->serializer, 30);

        $this->assertSame($data, $strategy->fetchAll($this->select));
    }

    public static function invalidCacheDataProvider(): array
    {
        return [
            'invalid JSON' => ['invalid'],
            'scalar' => ['false'],
            'invalid row' => ['[1]'],
            'missing total' => ['[{"lifetime":"125.0000"}]']
        ];
    }

    #[DataProvider('queryContextDataProvider')]
    public function testCacheSeparatesFinalSqlAndBindings(
        string $firstSql,
        array $firstBindings,
        string $secondSql,
        array $secondBindings
    ): void {
        $entries = [];
        $firstData = [['lifetime' => '125.0000', 'average' => '25.0000']];
        $secondData = [['lifetime' => '300.0000', 'average' => '30.0000']];
        $this->select->method('__toString')->willReturn($firstSql);
        $secondSelect = $this->createStub(Select::class);
        $secondSelect->method('__toString')->willReturn($secondSql);
        $this->cache->expects($this->exactly(3))->method('load')->willReturnCallback(
            static function ($id) use (&$entries) {
                return $entries[$id] ?? false;
            }
        );
        $this->cache->expects($this->exactly(2))->method('save')->willReturnCallback(
            static function ($data, $id) use (&$entries) {
                $entries[$id] = $data;
                return true;
            }
        );
        $this->query->expects($this->exactly(2))->method('fetchAll')
            ->willReturnOnConsecutiveCalls($firstData, $secondData);
        $strategy = new FetchStrategy($this->query, $this->cache, $this->serializer, 30);

        $this->assertSame($firstData, $strategy->fetchAll($this->select, $firstBindings));
        $this->assertSame($secondData, $strategy->fetchAll($secondSelect, $secondBindings));
        $this->assertSame($firstData, $strategy->fetchAll($this->select, $firstBindings));
    }

    public static function queryContextDataProvider(): array
    {
        return [
            'different rendered store filters' => [
                'SELECT SUM(base_total_invoiced) FROM sales_order WHERE store_id = 1',
                [],
                'SELECT SUM(base_total_invoiced) FROM sales_order WHERE store_id = 2',
                []
            ],
            'different store bindings' => [
                'SELECT SUM(base_total_invoiced) FROM sales_order WHERE store_id = :store_id',
                ['store_id' => 1],
                'SELECT SUM(base_total_invoiced) FROM sales_order WHERE store_id = :store_id',
                ['store_id' => 2]
            ],
            'live versus aggregated data' => [
                'SELECT SUM(base_total_invoiced) FROM sales_order',
                [],
                'SELECT SUM(total_revenue_amount) FROM sales_order_aggregated_created',
                []
            ]
        ];
    }
}
