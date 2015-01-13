<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime;

interface DateInterface
{
    /**
     * Sets class wide options, if no option was given, the actual set options will be returned
     *
     * @param  array  $options  \Options to set
     * @throws \Zend_Date_Exception
     * @return array of options if no option was given
     */
    public static function setOptions(array $options = []);

    /**
     * Returns this object's internal UNIX timestamp (equivalent to \Zend_Date::TIMESTAMP).
     * If the timestamp is too large for integers, then the return value will be a string.
     * This function does not return the timestamp as an object.
     * Use clone() or copyPart() instead.
     *
     * @return integer|string  UNIX timestamp
     */
    public function getTimestamp();

    /**
     * Sets a new timestamp
     *
     * @param  integer|string|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $timestamp  Timestamp to set
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setTimestamp($timestamp);

    /**
     * Adds a timestamp
     *
     * @param  integer|string|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $timestamp  Timestamp to add
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addTimestamp($timestamp);

    /**
     * Subtracts a timestamp
     *
     * @param  integer|string|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $timestamp  Timestamp to sub
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subTimestamp($timestamp);

    /**
     * Compares two timestamps, returning the difference as integer
     *
     * @param  integer|string|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $timestamp  Timestamp to compare
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareTimestamp($timestamp);

    /**
     * Returns a string representation of the object
     * Supported format tokens are:
     * G - era, y - year, Y - ISO year, M - month, w - week of year, D - day of year, d - day of month
     * E - day of week, e - number of weekday (1-7), h - hour 1-12, H - hour 0-23, m - minute, s - second
     * A - milliseconds of day, z - timezone, Z - timezone offset, S - fractional second, a - period of day
     *
     * Additionally format tokens but non ISO conform are:
     * SS - day suffix, eee - php number of weekday(0-6), ddd - number of days per month
     * l - Leap year, B - swatch internet time, I - daylight saving time, X - timezone offset in seconds
     * r - RFC2822 format, U - unix timestamp
     *
     * Not supported ISO tokens are
     * u - extended year, Q - quarter, q - quarter, L - stand alone month, W - week of month
     * F - day of week of month, g - modified julian, c - stand alone weekday, k - hour 0-11, K - hour 1-24
     * v - wall zone
     *
     * @param  string              $format  OPTIONAL Rule for formatting output. If null the default date format is used
     * @param  string              $type    OPTIONAL Type for the format string which overrides the standard setting
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return string
     */
    public function toString($format = null, $type = null, $locale = null);

    /**
     * Returns a string representation of the date which is equal with the timestamp
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns a integer representation of the object
     * But returns false when the given part is no value f.e. Month-Name
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $part  OPTIONAL Defines the date or datepart to return as integer
     * @return integer|false
     */
    public function toValue($part = null);

    /**
     * Returns an array representation of the object
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns a representation of a date or datepart
     * This could be for example a localized monthname, the time without date,
     * the era or only the fractional seconds. There are about 50 different supported date parts.
     * For a complete list of supported datepart values look into the docu
     *
     * @param  string              $part    OPTIONAL Part of the date to return, if null the timestamp is returned
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return string  date or datepart
     */
    public function get($part = null, $locale = null);

    /**
     * Counts the exact year number
     * < 70 - 2000 added, >70 < 100 - 1900, others just returned
     *
     * @param  integer  $value year number
     * @return integer  Number of year
     */
    public static function getFullYear($value);

