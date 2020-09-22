<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\DateTime;

/**
 * Date conversion model
 *
 * @api
 * @since 100.0.2
 */
class DateTime
{
    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->_localeDate = $localeDate;
    }

    /**
     * Calculates timezone offset
     *
     * @param  string|null $timezone
     * @return int offset between timezone and gmt
     */
    public function calculateOffset($timezone = null)
    {
        $result = true;
        $offset = 0;
        if ($timezone !== null) {
            $oldZone = @date_default_timezone_get();
            $result = date_default_timezone_set($timezone);
        }
        if ($result === true) {
            $offset = (int)date('Z');
        }
        if ($timezone !== null) {
            date_default_timezone_set($oldZone);
        }
        return $offset;
    }

    /**
     * Forms GMT date
     *
     * @param  string $format
     * @param  int|string $input date in current timezone
     * @return string
     */
    public function gmtDate($format = null, $input = null)
    {
        if ($format === null) {
            $format = 'Y-m-d H:i:s';
        }
        $date = $this->gmtTimestamp($input);
        if ($date === false) {
            return false;
        }
        $result = date($format, $date);
        return $result;
    }

    /**
     * Converts input date into date with timezone offset. Input date must be in GMT timezone.
     *
     * @param  string $format
     * @param  int|string $input date in GMT timezone
     * @return string
     */
    public function date($format = null, $input = null)
    {
        if ($format === null) {
            $format = 'Y-m-d H:i:s';
        }
        $result = date($format, $this->timestamp($input));
        return $result;
    }

    /**
     * Forms GMT timestamp
     *
     * @param  int|string|\DateTimeInterface $input date in current timezone
     * @return int
     */
    public function gmtTimestamp($input = null)
    {
        if ($input === null) {
            return (int)gmdate('U');
        } elseif (is_numeric($input)) {
            $result = $input;
        } elseif ($input instanceof \DateTimeInterface) {
            $result = $input->getTimestamp();
        } else {
            $result = strtotime($input);
        }
        if ($result === false) {
            // strtotime() unable to parse string (it's not a date or has incorrect format)
            return false;
        }
        $date = $this->_localeDate->date($result);
        $timestamp = $date->getTimestamp();
        unset($date);
        return $timestamp;
    }

    /**
     * Converts input date into timestamp with timezone offset. Input date must be in GMT timezone.
     *
     * @param  int|string $input date in GMT timezone
     * @return int
     */
    public function timestamp($input = null)
    {
        switch (true) {
            case ($input === null):
                $result = $this->gmtTimestamp();
                break;
            case (is_numeric($input)):
                $result = $input;
                break;
            case ($input instanceof \DateTimeInterface):
                $result = $input->getTimestamp();
                break;
            default:
                $result = strtotime($input);
        }

        $date = $this->_localeDate->date($result);

        return $date->getTimestamp();
    }

    /**
     * Get current timezone offset in seconds/minutes/hours
     *
     * @param  string $type
     * @return int
     */
    public function getGmtOffset($type = 'seconds')
    {
        $offset = $this->calculateOffset($this->_localeDate->getConfigTimezone());
        switch ($type) {
            case 'seconds':
            default:
                break;
            case 'minutes':
                $offset = $offset / 60;
                break;
            case 'hours':
                $offset = $offset / 60 / 60;
                break;
        }
        return $offset;
    }
}
