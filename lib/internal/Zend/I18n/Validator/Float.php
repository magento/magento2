<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_I18n
 */

namespace Zend\I18n\Validator;

use Locale;
use NumberFormatter;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * @category   Zend
 * @package    Zend_I18n
 * @subpackage Validator
 */
class Float extends AbstractValidator
{
    const INVALID   = 'floatInvalid';
    const NOT_FLOAT = 'notFloat';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::NOT_FLOAT => "The input does not appear to be a float",
    );

    /**
     * Optional locale
     *
     * @var string|null
     */
    protected $locale;

    /**
     * Constructor for the integer validator
     *
     * @param array|Traversable $options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (array_key_exists('locale', $options)) {
            $this->setLocale($options['locale']);
        }

        parent::__construct($options);
    }

    /**
     * Returns the set locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = Locale::getDefault();
        }
        return $this->locale;
    }

    /**
     * Sets the locale to use
     *
     * @param string|null $locale
     * @return Float
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }


    /**
     * Returns true if and only if $value is a floating-point value
     *
     * @param  string $value
     * @return boolean
     * @throws Exception\InvalidArgumentException
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        if (is_float($value)) {
            return true;
        }

        $locale = $this->getLocale();
        $format = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        if (intl_is_failure($format->getErrorCode())) {
            throw new Exception\InvalidArgumentException("Invalid locale string given");
        }

        $parsedFloat = $format->parse($value, NumberFormatter::TYPE_DOUBLE);
        if (intl_is_failure($format->getErrorCode())) {
            $this->error(self::NOT_FLOAT);
            return false;
        }

        $decimalSep  = $format->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $groupingSep = $format->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);

        $valueFiltered = str_replace($groupingSep, '', $value);
        $valueFiltered = str_replace($decimalSep, '.', $valueFiltered);

        while (strpos($valueFiltered, '.') !== false
               && (substr($valueFiltered, -1) == '0' || substr($valueFiltered, -1) == '.')
        ) {
            $valueFiltered = substr($valueFiltered, 0, strlen($valueFiltered) - 1);
        }

        if (strval($parsedFloat) !== $valueFiltered) {
            $this->error(self::NOT_FLOAT);
            return false;
        }

        return true;
    }
}