    /**
     * Sets the given date as new date or a given datepart as new datepart returning the new datepart
     * This could be for example a localized dayname, the date without time,
     * the month or only the seconds. There are about 50 different supported date parts.
     * For a complete list of supported datepart values look into the docu
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date or datepart to set
     * @param  string                          $part    OPTIONAL Part of the date to set, if null the timestamp is set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return $this Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function set($date, $part = null, $locale = null);

    /**
     * Adds a date or datepart to the existing date, by extracting $part from $date,
     * and modifying this object by adding that part.  The $part is then extracted from
     * this object and returned as an integer or numeric string (for large values, or $part's
     * corresponding to pre-defined formatted date strings).
     * This could be for example a ISO 8601 date, the hour the monthname or only the minute.
     * There are about 50 different supported date parts.
     * For a complete list of supported datepart values look into the docu.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date or datepart to add
     * @param  string                          $part    OPTIONAL Part of the date to add, if null the timestamp is added
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return $this Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function add($date, $part = \Zend_Date::TIMESTAMP, $locale = null);

    /**
     * Subtracts a date from another date.
     * This could be for example a RFC2822 date, the time,
     * the year or only the timestamp. There are about 50 different supported date parts.
     * For a complete list of supported datepart values look into the docu
     * Be aware: Adding -2 Months is not equal to Subtracting 2 Months !!!
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date or datepart to subtract
     * @param  string                          $part    OPTIONAL Part of the date to sub, if null the timestamp is subtracted
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return $this Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function sub($date, $part = \Zend_Date::TIMESTAMP, $locale = null);

    /**
     * Compares a date or datepart with the existing one.
     * Returns -1 if earlier, 0 if equal and 1 if later.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date or datepart to compare with the date object
     * @param  string                          $part    OPTIONAL Part of the date to compare, if null the timestamp is subtracted
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compare($date, $part = \Zend_Date::TIMESTAMP, $locale = null);

    /**
     * Returns a new instance of \Magento\Framework\Stdlib\DateTime\DateInterface with the selected part copied.
     * To make an exact copy, use PHP's clone keyword.
     * For a complete list of supported date part values look into the docu.
     * If a date part is copied, all other date parts are set to standard values.
     * For example: If only YEAR is copied, the returned date object is equal to
     * 01-01-YEAR 00:00:00 (01-01-1970 00:00:00 is equal to timestamp 0)
     * If only HOUR is copied, the returned date object is equal to
     * 01-01-1970 HOUR:00:00 (so $this contains a timestamp equal to a timestamp of 0 plus HOUR).
     *
     * @param  string              $part    Part of the date to compare, if null the timestamp is subtracted
     * @param  string|\Zend_Locale  $locale  OPTIONAL New object's locale.  No adjustments to timezone are made.
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface New clone with requested part
     */
    public function copyPart($part, $locale = null);

    /**
     * Internal function, returns the offset of a given timezone
     *
     * @param string $zone
     * @return integer
     */
    public function getTimezoneFromString($zone);

    /**
     * Returns true when both date objects or date parts are equal.
     * For example:
     * 15.May.2000 <-> 15.June.2000 Equals only for Day or Year... all other will return false
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date or datepart to equal with
     * @param  string                          $part    OPTIONAL Part of the date to compare, if null the timestamp is used
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return boolean
     * @throws \Zend_Date_Exception
     */
    public function equals($date, $part = \Zend_Date::TIMESTAMP, $locale = null);

    /**
     * Returns if the given date or datepart is earlier
     * For example:
     * 15.May.2000 <-> 13.June.1999 will return true for day, year and date, but not for month
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date or datepart to compare with
     * @param  string                          $part    OPTIONAL Part of the date to compare, if null the timestamp is used
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return boolean
     * @throws \Zend_Date_Exception
     */
    public function isEarlier($date, $part = null, $locale = null);

    /**
     * Returns if the given date or datepart is later
     * For example:
     * 15.May.2000 <-> 13.June.1999 will return true for month but false for day, year and date
     * Returns if the given date is later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date or datepart to compare with
     * @param  string                          $part    OPTIONAL Part of the date to compare, if null the timestamp is used
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return boolean
     * @throws \Zend_Date_Exception
     */
    public function isLater($date, $part = null, $locale = null);

