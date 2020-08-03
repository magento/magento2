<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

/**
 * Dashboard period info retriever
 */
class Period
{
    public const PERIOD_24_HOURS = '24h';
    public const PERIOD_7_DAYS = '7d';
    public const PERIOD_1_MONTH = '1m';
    public const PERIOD_1_YEAR = '1y';
    public const PERIOD_2_YEARS = '2y';

    private const PERIOD_UNIT_HOUR = 'hour';
    private const PERIOD_UNIT_DAY = 'day';
    private const PERIOD_UNIT_MONTH = 'month';

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     */
    public function getDatePeriods(): array
    {
        return [
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
            static::PERIOD_24_HOURS => static::PERIOD_UNIT_HOUR,
            static::PERIOD_7_DAYS => static::PERIOD_UNIT_DAY,
            static::PERIOD_1_MONTH => static::PERIOD_UNIT_DAY,
            static::PERIOD_1_YEAR => static::PERIOD_UNIT_MONTH,
            static::PERIOD_2_YEARS => static::PERIOD_UNIT_MONTH
        ];
    }
}
