<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParser\NumericInterface as PartNumericInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression part numeric
 *
 * @api
 */
class NumericParserFactory
{
    const GENERIC_NUMERIC = 'Generic';
    const MINUTES_NUMERIC = 'Minutes';
    const HOURS_NUMERIC = 'Hours';
    const MONTHDAY_NUMERIC = 'MonthDay';
    const MONTH_NUMERIC = 'Month';
    const WEEKDAY_NUMERIC = 'WeekDay';
    const YEAR_NUMERIC = 'Year';

    /**
     * @return array
     */
    public function getAvailableNumerics()
    {
        return [
            self::GENERIC_NUMERIC,
            self::MINUTES_NUMERIC,
            self::HOURS_NUMERIC,
            self::MONTHDAY_NUMERIC,
            self::MONTH_NUMERIC,
            self::WEEKDAY_NUMERIC,
            self::YEAR_NUMERIC,
        ];
    }

    /**
     * Get the numeric specified by numeric type
     *
     * @param string $numericType
     *
     * @throws CronException
     * @return PartNumericInterface
     */
    public function create($numericType)
    {
        if (!in_array($numericType, $this->getAvailableNumerics())) {
            throw new CronException(__('Invalid cron expression part numeric type: %1', $numericType));
        }

        $numeric = ObjectManager::getInstance()
            ->get('Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParser\\' . $numericType);

        if (!$numeric instanceof PartNumericInterface) {
            $exceptionMessage = 'Invalid cron expression part numeric type: %1 is not an instance of '
                . PartNumericInterface::class;
            throw new CronException(__($exceptionMessage, $numericType));
        }

        return $numeric;
    }
}
