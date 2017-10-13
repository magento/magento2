<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher\MatcherInterface as PartMatcherInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression part matcher
 *
 * @api
 */
class MatcherFactory
{
    const GENERIC_MATCHER = 'Generic';
    const MINUTES_MATCHER = 'Minutes';
    const HOURS_MATCHER = 'Hours';
    const MONTHDAY_MATCHER = 'MonthDay';
    const MONTH_MATCHER = 'Month';
    const WEEKDAY_MATCHER = 'WeekDay';
    const YEAR_MATCHER = 'Year';

    /**
     * @return array
     */
    public function getAvailableMatchers()
    {
        return [
            self::GENERIC_MATCHER,
            self::MINUTES_MATCHER,
            self::HOURS_MATCHER,
            self::MONTHDAY_MATCHER,
            self::MONTH_MATCHER,
            self::WEEKDAY_MATCHER,
            self::YEAR_MATCHER,
        ];
    }

    /**
     * Get the matcher specified by matcher type
     *
     * @param string $matcherType
     *
     * @throws CronException
     * @return PartMatcherInterface
     */
    public function create($matcherType)
    {
        if (!in_array($matcherType, $this->getAvailableMatchers())) {
            throw new CronException(__('Invalid cron expression part matcher type: %1', $matcherType));
        }

        $matcher = ObjectManager::getInstance()
            ->get('Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher\\' . $matcherType);

        if (!$matcher instanceof PartMatcherInterface) {
            $exceptionMessage = 'Invalid cron expression part matcher type: %1 is not an instance of '
                . PartMatcherInterface::class;
            throw new CronException(__($exceptionMessage, $matcherType));
        }

        return $matcher;
    }
}
