<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report;

use Exception;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Reports\Model\ResourceModel\Report\AbstractReport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Zend_Db_Statement_Interface;

class AbstractReportTest extends TestCase
{
    /**
     * Database adapter double
     *
     * @var AdapterInterface|MockObject
     */
    private MockObject $connection;

    /**
     * Report resource under test
     *
     * @var AbstractReport|Stub
     */
    private Stub $report;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->report = $this->createStub(AbstractReport::class);
        $this->report->method('getConnection')->willReturn($this->connection);
    }

    public function testConditionMatchesPeriodByIndexableInList(): void
    {
        $select = $this->createSelect('select-with-dates');
        $this->connection->method('query')->with($select)->willReturn($this->createStatement(
            ['2026-11-08', '2026-11-09']
        ));
        $this->connection->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('period', ['in' => ['2026-11-08', '2026-11-09']])
            ->willReturn('period-condition');

        $this->assertSame('period-condition', $this->makeCondition($select, 'period'));
    }

    public function testEmptyDateRangeProducesFalseCondition(): void
    {
        $select = $this->createSelect('select-without-dates');
        $this->connection->method('query')->with($select)->willReturn($this->createStatement([]));
        $this->connection->expects($this->never())->method('prepareSqlCondition');

        $this->assertSame('1=0', $this->makeCondition($select, 'period'));
    }

    public function testFailedDateRangeQueryProducesNoCondition(): void
    {
        $select = $this->createSelect('select-that-fails');
        $this->connection->method('query')->willThrowException(new Exception('query failed'));
        $this->connection->expects($this->never())->method('prepareSqlCondition');

        $this->assertFalse($this->makeCondition($select, 'period'));
    }

    private function makeCondition(Select $select, string $periodColumn): string|false
    {
        $method = new ReflectionMethod(AbstractReport::class, '_makeConditionFromDateRangeSelect');

        return $method->invoke($this->report, $select, $periodColumn);
    }

    /**
     * The select string must be unique per test: results are cached by it in a process-wide static.
     */
    private function createSelect(string $sql): Select
    {
        $select = $this->createStub(Select::class);
        $select->method('__toString')->willReturn($sql);

        return $select;
    }

    private function createStatement(array $dates): Zend_Db_Statement_Interface
    {
        $statement = $this->createStub(Zend_Db_Statement_Interface::class);
        $statement->method('fetchColumn')->willReturnOnConsecutiveCalls(...array_merge($dates, [false]));

        return $statement;
    }
}
