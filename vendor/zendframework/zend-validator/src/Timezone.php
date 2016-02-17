<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use DateTimeZone;

class Timezone extends AbstractValidator
{
    const INVALID                       = 'invalidTimezone';
    const INVALID_TIMEZONE_LOCATION     = 'invalidTimezoneLocation';
    const INVALID_TIMEZONE_ABBREVIATION = 'invalidTimezoneAbbreviation';

    const LOCATION      = 0x01;
    const ABBREVIATION  = 0x02;
    const ALL           = 0x03;

    /**
     * @var array
     */
    protected $constants = array(
        self::LOCATION       => 'location',
        self::ABBREVIATION   => 'abbreviation',
    );

    /**
     * Default value for types; value = 3
     *
     * @var array
     */
    protected $defaultType = array(
        self::LOCATION,
        self::ABBREVIATION,
    );

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID                       => 'Invalid timezone given.',
        self::INVALID_TIMEZONE_LOCATION     => 'Invalid timezone location given.',
        self::INVALID_TIMEZONE_ABBREVIATION => 'Invalid timezone abbreviation given.',
    );

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array|int $options OPTIONAL
     */
    public function __construct($options = array())
    {
        $opts['type'] = $this->defaultType;

        if (is_array($options)) {
            if (array_key_exists('type', $options)) {
                $opts['type'] = $options['type'];
            }
        } elseif (! empty($options)) {
            $opts['type'] = $options;
        }

        // setType called by parent constructor then setOptions method
        parent::__construct($opts);
    }

    /**
     * Set the types
     *
     * @param  int|array $type
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setType($type = null)
    {
        $type = $this->calculateTypeValue($type);

        if (!is_int($type) || ($type < 1) || ($type > self::ALL)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown type "%s" provided',
                (is_string($type) || is_int($type))
                    ? $type
                    : (is_object($type) ? get_class($type) : gettype($type))
            ));
        }

        $this->options['type'] = $type;
    }

    /**
     * Returns true if timezone location or timezone abbreviations is correct.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if ($value !== null && !is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $type = $this->options['type'];
        $this->setValue($value);

        switch (true) {
            // Check in locations and abbreviations
            case (($type & self::LOCATION) && ($type & self::ABBREVIATION)):
                $abbrs = DateTimeZone::listAbbreviations();
                $locations = DateTimeZone::listIdentifiers();

                if (!array_key_exists($value, $abbrs) && !in_array($value, $locations)) {
                    $this->error(self::INVALID);
                    return false;
                }
                break;

            // Check only in locations
            case ($type & self::LOCATION):
                $locations = DateTimeZone::listIdentifiers();

                if (!in_array($value, $locations)) {
                    $this->error(self::INVALID_TIMEZONE_LOCATION);
                    return false;
                }
                break;

            // Check only in abbreviations
            case ($type & self::ABBREVIATION):
                $abbrs = DateTimeZone::listAbbreviations();

                if (!array_key_exists($value, $abbrs)) {
                    $this->error(self::INVALID_TIMEZONE_ABBREVIATION);
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * @param array|int|string $type
     *
     * @return int
     */
    protected function calculateTypeValue($type)
    {
        $types    = (array) $type;
        $detected = 0;

        foreach ($types as $value) {
            if (is_int($value)) {
                $detected |= $value;
            } elseif (false !== ($position = array_search($value, $this->constants))) {
                $detected |= array_search($value, $this->constants);
            }
        }

        return $detected;
    }
}