    /**
     * Returns only the time of the date as new \Magento\Framework\Stdlib\DateTime\Date object
     * For example:
     * 15.May.2000 10:11:23 will return a dateobject equal to 01.Jan.1970 10:11:23
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getTime($locale = null);

    /**
     * Sets a new time for the date object. Format defines how to parse the time string.
     * Also a complete date can be given, but only the time is used for setting.
     * For example: dd.MMMM.yyTHH:mm' and 'ss sec'-> 10.May.07T25:11 and 44 sec => 1h11min44sec + 1 day
     * Returned is the new date object and the existing date is left as it was before
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $time    Time to set
     * @param  string                          $format  OPTIONAL Timeformat for parsing input
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setTime($time, $format = null, $locale = null);

    /**
     * Adds a time to the existing date. Format defines how to parse the time string.
     * If only parts are given the other parts are set to 0.
     * If no format is given, the standardformat of this locale is used.
     * For example: HH:mm:ss -> 10 -> +10 hours
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $time    Time to add
     * @param  string                          $format  OPTIONAL Timeformat for parsing input
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addTime($time, $format = null, $locale = null);

    /**
     * Subtracts a time from the existing date. Format defines how to parse the time string.
     * If only parts are given the other parts are set to 0.
     * If no format is given, the standardformat of this locale is used.
     * For example: HH:mm:ss -> 10 -> -10 hours
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $time    Time to sub
     * @param  string                          $format  OPTIONAL Timeformat for parsing input
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid inteface
     * @throws \Zend_Date_Exception
     */
    public function subTime($time, $format = null, $locale = null);

    /**
     * Compares the time from the existing date. Format defines how to parse the time string.
     * If only parts are given the other parts are set to default.
     * If no format us given, the standardformat of this locale is used.
     * For example: HH:mm:ss -> 10 -> 10 hours
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $time    Time to compare
     * @param  string                          $format  OPTIONAL Timeformat for parsing input
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareTime($time, $format = null, $locale = null);

    /**
     * Returns a clone of $this, with the time part set to 00:00:00.
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getDate($locale = null);

    /**
     * Sets a new date for the date object. Format defines how to parse the date string.
     * Also a complete date with time can be given, but only the date is used for setting.
     * For example: MMMM.yy HH:mm-> May.07 22:11 => 01.May.07 00:00
     * Returned is the new date object and the existing time is left as it was before
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date to set
     * @param  string                          $format  OPTIONAL Date format for parsing
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setDate($date, $format = null, $locale = null);

    /**
     * Adds a date to the existing date object. Format defines how to parse the date string.
     * If only parts are given the other parts are set to 0.
     * If no format is given, the standardformat of this locale is used.
     * For example: MM.dd.YYYY -> 10 -> +10 months
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date to add
     * @param  string                          $format  OPTIONAL Date format for parsing input
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addDate($date, $format = null, $locale = null);

    /**
     * Subtracts a date from the existing date object. Format defines how to parse the date string.
     * If only parts are given the other parts are set to 0.
     * If no format is given, the standardformat of this locale is used.
     * For example: MM.dd.YYYY -> 10 -> -10 months
     * Be aware: Subtracting 2 months is not equal to Adding -2 months !!!
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date to sub
     * @param  string                          $format  OPTIONAL Date format for parsing input
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subDate($date, $format = null, $locale = null);

    /**
     * Compares the date from the existing date object, ignoring the time.
     * Format defines how to parse the date string.
     * If only parts are given the other parts are set to 0.
     * If no format is given, the standardformat of this locale is used.
     * For example: 10.01.2000 => 10.02.1999 -> false
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Date to compare
     * @param  string                          $format  OPTIONAL Date format for parsing input
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareDate($date, $format = null, $locale = null);

    /**
     * Returns the full ISO 8601 date from the date object.
     * Always the complete ISO 8601 specifiction is used. If an other ISO date is needed
     * (ISO 8601 defines several formats) use toString() instead.
     * This function does not return the ISO date as object. Use copy() instead.
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return string
     */
    public function getIso($locale = null);

