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

use DateTime;
use Traversable;

/**
 * @category   Zend
 * @package    Zend_Validate
 */
class Date extends AbstractValidator
{
    const INVALID        = 'dateInvalid';
    const INVALID_DATE   = 'dateInvalidDate';
    const FALSEFORMAT    = 'dateFalseFormat';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID        => "Invalid type given. String, integer, array or DateTime expected",
        self::INVALID_DATE   => "The input does not appear to be a valid date",
        self::FALSEFORMAT    => "The input does not fit the date format '%format%'",
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'format'  => 'format'
    );

    /**
     * Optional format
     *
     * @var string|null
     */
    protected $format;

    /**
     * Sets validator options
     *
     * @param  string|array|Traversable $options OPTIONAL
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['format'] = array_shift($options);
            $options = $temp;
        }

        if (array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
        }

        parent::__construct($options);
    }

    /**
     * Returns the format option
     *
     * @return string|null
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets the format option
     *
     * @param  string $format
     * @return Date provides a fluent interface
     */
    public function setFormat($format = null)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Returns true if $value is a valid date of the format YYYY-MM-DD
     * If optional $format is set the date format is checked
     * according to DateTime
     *
     * @param  string|array|int|DateTime $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)
            && !is_array($value)
            && !is_int($value)
            && !($value instanceof DateTime)
        ) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        $format = $this->getFormat();

        if ($value instanceof DateTime) {
            return true;
        } elseif (is_int($value)
            || (is_string($value) && null !== $format)
        ) {
            $date = (is_int($value))
                    ? date_create("@$value") // from timestamp
                    : DateTime::createFromFormat($format, $value);

            // Invalid dates can show up as warnings (ie. "2007-02-99")
            // and still return a DateTime object
            $errors = DateTime::getLastErrors();

            if (false === $date || $errors['warning_count'] > 0) {
                $this->error(self::INVALID_DATE);
                return false;
            }
        } else {
            if (is_array($value)) {
                $value = implode('-', $value);
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $this->format = 'Y-m-d';
                $this->error(self::FALSEFORMAT);
                $this->format = null;
                return false;
            }

            list($year, $month, $day) = sscanf($value, '%d-%d-%d');

            if (!checkdate($month, $day, $year)) {
                $this->error(self::INVALID_DATE);
                return false;
            }
        }

        return true;
    }
}
