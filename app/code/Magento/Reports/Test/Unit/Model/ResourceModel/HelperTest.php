<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Reports\Model\ResourceModel\Helper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * @var ResourceConnection|Stub
     */
    protected $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->createStub(ResourceConnection::class);

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();

        $this->resourceMock
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
    }

    /**
     * @return void
     */
    public function testMergeVisitorProductIndex()
    {
        $mainTable = 'mainTable';
        $data = ['dataKey' => 'dataValue'];
        $matchFields = ['matchField'];

        $this->connectionMock
            ->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($mainTable, $data, array_keys($data));

        $helper = new Helper(
            $this->resourceMock,
            $this->storeManager
        );
        $helper->mergeVisitorProductIndex($mainTable, $data, $matchFields);
    }

    /**
     * @param string $type
     * @param array $result
     * @param bool $exists
     * @return void
     */
    #[DataProvider('typesDataProvider')]
    public function testUpdateReportRatingPos($type, $result, $exists)
    {
        $mainTable = 'mainTable';
        $column = 'column';
        $aggregationTable = 'aggregationTable';

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn([$store]);
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock
            ->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('group')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('order')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('insertFromSelect')
            ->with($aggregationTable, $result)
            ->willReturnSelf();

        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $this->connectionMock
            ->expects($this->atLeastOnce())
            ->method('tableColumnExists')
            ->willReturn($exists);

        $helper = new Helper(
            $this->resourceMock,
            $this->storeManager
        );
        $helper->updateReportRatingPos($this->connectionMock, $type, $column, $mainTable, $aggregationTable);
    }

    /**
     * @param string|null $type
     * @param bool $periodMonthExists
     * @param string $expectedCondition
     * @return void
     */
    #[DataProvider('periodsDataProvider')]
    public function testUpdateReportRatingPosForPeriodsRestrictsToPeriodsContainingGivenDays(
        ?string $type,
        bool $periodMonthExists,
        string $expectedCondition
    ): void {
        $whereConditions = $this->runUpdateReportRatingPos(
            $type,
            $periodMonthExists,
            ['2024-12-31', '2025-01-20', '2025-01-21', '2025-01-20']
        );

        $this->assertSame([$expectedCondition, 't.store_id = 1'], $whereConditions);
    }

    /**
     * @return array
     */
    public static function periodsDataProvider(): array
    {
        $months = '(t.period >= 2024-12-01 AND t.period <= 2024-12-31)'
            . ' OR (t.period >= 2025-01-01 AND t.period <= 2025-01-31)';
        return [
            'day' => ['day', true, 't.period IN(2024-12-31,2025-01-20,2025-01-21)'],
            'default' => [null, false, 't.period IN(2024-12-31,2025-01-20,2025-01-21)'],
            'month with period_month column' => ['month', true, $months],
            'month without period_month column' => ['month', false, $months],
            'year' => [
                'year',
                true,
                '(t.period >= 2024-01-01 AND t.period <= 2024-12-31)'
                . ' OR (t.period >= 2025-01-01 AND t.period <= 2025-12-31)'
            ],
        ];
    }

    /**
     * @param array $periods
     * @return void
     */
    #[DataProvider('invalidPeriodsDataProvider')]
    public function testUpdateReportRatingPosForPeriodsWithInvalidValueRecalculatesAllPeriods(array $periods): void
    {
        $whereConditions = $this->runUpdateReportRatingPos('day', true, $periods);

        $this->assertSame(['t.store_id = 1'], $whereConditions);
    }

    /**
     * @return array
     */
    public static function invalidPeriodsDataProvider(): array
    {
        return [
            'relative date' => [['2025-01-20', 'yesterday']],
            'non-existent day' => [['2025-01-20', '2025-02-30']],
            'unpadded' => [['2025-1-5', '2025-01-20']],
            'with time' => [['2025-01-21 10:00:00']],
            'null' => [['2025-01-20', null]],
            'integer' => [[20250120]],
            'month 13' => [['2025-13-01']],
        ];
    }

    /**
     * @return void
     */
    public function testUpdateReportRatingPosRecalculatesAllPeriods(): void
    {
        $whereConditions = $this->runUpdateReportRatingPos('month', true, null);

        $this->assertSame(['t.store_id = 1'], $whereConditions);
    }

    /**
     * @return void
     */
    public function testUpdateReportRatingPosForPeriodsWithEmptyPeriodsDoesNothing(): void
    {
        $this->storeManager->expects($this->never())->method('getStores');
        $this->connectionMock->expects($this->never())->method('query');

        $helper = new Helper($this->resourceMock, $this->storeManager);
        $helper->updateReportRatingPosForPeriods(
            $this->connectionMock,
            'day',
            'qty_ordered',
            'mainTable',
            'aggregationTable',
            []
        );
    }

    /**
     * Run the rating update for one store and return every WHERE condition added to its selects
     *
     * @param string|null $type
     * @param bool $periodMonthExists
     * @param array|null $periods Null runs updateReportRatingPos(), an array updateReportRatingPosForPeriods()
     * @return array
     */
    private function runUpdateReportRatingPos(?string $type, bool $periodMonthExists, ?array $periods): array
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn([$store]);

        $whereConditions = [];
        $selectMock = $this->createStub(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('group')->willReturnSelf();
        $selectMock->method('order')->willReturnSelf();
        $selectMock->method('where')->willReturnCallback(
            function ($condition) use (&$whereConditions, $selectMock) {
                $whereConditions[] = $condition;
                return $selectMock;
            }
        );
        $selectMock->method('insertFromSelect')->willReturn('INSERT');

        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->method('tableColumnExists')->willReturn($periodMonthExists);
        $this->connectionMock->method('getDateFormatSql')->willReturn('formatted_period');
        $this->connectionMock->method('prepareSqlCondition')->willReturnCallback(
            static function (string $field, array $condition): string {
                if (isset($condition['in'])) {
                    return $field . ' IN(' . implode(',', $condition['in']) . ')';
                }
                return $field . ' >= ' . $condition['from'] . ' AND ' . $field . ' <= ' . $condition['to'];
            }
        );
        $this->connectionMock->expects($this->exactly(2))->method('query');

        $helper = new Helper($this->resourceMock, $this->storeManager);
        if ($periods === null) {
            $helper->updateReportRatingPos($this->connectionMock, $type, 'qty_ordered', 'mainTable', 'aggTable');
        } else {
            $helper->updateReportRatingPosForPeriods(
                $this->connectionMock,
                $type,
                'qty_ordered',
                'mainTable',
                'aggTable',
                $periods
            );
        }

        return $whereConditions;
    }

    /**
     * @return array
     */
    public static function typesDataProvider()
    {
        $mResult = [
            'period',
            'store_id',
            'product_id',
            'product_name',
            'product_price',
            'column',
            'rating_pos'
        ];
        $dResult = [
            'period',
            'store_id',
            'product_id',
            'product_name',
            'product_price',
            'id',
            'column',
            'rating_pos'
        ];
        return [
            ['type' => 'year', 'result' => $mResult, 'exists' => false],
            ['type' => 'month', 'result' => [
                'store_id',
                'product_id',
                'product_name',
                'product_price',
                'period',
                'column',
                'rating_pos'
            ], 'exists' => true],
            ['type' => 'day', 'result' => $dResult, 'exists' => false],
            ['type' => null, 'result' => $mResult, 'exists' => false]
        ];
    }
}