    /**
     * Sets a new date for the date object. Not given parts are set to default.
     * Only supported ISO 8601 formats are accepted.
     * For example: 050901 -> 01.Sept.2005 00:00:00, 20050201T10:00:30 -> 01.Feb.2005 10h00m30s
     * Returned is the new date object
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    ISO Date to set
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setIso($date, $locale = null);

    /**
     * Adds a ISO date to the date object. Not given parts are set to default.
     * Only supported ISO 8601 formats are accepted.
     * For example: 050901 -> + 01.Sept.2005 00:00:00, 10:00:00 -> +10h
     * Returned is the new date object
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    ISO Date to add
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addIso($date, $locale = null);

    /**
     * Subtracts a ISO date from the date object. Not given parts are set to default.
     * Only supported ISO 8601 formats are accepted.
     * For example: 050901 -> - 01.Sept.2005 00:00:00, 10:00:00 -> -10h
     * Returned is the new date object
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    ISO Date to sub
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subIso($date, $locale = null);

    /**
     * Compares a ISO date with the date object. Not given parts are set to default.
     * Only supported ISO 8601 formats are accepted.
     * For example: 050901 -> - 01.Sept.2005 00:00:00, 10:00:00 -> -10h
     * Returns if equal, earlier or later
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    ISO Date to sub
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareIso($date, $locale = null);

    /**
     * Returns a RFC 822 compilant datestring from the date object.
     * This function does not return the RFC date as object. Use copy() instead.
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return string
     */
    public function getArpa($locale = null);

    /**
     * Sets a RFC 822 date as new date for the date object.
     * Only RFC 822 compilant date strings are accepted.
     * For example: Sat, 14 Feb 09 00:31:30 +0100
     * Returned is the new date object
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    RFC 822 to set
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setArpa($date, $locale = null);

    /**
     * Adds a RFC 822 date to the date object.
     * ARPA messages are used in emails or HTTP Headers.
     * Only RFC 822 compilant date strings are accepted.
     * For example: Sat, 14 Feb 09 00:31:30 +0100
     * Returned is the new date object
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    RFC 822 Date to add
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addArpa($date, $locale = null);

    /**
     * Subtracts a RFC 822 date from the date object.
     * ARPA messages are used in emails or HTTP Headers.
     * Only RFC 822 compilant date strings are accepted.
     * For example: Sat, 14 Feb 09 00:31:30 +0100
     * Returned is the new date object
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    RFC 822 Date to sub
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subArpa($date, $locale = null);

    /**
     * Compares a RFC 822 compilant date with the date object.
     * ARPA messages are used in emails or HTTP Headers.
     * Only RFC 822 compilant date strings are accepted.
     * For example: Sat, 14 Feb 09 00:31:30 +0100
     * Returns if equal, earlier or later
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    RFC 822 Date to sub
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareArpa($date, $locale = null);

    /**
     * Returns the time of sunrise for this date and a given location as new date object
     * For a list of cities and correct locations use the class \Zend_Date_Cities
     *
     * @param  $location array - location of sunrise
     *                   ['horizon']   -> civil, nautic, astronomical, effective (default)
     *                   ['longitude'] -> longitude of location
     *                   ['latitude']  -> latitude of location
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     * @throws \Zend_Date_Exception
     */
    public function getSunrise($location);

    /**
     * Returns the time of sunset for this date and a given location as new date object
     * For a list of cities and correct locations use the class \Zend_Date_Cities
     *
     * @param  $location array - location of sunset
     *                   ['horizon']   -> civil, nautic, astronomical, effective (default)
     *                   ['longitude'] -> longitude of location
     *                   ['latitude']  -> latitude of location
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     * @throws \Zend_Date_Exception
     */
    public function getSunset($location);

    /**
     * Returns an array with the sunset and sunrise dates for all horizon types
     * For a list of cities and correct locations use the class \Zend_Date_Cities
     *
     * @param  $location array - location of suninfo
     *                   ['horizon']   -> civil, nautic, astronomical, effective (default)
     *                   ['longitude'] -> longitude of location
     *                   ['latitude']  -> latitude of location
     * @return array - [sunset|sunrise][effective|civil|nautic|astronomic]
     * @throws \Zend_Date_Exception
     */
    public function getSunInfo($location);

    /**
     * Check a given year for leap year.
     *
     * @param  integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $year  Year to check
     * @return boolean
     */
    public static function checkLeapYear($year);

    /**
     * Returns true, if the year is a leap year.
     *
     * @return boolean
     */
    public function isLeapYear();

    /**
     * Returns if the set date is todays date
     *
     * @return boolean
     */
    public function isToday();

    /**
     * Returns if the set date is yesterdays date
     *
     * @return boolean
     */
    public function isYesterday();

    /**
     * Returns if the set date is tomorrows date
     *
     * @return boolean
     */
    public function isTomorrow();

