<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime;

/**
 * @package Magento\Framework
 */
class DateTimeFormatter
{
    /**
     * Returns a translated and localized date string
     *
     * @param \IntlCalendar|\DateTime $object
     * @param string|int|array|null $format
     * @param string|null $locale
     * @return string
     */
    public function formatObject($object, $format = null, $locale = null)
    {
        if (defined('HHVM_VERSION')) {
            return $this->doFormatObject($object, $format, $locale);
        }
        return \IntlDateFormatter::formatObject($object, $format, $locale);
    }

    /**
     * Implements what IntlDateFormatter::formatObject() is in PHP 5.5+
     *
     * @param \IntlCalendar|\DateTime $object
     * @param string|int|array|null $format
     * @param string|null $locale
     * @return string
     */
    protected function doFormatObject($object, $format = null, $locale = null)
    {
        $pattern = $dateFormat = $timeFormat = $calendar = null;

        if (is_array($format)) {
            list($dateFormat, $timeFormat) = $format;
        } elseif (is_numeric($format)) {
            $dateFormat = $format;
        } else {
            $dateFormat = $timeFormat = \IntlDateFormatter::FULL;
            $pattern = $format;
        }

        $timezone = $object->getTimezone()->getName();
        if ($timezone === '+00:00') {
            $timezone = 'UTC';
        } elseif ($timezone[0] === '+' || $timezone[0] === '-') {
            $timezone = 'GMT' . $timezone;
        }
        return (new \IntlDateFormatter($locale, $dateFormat, $timeFormat, $timezone, $calendar, $pattern))
            ->format($object);
    }
}
