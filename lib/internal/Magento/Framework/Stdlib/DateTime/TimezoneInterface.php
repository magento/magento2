<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Stdlib\DateTime;

interface TimezoneInterface
{
    /**
     * Return path to default timezone
     *
     * @return string
     */
    public function getDefaultTimezonePath();

    /**
     * Retrieve timezone code
     *
     * @return string
     */
    public function getDefaultTimezone();

    /**
     * Retrieve ISO date format
     *
     * @param   int $type
     * @return  string
     */
    public function getDateFormat($type = \IntlDateFormatter::SHORT);

    /**
     * Retrieve short date format with 4-digit year
     *
     * @return  string
     */
    public function getDateFormatWithLongYear();

    /**
     * Retrieve ISO time format
     *
     * @param   string $type
     * @return  string
     */
    public function getTimeFormat($type = null);

    /**
     * Retrieve ISO datetime format
     *
     * @param   string $type
     * @return  string
     */
    public function getDateTimeFormat($type);

    /**
     * Create \DateTime object for current locale
     *
     * @param mixed              $date
     * @param string             $part
     * @param string|Zend_Locale $locale
     * @param bool               $useTimezone
     * @return \DateTime
     */
    public function date($date = null, $part = null, $locale = null, $useTimezone = true);

    /**
     * Create \DateTime object with date converted to scope timezone and scope Locale
     *
     * @param   mixed $scope Information about scope
     * @param   string|integer|\DateTime|array|null $date date in UTC
     * @param   boolean $includeTime flag for including time to date
     * @return  \DateTime
     */
    public function scopeDate($scope = null, $date = null, $includeTime = false);

    /**
     * Create \DateTime object with date converted from scope's timezone
     * to UTC time zone. Date can be passed in format of scope's locale
     * or in format which was passed as parameter.
     *
     * @param mixed $scope Information about scope
     * @param string|integer|\DateTime|array|null $date date in scope's timezone
     * @param boolean $includeTime flag for including time to date
     * @return \DateTime
     */
    public function utcDate($scope, $date, $includeTime = false);

    /**
     * Get scope timestamp
     * Timestamp will be built with scope timezone settings
     *
     * @param   mixed $scope
     * @return  int
     */
    public function scopeTimeStamp($scope = null);

    /**
     * Format date using current locale options and time zone.
     *
     * @param \DateTime|null $date
     * @param string $format
     * @param bool $showTime
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false
    );

    /**
     * Format time using current locale options
     *
     * @param \DateTime|null $time
     * @param string $format
     * @param bool $showDate
     * @return string
     */
    public function formatTime(
        $time = null,
        $format = \IntlDateFormatter::SHORT,
        $showDate = false
    );

    /**
     * Gets the scope config timezone
     *
     * @return string
     */
    public function getConfigTimezone();

    /**
     * Checks if current date of the given scope (in the scope timezone) is within the range
     *
     * @param int|string|\Magento\Framework\App\ScopeInterface $scope
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return bool
     */
    public function isScopeDateInInterval($scope, $dateFrom = null, $dateTo = null);

    /**
     * @param \DateTimeInterface $date
     * @param int $dateType
     * @param int $timeType
     * @param null $locale
     * @param null $timezone
     * @return mixed
     */
    public function formatDateTime(
        \DateTimeInterface $date,
        $dateType = \IntlDateFormatter::SHORT,
        $timeType = \IntlDateFormatter::SHORT,
        $locale = null,
        $timezone = null
    );
}