    /**
     * Returns the actual date as new date object
     *
     * @param  string|\Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public static function now($locale = null);

    /**
     * Returns only the year from the date object as new object.
     * For example: 10.May.2000 10:30:00 -> 01.Jan.2000 00:00:00
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getYear($locale = null);

    /**
     * Sets a new year
     * If the year is between 0 and 69, 2000 will be set (2000-2069)
     * If the year if between 70 and 99, 1999 will be set (1970-1999)
     * 3 or 4 digit years are set as expected. If you need to set year 0-99
     * use set() instead.
     * Returned is the new date object
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Year to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setYear($year, $locale = null);

    /**
     * Adds the year to the existing date object
     * If the year is between 0 and 69, 2000 will be added (2000-2069)
     * If the year if between 70 and 99, 1999 will be added (1970-1999)
     * 3 or 4 digit years are added as expected. If you need to add years from 0-99
     * use add() instead.
     * Returned is the new date object
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Year to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addYear($year, $locale = null);

    /**
     * Subs the year from the existing date object
     * If the year is between 0 and 69, 2000 will be subtracted (2000-2069)
     * If the year if between 70 and 99, 1999 will be subtracted (1970-1999)
     * 3 or 4 digit years are subtracted as expected. If you need to subtract years from 0-99
     * use sub() instead.
     * Returned is the new date object
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $date    Year to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subYear($year, $locale = null);

    /**
     * Compares the year with the existing date object, ignoring other date parts.
     * For example: 10.03.2000 -> 15.02.2000 -> true
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $year    Year to compare
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareYear($year, $locale = null);

    /**
     * Returns only the month from the date object as new object.
     * For example: 10.May.2000 10:30:00 -> 01.May.1970 00:00:00
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return \Zend_Date
     */
    public function getMonth($locale = null);

