<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Stdlib\DateTime;

interface TimezoneInterface
{
    /**
     * Default timezone
     */
    const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Date and time format codes
     */
    const FORMAT_TYPE_FULL = 'full';

    const FORMAT_TYPE_LONG = 'long';

    const FORMAT_TYPE_MEDIUM = 'medium';

    const FORMAT_TYPE_SHORT = 'short';

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
     * @param   string $type
     * @return  string
     */
    public function getDateFormat($type = null);

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
     * Create \Magento\Framework\Stdlib\DateTime\DateInterface object for current locale
     *
     * @param mixed              $date
     * @param string             $part
     * @param string|Zend_Locale $locale
     * @param bool               $useTimezone
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function date($date = null, $part = null, $locale = null, $useTimezone = true);

    /**
     * Create \Magento\Framework\Stdlib\DateTime\DateInterface object with date converted to scope timezone and scope Locale
     *
     * @param   mixed $scope Information about scope
     * @param   string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface|array|null $date date in UTC
     * @param   boolean $includeTime flag for including time to date
     * @return  \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function scopeDate($scope = null, $date = null, $includeTime = false);

    /**
     * Create \Magento\Framework\Stdlib\DateTime\DateInterface object with date converted from scope's timezone
     * to UTC time zone. Date can be passed in format of scope's locale
     * or in format which was passed as parameter.
     *
     * @param mixed $scope Information about scope
     * @param string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface|array|null $date date in scope's timezone
     * @param boolean $includeTime flag for including time to date
     * @param null|string $format
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function utcDate($scope, $date, $includeTime = false, $format = null);

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
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface|null $date
     * @param string $format
     * @param bool $showTime
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
        $showTime = false
    );

    /**
     * Format time using current locale options
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface|null $time
     * @param string $format
     * @param bool $showDate
     * @return string
     */
    public function formatTime(
        $time = null,
        $format = \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
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
}
