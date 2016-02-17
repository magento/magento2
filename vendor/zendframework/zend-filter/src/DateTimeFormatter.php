<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use DateTime;

class DateTimeFormatter extends AbstractFilter
{
    /**
     * A valid format string accepted by date()
     *
     * @var string
     */
    protected $format = DateTime::ISO8601;

    /**
     * Sets filter options
     *
     * @param array|\Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set the format string accepted by date() to use when formatting a string
     *
     * @param  string $format
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Filter a datetime string by normalizing it to the filters specified format
     *
     * @param  DateTime|string|integer $value
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function filter($value)
    {
        try {
            $result = $this->normalizeDateTime($value);
        } catch (\Exception $e) {
            // DateTime threw an exception, an invalid date string was provided
            throw new Exception\InvalidArgumentException('Invalid date string provided', $e->getCode(), $e);
        }

        if ($result === false) {
            return $value;
        }

        return $result;
    }

    /**
     * Normalize the provided value to a formatted string
     *
     * @param  string|int|DateTime $value
     * @return string
     */
    protected function normalizeDateTime($value)
    {
        if ($value === '' || $value === null) {
            return $value;
        }

        if (!is_string($value) && !is_int($value) && !$value instanceof DateTime) {
            return $value;
        }

        if (is_int($value)) {
            //timestamp
            $value = new DateTime('@' . $value);
        } elseif (!$value instanceof DateTime) {
            $value = new DateTime($value);
        }

        return $value->format($this->format);
    }
}
