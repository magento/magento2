<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 * @package   Zend_Measure
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Abstract.php 21329 2010-03-04 22:06:08Z thomas $
 */

/**
 * @see Zend_Locale
 */
#require_once 'Zend/Locale.php';

/**
 * @see Zend_Locale_Math
 */
#require_once 'Zend/Locale/Math.php';

/**
 * @see Zend_Locale_Format
 */
#require_once 'Zend/Locale/Format.php';

/**
 * Abstract class for all measurements
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Abstract
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Measure_Abstract
{
    /**
     * Plain value in standard unit
     *
     * @var string $_value
     */
    protected $_value;

    /**
     * Original type for this unit
     *
     * @var string $_type
     */
    protected $_type;

    /**
     * Locale identifier
     *
     * @var string $_locale
     */
    protected $_locale = null;

    /**
     * Unit types for this measurement
     */
    protected $_units = array();

    /**
     * Zend_Measure_Abstract is an abstract class for the different measurement types
     *
     * @param  $value  mixed  - Value as string, integer, real or float
     * @param  $type   type   - OPTIONAL a measure type f.e. Zend_Measure_Length::METER
     * @param  $locale locale - OPTIONAL a Zend_Locale Type
     * @throws Zend_Measure_Exception
     */
    public function __construct($value, $type = null, $locale = null)
    {
        if (($type !== null) and (Zend_Locale::isLocale($type, null, false))) {
            $locale = $type;
            $type = null;
        }

        $this->setLocale($locale);
        if ($type === null) {
            $type = $this->_units['STANDARD'];
        }

        if (isset($this->_units[$type]) === false) {
            #require_once 'Zend/Measure/Exception.php';
            throw new Zend_Measure_Exception("Type ($type) is unknown");
        }

        $this->setValue($value, $type, $this->_locale);
    }

    /**
     * Returns the actual set locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Sets a new locale for the value representation
     *
     * @param string|Zend_Locale $locale (Optional) New locale to set
     * @param boolean            $check  False, check but don't set; True, set the new locale
     * @return Zend_Measure_Abstract
     */
    public function setLocale($locale = null, $check = false)
    {
        if (empty($locale)) {
            #require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Locale') === true) {
                $locale = Zend_Registry::get('Zend_Locale');
            }
        }

        if ($locale === null) {
            $locale = new Zend_Locale();
        }

        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                #require_once 'Zend/Measure/Exception.php';
                throw new Zend_Measure_Exception("Language (" . (string) $locale . ") is unknown");
            }

            $locale = new Zend_Locale($locale);
        }

        if (!$check) {
            $this->_locale = (string) $locale;
        }
        return $this;
    }

    /**
     * Returns the internal value
     *
     * @param integer            $round  (Optional) Rounds the value to an given precision,
     *                                              Default is -1 which returns without rounding
     * @param string|Zend_Locale $locale (Optional) Locale for number representation
     * @return integer|string
     */
    public function getValue($round = -1, $locale = null)
    {
        if ($round < 0) {
            $return = $this->_value;
        } else {
            $return = Zend_Locale_Math::round($this->_value, $round);
        }

        if ($locale !== null) {
            $this->setLocale($locale, true);
            return Zend_Locale_Format::toNumber($return, array('locale' => $locale));
        }

        return $return;
    }

    /**
     * Set a new value
     *
     * @param  integer|string      $value   Value as string, integer, real or float
     * @param  string              $type    OPTIONAL A measure type f.e. Zend_Measure_Length::METER
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale for parsing numbers
     * @throws Zend_Measure_Exception
     * @return Zend_Measure_Abstract
     */
    public function setValue($value, $type = null, $locale = null)
    {
        if (($type !== null) and (Zend_Locale::isLocale($type, null, false))) {
            $locale = $type;
            $type = null;
        }

        if ($locale === null) {
            $locale = $this->_locale;
        }

        $this->setLocale($locale, true);
        if ($type === null) {
            $type = $this->_units['STANDARD'];
        }

        if (empty($this->_units[$type])) {
            #require_once 'Zend/Measure/Exception.php';
            throw new Zend_Measure_Exception("Type ($type) is unknown");
        }

        try {
            $value = Zend_Locale_Format::getNumber($value, array('locale' => $locale));
        } catch(Exception $e) {
            #require_once 'Zend/Measure/Exception.php';
            throw new Zend_Measure_Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->_value = $value;
        $this->setType($type);
        return $this;
    }

    /**
     * Returns the original type
     *
     * @return type
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set a new type, and convert the value
     *
     * @param  string $type New type to set
     * @throws Zend_Measure_Exception
     * @return Zend_Measure_Abstract
     */
    public function setType($type)
    {
        if (empty($this->_units[$type])) {
            #require_once 'Zend/Measure/Exception.php';
            throw new Zend_Measure_Exception("Type ($type) is unknown");
        }

        if (empty($this->_type)) {
            $this->_type = $type;
        } else {
            // Convert to standard value
            $value = $this->_value;
            if (is_array($this->_units[$this->getType()][0])) {
                foreach ($this->_units[$this->getType()][0] as $key => $found) {
                    switch ($key) {
                        case "/":
                            if ($found != 0) {
                                $value = call_user_func(Zend_Locale_Math::$div, $value, $found, 25);
                            }
                            break;
                        case "+":
                            $value = call_user_func(Zend_Locale_Math::$add, $value, $found, 25);
                            break;
                        case "-":
                            $value = call_user_func(Zend_Locale_Math::$sub, $value, $found, 25);
                            break;
                        default:
                            $value = call_user_func(Zend_Locale_Math::$mul, $value, $found, 25);
                            break;
                    }
                }
            } else {
                $value = call_user_func(Zend_Locale_Math::$mul, $value, $this->_units[$this->getType()][0], 25);
            }

            // Convert to expected value
            if (is_array($this->_units[$type][0])) {
                foreach (array_reverse($this->_units[$type][0]) as $key => $found) {
                    switch ($key) {
                        case "/":
                            $value = call_user_func(Zend_Locale_Math::$mul, $value, $found, 25);
                            break;
                        case "+":
                            $value = call_user_func(Zend_Locale_Math::$sub, $value, $found, 25);
                            break;
                        case "-":
                            $value = call_user_func(Zend_Locale_Math::$add, $value, $found, 25);
                            break;
                        default:
                            if ($found != 0) {
                                $value = call_user_func(Zend_Locale_Math::$div, $value, $found, 25);
                            }
                            break;
                    }
                }
            } else {
                $value = call_user_func(Zend_Locale_Math::$div, $value, $this->_units[$type][0], 25);
            }

            $slength = strlen($value);
            $length  = 0;
            for($i = 1; $i <= $slength; ++$i) {
                if ($value[$slength - $i] != '0') {
                    $length = 26 - $i;
                    break;
                }
            }

            $this->_value = Zend_Locale_Math::round($value, $length);
            $this->_type  = $type;
        }
        return $this;
    }

    /**
     * Compare if the value and type is equal
     *
     * @param  Zend_Measure_Abstract $object object to compare
     * @return boolean
     */
    public function equals($object)
    {
        if ((string) $object == $this->toString()) {
            return true;
        }

        return false;
    }

    /**
     * Returns a string representation
     *
     * @param  integer            $round  (Optional) Runds the value to an given exception
     * @param  string|Zend_Locale $locale (Optional) Locale to set for the number
     * @return string
     */
    public function toString($round = -1, $locale = null)
    {
        if ($locale === null) {
            $locale = $this->_locale;
        }

        return $this->getValue($round, $locale) . ' ' . $this->_units[$this->getType()][1];
    }

    /**
     * Returns a string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Returns the conversion list
     *
     * @return array
     */
    public function getConversionList()
    {
        return $this->_units;
    }

    /**
     * Alias function for setType returning the converted unit
     *
     * @param  string             $type   Constant Type
     * @param  integer            $round  (Optional) Rounds the value to a given precision
     * @param  string|Zend_Locale $locale (Optional) Locale to set for the number
     * @return string
     */
    public function convertTo($type, $round = 2, $locale = null)
    {
        $this->setType($type);
        return $this->toString($round, $locale);
    }

    /**
     * Adds an unit to another one
     *
     * @param  Zend_Measure_Abstract $object object of same unit type
     * @return Zend_Measure_Abstract
     */
    public function add($object)
    {
        $object->setType($this->getType());
        $value  = $this->getValue(-1) + $object->getValue(-1);

        $this->setValue($value, $this->getType(), $this->_locale);
        return $this;
    }

    /**
     * Substracts an unit from another one
     *
     * @param  Zend_Measure_Abstract $object object of same unit type
     * @return Zend_Measure_Abstract
     */
    public function sub($object)
    {
        $object->setType($this->getType());
        $value  = $this->getValue(-1) - $object->getValue(-1);

        $this->setValue($value, $this->getType(), $this->_locale);
        return $this;
    }

    /**
     * Compares two units
     *
     * @param  Zend_Measure_Abstract $object object of same unit type
     * @return boolean
     */
    public function compare($object)
    {
        $object->setType($this->getType());
        $value  = $this->getValue(-1) - $object->getValue(-1);

        if ($value < 0) {
            return -1;
        } else if ($value > 0) {
            return 1;
        }

        return 0;
    }
}
