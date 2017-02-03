<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param string $locale
     * @param bool               $useTimezone
     * @return \DateTime
     */
    public function date($date = null, $locale = null, $useTimezone = true);

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
     * @param int $format
     * @param bool $showTime
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false
    );

    /**
     * Gets the scope config timezone
     *
     * @param string $scopeType
     * @param string $scopeCode
     * @return string
     */
    public function getConfigTimezone($scopeType = null, $scopeCode = null);

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
     * @param string|\DateTimeInterface $date
     * @param int $dateType
     * @param int $timeType
     * @param null $locale
     * @param null $timezone
     * @param string|null $pattern
     * @return string
     */
    public function formatDateTime(
        $date,
        $dateType = \IntlDateFormatter::SHORT,
        $timeType = \IntlDateFormatter::SHORT,
        $locale = null,
        $timezone = null,
        $pattern = null
    );
}