    /**
     * Sets a new month
     * The month can be a number or a string. Setting months lower than 0 and greater then 12
     * will result in adding or subtracting the relevant year. (12 months equal one year)
     * If a localized monthname is given it will be parsed with the default locale or the optional
     * set locale.
     * Returned is the new date object
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Month to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setMonth($month, $locale = null);

    /**
     * Adds months to the existing date object.
     * The month can be a number or a string. Adding months lower than 0 and greater then 12
     * will result in adding or subtracting the relevant year. (12 months equal one year)
     * If a localized monthname is given it will be parsed with the default locale or the optional
     * set locale.
     * Returned is the new date object
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Month to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addMonth($month, $locale = null);

    /**
     * Subtracts months from the existing date object.
     * The month can be a number or a string. Subtracting months lower than 0 and greater then 12
     * will result in adding or subtracting the relevant year. (12 months equal one year)
     * If a localized monthname is given it will be parsed with the default locale or the optional
     * set locale.
     * Returned is the new date object
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Month to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subMonth($month, $locale = null);

    /**
     * Compares the month with the existing date object, ignoring other date parts.
     * For example: 10.03.2000 -> 15.03.1950 -> true
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Month to compare
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareMonth($month, $locale = null);

    /**
     * Returns the day as new date object
     * Example: 20.May.1986 -> 20.Jan.1970 00:00:00
     *
     * @param $locale  string|\Zend_Locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getDay($locale = null);

    /**
     * Sets a new day
     * The day can be a number or a string. Setting days lower then 0 or greater than the number of this months days
     * will result in adding or subtracting the relevant month.
     * If a localized dayname is given it will be parsed with the default locale or the optional
     * set locale.
     * Returned is the new date object
     * Example: setDay('Montag', 'de_AT'); will set the monday of this week as day.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Day to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setDay($day, $locale = null);

    /**
     * Adds days to the existing date object.
     * The day can be a number or a string. Adding days lower then 0 or greater than the number of this months days
     * will result in adding or subtracting the relevant month.
     * If a localized dayname is given it will be parsed with the default locale or the optional
     * set locale.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Day to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addDay($day, $locale = null);

    /**
     * Subtracts days from the existing date object.
     * The day can be a number or a string. Subtracting days lower then 0 or greater than the number of this months days
     * will result in adding or subtracting the relevant month.
     * If a localized dayname is given it will be parsed with the default locale or the optional
     * set locale.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Day to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subDay($day, $locale = null);

    /**
     * Compares the day with the existing date object, ignoring other date parts.
     * For example: 'Monday', 'en' -> 08.Jan.2007 -> 0
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $day     Day to compare
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareDay($day, $locale = null);

    /**
     * Returns the weekday as new date object
     * Weekday is always from 1-7
     * Example: 09-Jan-2007 -> 2 = Tuesday -> 02-Jan-1970 (when 02.01.1970 is also Tuesday)
     *
     * @param $locale  string|\Zend_Locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getWeekday($locale = null);

    /**
     * Sets a new weekday
     * The weekday can be a number or a string. If a localized weekday name is given,
     * then it will be parsed as a date in $locale (defaults to the same locale as $this).
     * Returned is the new date object.
     * Example: setWeekday(3); will set the wednesday of this week as day.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Weekday to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setWeekday($weekday, $locale = null);

    /**
     * Adds weekdays to the existing date object.
     * The weekday can be a number or a string.
     * If a localized dayname is given it will be parsed with the default locale or the optional
     * set locale.
     * Returned is the new date object
     * Example: addWeekday(3); will add the difference of days from the beginning of the month until
     * wednesday.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Weekday to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addWeekday($weekday, $locale = null);

    /**
     * Subtracts weekdays from the existing date object.
     * The weekday can be a number or a string.
     * If a localized dayname is given it will be parsed with the default locale or the optional
     * set locale.
     * Returned is the new date object
     * Example: subWeekday(3); will subtract the difference of days from the beginning of the month until
     * wednesday.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $month   Weekday to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subWeekday($weekday, $locale = null);

    /**
     * Compares the weekday with the existing date object, ignoring other date parts.
     * For example: 'Monday', 'en' -> 08.Jan.2007 -> 0
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $weekday  Weekday to compare
     * @param  string|\Zend_Locale              $locale   OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareWeekday($weekday, $locale = null);

    /**
     * Returns the day of year as new date object
     * Example: 02.Feb.1986 10:00:00 -> 02.Feb.1970 00:00:00
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getDayOfYear($locale = null);

    /**
     * Sets a new day of year
     * The day of year is always a number.
     * Returned is the new date object
     * Example: 04.May.2004 -> setDayOfYear(10) -> 10.Jan.2004
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $day     Day of Year to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setDayOfYear($day, $locale = null);

    /**
     * Adds a day of year to the existing date object.
     * The day of year is always a number.
     * Returned is the new date object
     * Example: addDayOfYear(10); will add 10 days to the existing date object.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $day     Day of Year to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addDayOfYear($day, $locale = null);

    /**
     * Subtracts a day of year from the existing date object.
     * The day of year is always a number.
     * Returned is the new date object
     * Example: subDayOfYear(10); will subtract 10 days from the existing date object.
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $day     Day of Year to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subDayOfYear($day, $locale = null);

    /**
     * Compares the day of year with the existing date object.
     * For example: compareDayOfYear(33) -> 02.Feb.2007 -> 0
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $day     Day of Year to compare
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareDayOfYear($day, $locale = null);

    /**
     * Returns the hour as new date object
     * Example: 02.Feb.1986 10:30:25 -> 01.Jan.1970 10:00:00
     *
     * @param $locale  string|\Zend_Locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getHour($locale = null);

    /**
     * Sets a new hour
     * The hour is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> setHour(7); -> 04.May.1993 07:07:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $hour    Hour to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setHour($hour, $locale = null);

    /**
     * Adds hours to the existing date object.
     * The hour is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> addHour(12); -> 05.May.1993 01:07:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $hour    Hour to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addHour($hour, $locale = null);

    /**
     * Subtracts hours from the existing date object.
     * The hour is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> subHour(6); -> 05.May.1993 07:07:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $hour    Hour to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subHour($hour, $locale = null);

    /**
     * Compares the hour with the existing date object.
     * For example: 10:30:25 -> compareHour(10) -> 0
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $hour    Hour to compare
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareHour($hour, $locale = null);

    /**
     * Returns the minute as new date object
     * Example: 02.Feb.1986 10:30:25 -> 01.Jan.1970 00:30:00
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getMinute($locale = null);

    /**
     * Sets a new minute
     * The minute is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> setMinute(29); -> 04.May.1993 13:29:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $minute  Minute to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setMinute($minute, $locale = null);

    /**
     * Adds minutes to the existing date object.
     * The minute is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> addMinute(65); -> 04.May.1993 13:12:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $minute  Minute to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addMinute($minute, $locale = null);

    /**
     * Subtracts minutes from the existing date object.
     * The minute is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> subMinute(9); -> 04.May.1993 12:58:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $minute  Minute to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subMinute($minute, $locale = null);

    /**
     * Compares the minute with the existing date object.
     * For example: 10:30:25 -> compareMinute(30) -> 0
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $minute  Hour to compare
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareMinute($minute, $locale = null);

    /**
     * Returns the second as new date object
     * Example: 02.Feb.1986 10:30:25 -> 01.Jan.1970 00:00:25
     *
     * @param  string|\Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getSecond($locale = null);

    /**
     * Sets new seconds to the existing date object.
     * The second is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> setSecond(100); -> 04.May.1993 13:08:40
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface $second Second to set
     * @param  string|\Zend_Locale             $locale (Optional) Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setSecond($second, $locale = null);

    /**
     * Adds seconds to the existing date object.
     * The second is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> addSecond(65); -> 04.May.1993 13:08:30
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface $second Second to add
     * @param  string|\Zend_Locale             $locale (Optional) Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addSecond($second, $locale = null);

    /**
     * Subtracts seconds from the existing date object.
     * The second is always a number.
     * Returned is the new date object
     * Example: 04.May.1993 13:07:25 -> subSecond(10); -> 04.May.1993 13:07:15
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface $second Second to sub
     * @param  string|\Zend_Locale             $locale (Optional) Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subSecond($second, $locale = null);

    /**
     * Compares the second with the existing date object.
     * For example: 10:30:25 -> compareSecond(25) -> 0
     * Returns if equal, earlier or later
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface $second Second to compare
     * @param  string|\Zend_Locale             $locale (Optional) Locale for parsing input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     * @throws \Zend_Date_Exception
     */
    public function compareSecond($second, $locale = null);

