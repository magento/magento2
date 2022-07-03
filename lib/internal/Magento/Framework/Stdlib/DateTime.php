<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib;

/**
 * Converter of date formats
 * Internal dates
 *
 * @api
 * @since 100.0.2
 */
class DateTime
{
    /**#@+
     * Date format, used as default. Compatible with \DateTime
     */
    public const DATETIME_INTERNAL_FORMAT = 'yyyy-MM-dd HH:mm:ss';

    public const DATE_INTERNAL_FORMAT = 'yyyy-MM-dd';

    public const DATETIME_PHP_FORMAT = 'Y-m-d H:i:s';

    public const DATE_PHP_FORMAT = 'Y-m-d';

    /**#@-*/

    /**
     * Minimum allowed year value
     */
    public const YEAR_MIN_VALUE = -10000;

    /**
     * Maximum allowed year value
     */
    public const YEAR_MAX_VALUE = 10000;

    /**
     * Format date to internal format
     *
     * @param string|\DateTimeInterface|bool|null $date
     * @param boolean $includeTime
     * @return string|null
     */
    public function formatDate($date, $includeTime = true)
    {
        if ($date instanceof \DateTimeInterface) {
            $format = $includeTime ? self::DATETIME_PHP_FORMAT : self::DATE_PHP_FORMAT;
            return $date->format($format);
        } elseif (empty($date)) {
            return null;
        } elseif ($date === true) {
            $date = (new \DateTime())->getTimestamp();
        } elseif (!is_numeric($date)) {
            $date = (new \DateTime($date))->getTimestamp();
        }

        $format = $includeTime ? self::DATETIME_PHP_FORMAT : self::DATE_PHP_FORMAT;
        return (new \DateTime())->setTimestamp($date)->format($format);
    }

    /**
     * Check whether sql date is empty
     *
     * @param string $date
     * @return boolean
     */
    public function isEmptyDate($date)
    {
        return !$date || preg_replace('#[ 0:-]#', '', $date) === '';
    }

    /**
     * Wrapper for native gmdate function
     *
     * @param string $format
     * @param int $time
     * @return string The given time in given format
     *
     * @deprecated 101.0.1
     * @see Use Intl library for datetime handling: http://php.net/manual/en/book.intl.php
     *
     * @codeCoverageIgnore
     */
    public function gmDate($format, $time)
    {
        return gmdate($format, $time);
    }

    /**
     * Wrapper for native strtotime function
     *
     * @param string $timeStr
     * @return int
     *
     * @deprecated 101.0.1
     * @see Use Intl library for datetime handling: http://php.net/manual/en/book.intl.php
     *
     * @codeCoverageIgnore
     */
    public function strToTime($timeStr)
    {
        return strtotime($timeStr);
    }
}
