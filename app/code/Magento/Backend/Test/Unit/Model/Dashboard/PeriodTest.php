<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Dashboard;

use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PeriodTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Period
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->model = new Period($this->scopeConfigMock);
    }

    /**
     * Test getDatePeriods() method
     */
    public function testGetDatePeriods(): void
    {
        $this->assertEquals(
            [
                Period::PERIOD_TODAY => (string)__('Today'),
                Period::PERIOD_24_HOURS => (string)__('Last 24 Hours'),
                Period::PERIOD_7_DAYS => (string)__('Last 7 Days'),
                Period::PERIOD_1_MONTH => (string)__('Current Month'),
                Period::PERIOD_1_YEAR => (string)__('YTD'),
                Period::PERIOD_2_YEARS => (string)__('2YTD')
            ],
            $this->model->getDatePeriods()
        );
    }

    /**
     * Test getPeriodChartUnits() method
     */
    public function testGetPeriodChartUnits(): void
    {
        $this->assertEquals(
            [
                Period::PERIOD_TODAY => 'hour',
                Period::PERIOD_24_HOURS => 'hour',
                Period::PERIOD_7_DAYS => 'day',
                Period::PERIOD_1_MONTH => 'day',
                Period::PERIOD_1_YEAR => 'month',
                Period::PERIOD_2_YEARS => 'month',
            ],
            $this->model->getPeriodChartUnits()
        );
    }

    /**
     * @return array
     */
    public static function getDefaultPeriodDataProvider(): array
    {
        return [
            'aggregated data enabled' => ['1', Period::PERIOD_7_DAYS],
            'aggregated data disabled' => ['0', Period::PERIOD_TODAY],
        ];
    }

    /**
     * @param string $configValue
     * @param string $expectedPeriod
     * @return void
     */
    #[DataProvider('getDefaultPeriodDataProvider')]
    public function testGetDefaultPeriod(string $configValue, string $expectedPeriod): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/dashboard/use_aggregated_data', ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        $this->assertSame($expectedPeriod, $this->model->getDefaultPeriod());
    }

    /**
     * @return array
     */
    public static function resolvePeriodDataProvider(): array
    {
        return [
            'valid period is preserved' => ['7d', '0', '7d'],
            'empty period uses default when aggregated' => [null, '1', Period::PERIOD_7_DAYS],
            'empty period uses default when live' => [null, '0', Period::PERIOD_TODAY],
            'invalid period uses default when aggregated' => ['invalid', '1', Period::PERIOD_7_DAYS],
        ];
    }

    /**
     * @param string|null $requestPeriod
     * @param string $configValue
     * @param string $expectedPeriod
     * @return void
     */
    #[DataProvider('resolvePeriodDataProvider')]
    public function testResolvePeriod(?string $requestPeriod, string $configValue, string $expectedPeriod): void
    {
        $this->scopeConfigMock->expects($this->atMost(1))
            ->method('getValue')
            ->with('sales/dashboard/use_aggregated_data', ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        $this->assertSame($expectedPeriod, $this->model->resolvePeriod($requestPeriod));
    }
}
