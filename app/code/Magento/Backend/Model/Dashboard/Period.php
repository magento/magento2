<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Dashboard period info retriever
 */
class Period
{
    private const CONFIG_PATH_USE_AGGREGATED_DATA = 'sales/dashboard/use_aggregated_data';

    public const PERIOD_TODAY = 'today';
    public const PERIOD_24_HOURS = '24h';
    public const PERIOD_7_DAYS = '7d';
    public const PERIOD_1_MONTH = '1m';
    public const PERIOD_1_YEAR = '1y';
    public const PERIOD_2_YEARS = '2y';

    private const PERIOD_UNIT_HOUR = 'hour';
    private const PERIOD_UNIT_DAY = 'day';
    private const PERIOD_UNIT_MONTH = 'month';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     */
    public function getDatePeriods(): array
    {
        return [
            static::PERIOD_TODAY => __('Today'),
            static::PERIOD_24_HOURS => __('Last 24 Hours'),
            static::PERIOD_7_DAYS => __('Last 7 Days'),
            static::PERIOD_1_MONTH => __('Current Month'),
            static::PERIOD_1_YEAR => __('YTD'),
            static::PERIOD_2_YEARS => __('2YTD')
        ];
    }

    /**
     * Prepare array with periods mapping to chart units
     *
     * @return array
     */
    public function getPeriodChartUnits(): array
    {
        return [
            static::PERIOD_TODAY => self::PERIOD_UNIT_HOUR,
            static::PERIOD_24_HOURS => self::PERIOD_UNIT_HOUR,
            static::PERIOD_7_DAYS => self::PERIOD_UNIT_DAY,
            static::PERIOD_1_MONTH => self::PERIOD_UNIT_DAY,
            static::PERIOD_1_YEAR => self::PERIOD_UNIT_MONTH,
            static::PERIOD_2_YEARS => self::PERIOD_UNIT_MONTH
        ];
    }

    /**
     * Check if aggregated dashboard data is enabled
     *
     * @return bool
     */
    public function isAggregatedDataEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_PATH_USE_AGGREGATED_DATA,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get default dashboard period based on configuration
     *
     * @return string
     */
    public function getDefaultPeriod(): string
    {
        if ($this->isAggregatedDataEnabled()) {
            return static::PERIOD_7_DAYS;
        }

        return static::PERIOD_TODAY;
    }

    /**
     * Resolve dashboard period from request value
     *
     * @param string|null $period
     * @return string
     */
    public function resolvePeriod(?string $period): string
    {
        $availablePeriods = array_keys($this->getDatePeriods());
        if ($period !== null && $period !== '' && in_array($period, $availablePeriods, true)) {
            return $period;
        }

        return $this->getDefaultPeriod();
    }
}