    /**
     * Returns the precision for fractional seconds
     *
     * @return integer
     */
    public function getFractionalPrecision();

    /**
     * Sets a new precision for fractional seconds
     *
     * @param  integer $precision Precision for the fractional datepart 3 = milliseconds
     * @throws \Zend_Date_Exception
     * @return $this Provides fluid interface
     */
    public function setFractionalPrecision($precision);

    /**
     * Returns the milliseconds of the date object
     *
     * @return string
     */
    public function getMilliSecond();

    /**
     * Sets new milliseconds for the date object
     * Example: setMilliSecond(550, 2) -> equals +5 Sec +50 MilliSec
     *
     * @param  integer|\Magento\Framework\Stdlib\DateTime\DateInterface $milli     (Optional) Millisecond to set, when null the actual millisecond is set
     * @param  integer           $precision (Optional) Fraction precision of the given milliseconds
     * @return $this Provides fluid interface
     */
    public function setMilliSecond($milli = null, $precision = null);

    /**
     * Adds milliseconds to the date object
     *
     * @param  integer|\Magento\Framework\Stdlib\DateTime\DateInterface $milli     (Optional) Millisecond to add, when null the actual millisecond is added
     * @param  integer           $precision (Optional) Fractional precision for the given milliseconds
     * @return $this Provides fluid interface
     */
    public function addMilliSecond($milli = null, $precision = null);

    /**
     * Subtracts a millisecond
     *
     * @param  integer|\Magento\Framework\Stdlib\DateTime\DateInterface $milli     (Optional) Millisecond to sub, when null the actual millisecond is subtracted
     * @param  integer           $precision (Optional) Fractional precision for the given milliseconds
     * @return $this Provides fluid interface
     */
    public function subMilliSecond($milli = null, $precision = null);

