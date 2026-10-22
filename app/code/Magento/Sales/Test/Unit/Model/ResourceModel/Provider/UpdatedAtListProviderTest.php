<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\ResourceModel\Provider\UpdatedAtListProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdatedAtListProviderTest extends TestCase
{
    private const CUTOFF = '2026-09-23 10:00:00';

    /**
     * @var LastUpdateTimeCache&MockObject
     */
    private $lastUpdateTimeCache;

    /**
     * @var ScopeConfigInterface&MockObject
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $whereConditions = [];

    /**
     * @var UpdatedAtListProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $resourceConnection = $this->createStub(ResourceConnection::class);
        $connection = $this->createStub(AdapterInterface::class);
        $this->lastUpdateTimeCache = $this->createMock(LastUpdateTimeCache::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $resourceConnection->method('getConnection')->willReturn($connection);
        $resourceConnection->method('getTableName')
            ->willReturnCallback(static fn(string $table): string => $table);

        $select = $this->createStub(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('joinInner')->willReturnSelf();
        $select->method('where')->willReturnCallback(
            function (string $condition, $value) use ($select): Select {
                $this->whereConditions[] = [$condition, $value];
                return $select;
            }
        );
        $connection->method('select')->willReturn($select);
        $connection->method('fetchAll')->willReturn(['7']);

        $this->provider = new UpdatedAtListProvider(
            $resourceConnection,
            $this->lastUpdateTimeCache,
            $this->scopeConfig
        );
    }

    #[DataProvider('watermarkLookbackDataProvider')]
    public function testWatermarkLowerBoundIsMovedBackByLookback(
        string $watermark,
        mixed $lookback,
        string $expectedLowerBound
    ): void {
        $this->lastUpdateTimeCache->expects($this->once())
            ->method('get')
            ->with('sales_order_grid')
            ->willReturn($watermark);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing_lookback')
            ->willReturn($lookback);

        $ids = $this->provider->getIdsWithCutoff('sales_order', 'sales_order_grid', self::CUTOFF);

        $this->assertSame(['7'], $ids);
        $this->assertSame(
            [
                ['main_table.updated_at <= ?', self::CUTOFF],
                ['main_table.updated_at >= ?', $expectedLowerBound],
            ],
            $this->whereConditions
        );
    }

    /**
     * @return array
     */
    public static function watermarkLookbackDataProvider(): array
    {
        return [
            'default lookback' => ['2026-09-23 09:59:00', '300', '2026-09-23 09:54:00'],
            'lookback across midnight' => ['2026-09-23 00:01:00', '300', '2026-09-22 23:56:00'],
            'lookback disabled' => ['2026-09-23 09:59:00', '0', '2026-09-23 09:59:00'],
            'lookback not configured' => ['2026-09-23 09:59:00', null, '2026-09-23 09:59:00'],
            'unparsable watermark kept as is' => ['2026-09-23T09:59:00+00:00', '300', '2026-09-23T09:59:00+00:00'],
        ];
    }

    public function testNoLowerBoundWithoutWatermark(): void
    {
        $this->lastUpdateTimeCache->expects($this->once())
            ->method('get')
            ->with('sales_order_grid')
            ->willReturn(null);
        $this->scopeConfig->expects($this->never())->method('getValue');

        $this->provider->getIdsWithCutoff('sales_order', 'sales_order_grid', self::CUTOFF);

        $this->assertSame([['main_table.updated_at <= ?', self::CUTOFF]], $this->whereConditions);
    }
}
