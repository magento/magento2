<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator;

use DateInterval;
use DateTime;
use DateTimeZone;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\Exception;

/**
 * @category   Zend
 * @package    Zend_Validator
 */
class DateStep extends Date
{
    const NOT_STEP     = 'dateStepNotStep';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_STEP     => "The input is not a valid step"
    );

    /**
     * Optional base date value
     *
     * @var string|integer|\DateTime
     */
    protected $baseValue = '1970-01-01T00:00:00Z';

    /**
     * Date step interval (defaults to 1 day).
     * Uses the DateInterval specification.
     *
     * @var DateInterval
     */
    protected $step;

    /**
     * Format to use for parsing date strings
     *
     * @var string
     */
    protected $format = DateTime::ISO8601;

    /**
     * Optional timezone to be used when the baseValue
     * and validation values do not contain timezone info
     *
     * @var DateTimeZone
     */
    protected $timezone;

    /**
     * Set default options for this instance
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['baseValue'] = array_shift($options);
            if (!empty($options)) {
                $temp['step'] = array_shift($options);
            }
            if (!empty($options)) {
                $temp['format'] = array_shift($options);
            }
            if (!empty($options)) {
                $temp['timezone'] = array_shift($options);
            }

            $options = $temp;
        }

        if (isset($options['baseValue'])) {
            $this->setBaseValue($options['baseValue']);
        }
        if (isset($options['step'])) {
            $this->setStep($options['step']);
        } else {
            $this->setStep(new DateInterval('P1D'));
        }
        if (array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
        }
        if (isset($options['timezone'])) {
            $this->setTimezone($options['timezone']);
        } else {
            $this->setTimezone(new DateTimeZone(date_default_timezone_get()));
        }

        parent::__construct($options);
    }

    /**
     * Sets the base value from which the step should be computed
     *
     * @param  string|integer|\DateTime $baseValue
     * @return DateStep
     */
    public function setBaseValue($baseValue)
    {
        $this->baseValue = $baseValue;
        return $this;
    }

    /**
     * Returns the base value from which the step should be computed
     *
     * @return string|integer|\DateTime
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }

    /**
     * Sets the step date interval
     *
     * @param  DateInterval $step
     * @return DateStep
     */
    public function setStep(DateInterval $step)
    {
        $this->step = $step;
        return $this;
    }

    /**
     * Returns the step date interval
     *
     * @return DateInterval
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns the timezone option
     *
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Sets the timezone option
     *
     * @param  DateTimeZone $timezone
     * @return DateStep
     */
    public function setTimezone(DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Converts an int or string to a DateTime object
     *
     * @param  string|integer|\DateTime $param
     * @return \DateTime
     * @throws Exception\InvalidArgumentException
     */
    protected function convertToDateTime($param)
    {
        $dateObj = $param;
        if (is_int($param)) {
            // Convert from timestamp
            $dateObj = date_create("@$param");
        } elseif (is_string($param)) {
            // Custom week format support
            if (strpos($this->getFormat(), 'Y-\WW') === 0
                && preg_match('/^([0-9]{4})\-W([0-9]{2})/', $param, $matches)
            ) {
                $dateObj = new DateTime();
                $dateObj->setISODate($matches[1], $matches[2]);
            } else {
                $dateObj = DateTime::createFromFormat(
                    $this->getFormat(), $param, $this->getTimezone()
                );
            }
        }
        if (!($dateObj instanceof DateTime)) {
            throw new Exception\InvalidArgumentException('Invalid date param given');
        }

        return $dateObj;
    }

    /**
     * Returns true if a date is within a valid step
     *
     * @param  string|integer|\DateTime $value
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isValid($value)
    {
        parent::isValid($value);

        $this->setValue($value);

        $baseDate = $this->convertToDateTime($this->getBaseValue());
        $step     = $this->getStep();

        // Parse the date
        try {
            $valueDate = $this->convertToDateTime($value);
        } catch (Exception\InvalidArgumentException $ex) {
            return false;
        }

        // Same date?
        if ($valueDate == $baseDate) {
            return true;
        }

        // Optimization for simple intervals.
        // Handle intervals of just one date or time unit.
        $intervalParts = explode('|', $step->format('%y|%m|%d|%h|%i|%s'));
        $partCounts    = array_count_values($intervalParts);
        if (5 === $partCounts["0"]) {
            // Find the unit with the non-zero interval
            $unitKeys = array('years', 'months', 'days', 'hours', 'minutes', 'seconds');
            $intervalParts = array_combine($unitKeys, $intervalParts);

            $intervalUnit = null;
            $stepValue    = null;
            foreach ($intervalParts as $key => $value) {
                if (0 != $value) {
                    $intervalUnit = $key;
                    $stepValue    = (int) $value;
                    break;
                }
            }

            // Get absolute time difference
            $timeDiff  = $valueDate->diff($baseDate, true);
            $diffParts = explode('|', $timeDiff->format('%y|%m|%d|%h|%i|%s'));
            $diffParts = array_combine($unitKeys, $diffParts);

            // Check date units
            if (in_array($intervalUnit, array('years', 'months', 'days'))) {
                switch ($intervalUnit) {
                    case 'years':
                        if (   0 == $diffParts['months']  && 0 == $diffParts['days']
                            && 0 == $diffParts['hours']   && 0 == $diffParts['minutes']
                            && 0 == $diffParts['seconds']
                        ) {
                            if (($diffParts['years'] % $stepValue) === 0) {
                                return true;
                            }
                        }
                        break;
                    case 'months':
                        if (   0 == $diffParts['days']    && 0 == $diffParts['hours']
                            && 0 == $diffParts['minutes'] && 0 == $diffParts['seconds']
                        ) {
                            $months = ($diffParts['years'] * 12) + $diffParts['months'];
                            if (($months % $stepValue) === 0) {
                                return true;
                            }
                        }
                        break;
                    case 'days':
                        if (   0 == $diffParts['hours'] && 0 == $diffParts['minutes']
                            && 0 == $diffParts['seconds']
                        ) {
                            $days = $timeDiff->format('%a'); // Total days
                            if (($days % $stepValue) === 0) {
                                return true;
                            }
                        }
                        break;
                }
                $this->error(self::NOT_STEP);
                return false;
            }

            // Check time units
            if (in_array($intervalUnit, array('hours', 'minutes', 'seconds'))) {

                // Simple test if $stepValue is 1.
                if (1 == $stepValue) {
                    if ('hours' === $intervalUnit
                        && 0 == $diffParts['minutes'] && 0 == $diffParts['seconds']
                    ) {
                        return true;
                    } elseif ('minutes' === $intervalUnit && 0 == $diffParts['seconds']) {
                        return true;
                    } elseif ('seconds' === $intervalUnit) {
                        return true;
                    }
                }

                // Simple test for same day, when using default baseDate
                if ($baseDate->format('Y-m-d') == $valueDate->format('Y-m-d')
                    && $baseDate->format('Y-m-d') == '1970-01-01'
                ) {
                    switch ($intervalUnit) {
                        case 'hours':
                            if (0 == $diffParts['minutes'] && 0 == $diffParts['seconds']) {
                                if (($diffParts['hours'] % $stepValue) === 0) {
                                    return true;
                                }
                            }
                            break;
                        case 'minutes':
                            if (0 == $diffParts['seconds']) {
                                $minutes = ($diffParts['hours'] * 60) + $diffParts['minutes'];
                                if (($minutes % $stepValue) === 0) {
                                    return true;
                                }
                            }
                            break;
                        case 'seconds':
                            $seconds = ($diffParts['hours'] * 60)
                                       + ($diffParts['minutes'] * 60)
                                       + $diffParts['seconds'];
                            if (($seconds % $stepValue) === 0) {
                                return true;
                            }
                            break;
                    }
                    $this->error(self::NOT_STEP);
                    return false;
                }
            }
        }

        // Fall back to slower (but accurate) method for complex intervals.
        // Keep adding steps to the base date until a match is found
        // or until the value is exceeded.
        if ($baseDate < $valueDate) {
            while ($baseDate < $valueDate) {
                $baseDate->add($step);
                if ($baseDate == $valueDate) {
                    return true;
                }
            }
        } else {
            while ($baseDate > $valueDate) {
                $baseDate->sub($step);
                if ($baseDate == $valueDate) {
                    return true;
                }
            }
        }

        $this->error(self::NOT_STEP);
        return false;
    }
}