    /**
     * Compares only the millisecond part, returning the difference
     *
     * @param  integer|\Magento\Framework\Stdlib\DateTime\DateInterface  $milli  OPTIONAL Millisecond to compare, when null the actual millisecond is compared
     * @param  integer            $precision  OPTIONAL Fractional precision for the given milliseconds
     * @throws \Zend_Date_Exception On invalid input
     * @return integer  0 = equal, 1 = later, -1 = earlier
     */
    public function compareMilliSecond($milli = null, $precision = null);

    /**
     * Returns the week as new date object using monday as beginning of the week
     * Example: 12.Jan.2007 -> 08.Jan.1970 00:00:00
     *
     * @param $locale  string|\Zend_Locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    public function getWeek($locale = null);

    /**
     * Sets a new week. The week is always a number. The day of week is not changed.
     * Returned is the new date object
     * Example: 09.Jan.2007 13:07:25 -> setWeek(1); -> 02.Jan.2007 13:07:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $week    Week to set
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function setWeek($week, $locale = null);

    /**
     * Adds a week. The week is always a number. The day of week is not changed.
     * Returned is the new date object
     * Example: 09.Jan.2007 13:07:25 -> addWeek(1); -> 16.Jan.2007 13:07:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $week    Week to add
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface  Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function addWeek($week, $locale = null);

    /**
     * Subtracts a week. The week is always a number. The day of week is not changed.
     * Returned is the new date object
     * Example: 09.Jan.2007 13:07:25 -> subWeek(1); -> 02.Jan.2007 13:07:25
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $week    Week to sub
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface Provides fluid interface
     * @throws \Zend_Date_Exception
     */
    public function subWeek($week, $locale = null);

    /**
     * Compares only the week part, returning the difference
     * Returned is the new date object
     * Returns if equal, earlier or later
     * Example: 09.Jan.2007 13:07:25 -> compareWeek(2); -> 0
     *
     * @param  string|integer|array|\Magento\Framework\Stdlib\DateTime\DateInterface  $week    Week to compare
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return integer 0 = equal, 1 = later, -1 = earlier
     */
    public function compareWeek($week, $locale = null);

    /**
     * Sets a new standard locale for the date object.
     * This locale will be used for all functions
     * Returned is the really set locale.
     * Example: 'de_XX' will be set to 'de' because 'de_XX' does not exist
     * 'xx_YY' will be set to 'root' because 'xx' does not exist
     *
     * @param  string|\Zend_Locale $locale (Optional) Locale for parsing input
     * @throws \Zend_Date_Exception When the given locale does not exist
     * @return $this Provides fluent interface
     */
    public function setLocale($locale = null);

    /**
     * Returns the actual set locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Checks if the given date is a real date or datepart.
     * Returns false if a expected datepart is missing or a datepart exceeds its possible border.
     * But the check will only be done for the expected dateparts which are given by format.
     * If no format is given the standard dateformat for the actual locale is used.
     * f.e. 30.February.2007 will return false if format is 'dd.MMMM.YYYY'
     *
     * @param  string|array|\Magento\Framework\Stdlib\DateTime\DateInterface $date   Date to parse for correctness
     * @param  string                 $format (Optional) Format for parsing the date string
     * @param  string|\Zend_Locale     $locale (Optional) Locale for parsing date parts
     * @return boolean                True when all date parts are correct
     */
    public static function isDate($date, $format = null, $locale = null);

    /**
     * Sets a new timezone for calculation of $this object's gmt offset.
     * For a list of supported timezones look here: http://php.net/timezones
     * If no timezone can be detected or the given timezone is wrong UTC will be set.
     *
     * @param  string  $zone      OPTIONAL timezone for date calculation; defaults to date_default_timezone_get()
     * @return \Zend_Date_DateObject Provides fluent interface
     * @throws \Zend_Date_Exception
     */
    public function setTimezone($zone = null);

    /**
     * Return the timezone of $this object.
     * The timezone is initially set when the object is instantiated.
     *
     * @return  string  actual set timezone string
     */
    public function getTimezone();

    /**
     * Return the offset to GMT of $this object's timezone.
     * The offset to GMT is initially set when the object is instantiated using the currently,
     * in effect, default timezone for PHP functions.
     *
     * @return  integer  seconds difference between GMT timezone and timezone when object was instantiated
     */
    public function getGmtOffset();
}
