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
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Format
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Format.php 22808 2010-08-08 09:38:42Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * include needed classes
 */
#require_once 'Zend/Locale/Data.php';

/**
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Format
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Locale_Format
{
    const STANDARD   = 'auto';

    private static $_options = array('date_format'   => null,
                                     'number_format' => null,
                                     'format_type'   => 'iso',
                                     'fix_date'      => false,
                                     'locale'        => null,
                                     'cache'         => null,
                                     'disableCache'  => false,
                                     'precision'     => null);

    /**
     * Sets class wide options, if no option was given, the actual set options will be returned
     * The 'precision' option of a value is used to truncate or stretch extra digits. -1 means not to touch the extra digits.
     * The 'locale' option helps when parsing numbers and dates using separators and month names.
     * The date format 'format_type' option selects between CLDR/ISO date format specifier tokens and PHP's date() tokens.
     * The 'fix_date' option enables or disables heuristics that attempt to correct invalid dates.
     * The 'number_format' option can be used to specify a default number format string
     * The 'date_format' option can be used to specify a default date format string, but beware of using getDate(),
     * checkDateFormat() and getTime() after using setOptions() with a 'format'.  To use these four methods
     * with the default date format for a locale, use array('date_format' => null, 'locale' => $locale) for their options.
     *
     * @param  array  $options  Array of options, keyed by option name: format_type = 'iso' | 'php', fix_date = true | false,
     *                          locale = Zend_Locale | locale string, precision = whole number between -1 and 30
     * @throws Zend_Locale_Exception
     * @return Options array if no option was given
     */
    public static function setOptions(array $options = array())
    {
        self::$_options = self::_checkOptions($options) + self::$_options;
        return self::$_options;
    }

    /**
     * Internal function for checking the options array of proper input values
     * See {@link setOptions()} for details.
     *
     * @param  array  $options  Array of options, keyed by option name: format_type = 'iso' | 'php', fix_date = true | false,
     *                          locale = Zend_Locale | locale string, precision = whole number between -1 and 30
     * @throws Zend_Locale_Exception
     * @return Options array if no option was given
     */
    private static function _checkOptions(array $options = array())
    {
        if (count($options) == 0) {
            return self::$_options;
        }
        foreach ($options as $name => $value) {
            $name  = strtolower($name);
            if ($name !== 'locale') {
                if (gettype($value) === 'string') {
                    $value = strtolower($value);
                }
            }

            switch($name) {
                case 'number_format' :
                    if ($value == Zend_Locale_Format::STANDARD) {
                        $locale = self::$_options['locale'];
                        if (isset($options['locale'])) {
                            $locale = $options['locale'];
                        }
                        $options['number_format'] = Zend_Locale_Data::getContent($locale, 'decimalnumber');
                    } else if ((gettype($value) !== 'string') and ($value !== NULL)) {
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Unknown number format type '" . gettype($value) . "'. "
                            . "Format '$value' must be a valid number format string.");
                    }
                    break;

                case 'date_format' :
                    if ($value == Zend_Locale_Format::STANDARD) {
                        $locale = self::$_options['locale'];
                        if (isset($options['locale'])) {
                            $locale = $options['locale'];
                        }
                        $options['date_format'] = Zend_Locale_Format::getDateFormat($locale);
                    } else if ((gettype($value) !== 'string') and ($value !== NULL)) {
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Unknown dateformat type '" . gettype($value) . "'. "
                            . "Format '$value' must be a valid ISO or PHP date format string.");
                    } else {
                        if (((isset($options['format_type']) === true) and ($options['format_type'] == 'php')) or
                            ((isset($options['format_type']) === false) and (self::$_options['format_type'] == 'php'))) {
                            $options['date_format'] = Zend_Locale_Format::convertPhpToIsoFormat($value);
                        }
                    }
                    break;

                case 'format_type' :
                    if (($value != 'php') && ($value != 'iso')) {
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Unknown date format type '$value'. Only 'iso' and 'php'"
                           . " are supported.");
                    }
                    break;

                case 'fix_date' :
                    if (($value !== true) && ($value !== false)) {
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Enabling correction of dates must be either true or false"
                            . "(fix_date='$value').");
                    }
                    break;

                case 'locale' :
                    $options['locale'] = Zend_Locale::findLocale($value);
                    break;

                case 'cache' :
                    if ($value instanceof Zend_Cache_Core) {
                        Zend_Locale_Data::setCache($value);
                    }
                    break;

                case 'disablecache' :
                    Zend_Locale_Data::disableCache($value);
                    break;

                case 'precision' :
                    if ($value === NULL) {
                        $value = -1;
                    }

                    if (($value < -1) || ($value > 30)) {
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("'$value' precision is not a whole number less than 30.");
                    }
                    break;

                default:
                    #require_once 'Zend/Locale/Exception.php';
                    throw new Zend_Locale_Exception("Unknown option: '$name' = '$value'");
                    break;

            }
        }

        return $options;
    }

    /**
     * Changes the numbers/digits within a given string from one script to another
     * 'Decimal' representated the stardard numbers 0-9, if a script does not exist
     * an exception will be thrown.
     *
     * Examples for conversion from Arabic to Latin numerals:
     *   convertNumerals('١١٠ Tests', 'Arab'); -> returns '100 Tests'
     * Example for conversion from Latin to Arabic numerals:
     *   convertNumerals('100 Tests', 'Latn', 'Arab'); -> returns '١١٠ Tests'
     *
     * @param  string  $input  String to convert
     * @param  string  $from   Script to parse, see {@link Zend_Locale::getScriptList()} for details.
     * @param  string  $to     OPTIONAL Script to convert to
     * @return string  Returns the converted input
     * @throws Zend_Locale_Exception
     */
    public static function convertNumerals($input, $from, $to = null)
    {
        if (!self::_getUniCodeSupport()) {
            trigger_error("Sorry, your PCRE extension does not support UTF8 which is needed for the I18N core", E_USER_NOTICE);
        }

        $from   = strtolower($from);
        $source = Zend_Locale_Data::getContent('en', 'numberingsystem', $from);
        if (empty($source)) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("Unknown script '$from'. Use 'Latn' for digits 0,1,2,3,4,5,6,7,8,9.");
        }

        if ($to !== null) {
            $to     = strtolower($to);
            $target = Zend_Locale_Data::getContent('en', 'numberingsystem', $to);
            if (empty($target)) {
                #require_once 'Zend/Locale/Exception.php';
                throw new Zend_Locale_Exception("Unknown script '$to'. Use 'Latn' for digits 0,1,2,3,4,5,6,7,8,9.");
            }
        } else {
            $target = '0123456789';
        }

        for ($x = 0; $x < 10; ++$x) {
            $asource[$x] = "/" . iconv_substr($source, $x, 1, 'UTF-8') . "/u";
            $atarget[$x] = iconv_substr($target, $x, 1, 'UTF-8');
        }

        return preg_replace($asource, $atarget, $input);
    }

    /**
     * Returns the normalized number from a localized one
     * Parsing depends on given locale (grouping and decimal)
     *
     * Examples for input:
     * '2345.4356,1234' = 23455456.1234
     * '+23,3452.123' = 233452.123
     * '12343 ' = 12343
     * '-9456' = -9456
     * '0' = 0
     *
     * @param  string $input    Input string to parse for numbers
     * @param  array  $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return string Returns the extracted number
     * @throws Zend_Locale_Exception
     */
    public static function getNumber($input, array $options = array())
    {
        $options = self::_checkOptions($options) + self::$_options;
        if (!is_string($input)) {
            return $input;
        }

        if (!self::isNumber($input, $options)) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception('No localized value in ' . $input . ' found, or the given number does not match the localized format');
        }

        // Get correct signs for this locale
        $symbols = Zend_Locale_Data::getList($options['locale'],'symbols');
        // Change locale input to be default number
        if ((strpos($input, $symbols['minus']) !== false) ||
            (strpos($input, '-') !== false)) {
            $input = strtr($input, array($symbols['minus'] => '', '-' => ''));
            $input = '-' . $input;
        }

        $input = str_replace($symbols['group'],'', $input);
        if (strpos($input, $symbols['decimal']) !== false) {
            if ($symbols['decimal'] != '.') {
                $input = str_replace($symbols['decimal'], ".", $input);
            }

            $pre = substr($input, strpos($input, '.') + 1);
            if ($options['precision'] === null) {
                $options['precision'] = strlen($pre);
            }

            if (strlen($pre) >= $options['precision']) {
                $input = substr($input, 0, strlen($input) - strlen($pre) + $options['precision']);
            }

            if (($options['precision'] == 0) && ($input[strlen($input) - 1] == '.')) {
                $input = substr($input, 0, -1);
            }
        }

        return $input;
    }

    /**
     * Returns a locale formatted number depending on the given options.
     * The seperation and fraction sign is used from the set locale.
     * ##0.#  -> 12345.12345 -> 12345.12345
     * ##0.00 -> 12345.12345 -> 12345.12
     * ##,##0.00 -> 12345.12345 -> 12,345.12
     *
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: number_format, locale, precision. See {@link setOptions()} for details.
     * @return  string  locale formatted number
     * @throws Zend_Locale_Exception
     */
    public static function toNumber($value, array $options = array())
    {
        // load class within method for speed
        #require_once 'Zend/Locale/Math.php';

        $value             = Zend_Locale_Math::normalize($value);
        $value             = Zend_Locale_Math::floatalize($value);
        $options           = self::_checkOptions($options) + self::$_options;
        $options['locale'] = (string) $options['locale'];

        // Get correct signs for this locale
        $symbols = Zend_Locale_Data::getList($options['locale'], 'symbols');
        $oenc = iconv_get_encoding('internal_encoding');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        // Get format
        $format = $options['number_format'];
        if ($format === null) {
            $format  = Zend_Locale_Data::getContent($options['locale'], 'decimalnumber');
            $format  = self::_seperateFormat($format, $value, $options['precision']);

            if ($options['precision'] !== null) {
                $value   = Zend_Locale_Math::normalize(Zend_Locale_Math::round($value, $options['precision']));
            }
        } else {
            // seperate negative format pattern when available
            $format  = self::_seperateFormat($format, $value, $options['precision']);
            if (strpos($format, '.')) {
                if (is_numeric($options['precision'])) {
                    $value = Zend_Locale_Math::round($value, $options['precision']);
                } else {
                    if (substr($format, iconv_strpos($format, '.') + 1, 3) == '###') {
                        $options['precision'] = null;
                    } else {
                        $options['precision'] = iconv_strlen(iconv_substr($format, iconv_strpos($format, '.') + 1,
                                                             iconv_strrpos($format, '0') - iconv_strpos($format, '.')));
                        $format = iconv_substr($format, 0, iconv_strpos($format, '.') + 1) . '###'
                                . iconv_substr($format, iconv_strrpos($format, '0') + 1);
                    }
                }
            } else {
                $value = Zend_Locale_Math::round($value, 0);
                $options['precision'] = 0;
            }
            $value = Zend_Locale_Math::normalize($value);
        }

        if (iconv_strpos($format, '0') === false) {
            iconv_set_encoding('internal_encoding', $oenc);
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception('Wrong format... missing 0');
        }

        // get number parts
        $pos = iconv_strpos($value, '.');
        if ($pos !== false) {
            if ($options['precision'] === null) {
                $precstr = iconv_substr($value, $pos + 1);
            } else {
                $precstr = iconv_substr($value, $pos + 1, $options['precision']);
                if (iconv_strlen($precstr) < $options['precision']) {
                    $precstr = $precstr . str_pad("0", ($options['precision'] - iconv_strlen($precstr)), "0");
                }
            }
        } else {
            if ($options['precision'] > 0) {
                $precstr = str_pad("0", ($options['precision']), "0");
            }
        }

        if ($options['precision'] === null) {
            if (isset($precstr)) {
                $options['precision'] = iconv_strlen($precstr);
            } else {
                $options['precision'] = 0;
            }
        }

        // get fraction and format lengths
        if (strpos($value, '.') !== false) {
            $number = substr((string) $value, 0, strpos($value, '.'));
        } else {
            $number = $value;
        }

        $prec = call_user_func(Zend_Locale_Math::$sub, $value, $number, $options['precision']);
        $prec = Zend_Locale_Math::floatalize($prec);
        $prec = Zend_Locale_Math::normalize($prec);
        if (iconv_strpos($prec, '-') !== false) {
            $prec = iconv_substr($prec, 1);
        }

        if (($prec == 0) and ($options['precision'] > 0)) {
            $prec = "0.0";
        }

        if (($options['precision'] + 2) > iconv_strlen($prec)) {
            $prec = str_pad((string) $prec, $options['precision'] + 2, "0", STR_PAD_RIGHT);
        }

        if (iconv_strpos($number, '-') !== false) {
            $number = iconv_substr($number, 1);
        }
        $group  = iconv_strrpos($format, ',');
        $group2 = iconv_strpos ($format, ',');
        $point  = iconv_strpos ($format, '0');
        // Add fraction
        $rest = "";
        if (iconv_strpos($format, '.')) {
            $rest   = iconv_substr($format, iconv_strpos($format, '.') + 1);
            $length = iconv_strlen($rest);
            for($x = 0; $x < $length; ++$x) {
                if (($rest[0] == '0') || ($rest[0] == '#')) {
                    $rest = iconv_substr($rest, 1);
                }
            }
            $format = iconv_substr($format, 0, iconv_strlen($format) - iconv_strlen($rest));
        }

        if ($options['precision'] == '0') {
            if (iconv_strrpos($format, '-') != 0) {
                $format = iconv_substr($format, 0, $point)
                        . iconv_substr($format, iconv_strrpos($format, '#') + 2);
            } else {
                $format = iconv_substr($format, 0, $point);
            }
        } else {
            $format = iconv_substr($format, 0, $point) . $symbols['decimal']
                               . iconv_substr($prec, 2);
        }

        $format .= $rest;
        // Add seperation
        if ($group == 0) {
            // no seperation
            $format = $number . iconv_substr($format, $point);
        } else if ($group == $group2) {
            // only 1 seperation
            $seperation = ($point - $group);
            for ($x = iconv_strlen($number); $x > $seperation; $x -= $seperation) {
                if (iconv_substr($number, 0, $x - $seperation) !== "") {
                    $number = iconv_substr($number, 0, $x - $seperation) . $symbols['group']
                            . iconv_substr($number, $x - $seperation);
                }
            }
            $format = iconv_substr($format, 0, iconv_strpos($format, '#')) . $number . iconv_substr($format, $point);
        } else {

            // 2 seperations
            if (iconv_strlen($number) > ($point - $group)) {
                $seperation = ($point - $group);
                $number = iconv_substr($number, 0, iconv_strlen($number) - $seperation) . $symbols['group']
                        . iconv_substr($number, iconv_strlen($number) - $seperation);

                if ((iconv_strlen($number) - 1) > ($point - $group + 1)) {
                    $seperation2 = ($group - $group2 - 1);
                    for ($x = iconv_strlen($number) - $seperation2 - 2; $x > $seperation2; $x -= $seperation2) {
                        $number = iconv_substr($number, 0, $x - $seperation2) . $symbols['group']
                                . iconv_substr($number, $x - $seperation2);
                    }
                }

            }
            $format = iconv_substr($format, 0, iconv_strpos($format, '#')) . $number . iconv_substr($format, $point);
        }
        // set negative sign
        if (call_user_func(Zend_Locale_Math::$comp, $value, 0, $options['precision']) < 0) {
            if (iconv_strpos($format, '-') === false) {
                $format = $symbols['minus'] . $format;
            } else {
                $format = str_replace('-', $symbols['minus'], $format);
            }
        }

        iconv_set_encoding('internal_encoding', $oenc);
        return (string) $format;
    }

    private static function _seperateFormat($format, $value, $precision)
    {
        if (iconv_strpos($format, ';') !== false) {
            if (call_user_func(Zend_Locale_Math::$comp, $value, 0, $precision) < 0) {
                $tmpformat = iconv_substr($format, iconv_strpos($format, ';') + 1);
                if ($tmpformat[0] == '(') {
                    $format = iconv_substr($format, 0, iconv_strpos($format, ';'));
                } else {
                    $format = $tmpformat;
                }
            } else {
                $format = iconv_substr($format, 0, iconv_strpos($format, ';'));
            }
        }

        return $format;
    }


    /**
     * Checks if the input contains a normalized or localized number
     *
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  boolean           Returns true if a number was found
     */
    public static function isNumber($input, array $options = array())
    {
        if (!self::_getUniCodeSupport()) {
            trigger_error("Sorry, your PCRE extension does not support UTF8 which is needed for the I18N core", E_USER_NOTICE);
        }

        $options = self::_checkOptions($options) + self::$_options;

        // Get correct signs for this locale
        $symbols = Zend_Locale_Data::getList($options['locale'],'symbols');

        $regexs = Zend_Locale_Format::_getRegexForType('decimalnumber', $options);
        $regexs = array_merge($regexs, Zend_Locale_Format::_getRegexForType('scientificnumber', $options));
        if (!empty($input) && ($input[0] == $symbols['decimal'])) {
            $input = 0 . $input;
        }
        foreach ($regexs as $regex) {
            preg_match($regex, $input, $found);
            if (isset($found[0])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Internal method to convert cldr number syntax into regex
     *
     * @param  string $type
     * @return string
     */
    private static function _getRegexForType($type, $options)
    {
        $decimal  = Zend_Locale_Data::getContent($options['locale'], $type);
        $decimal  = preg_replace('/[^#0,;\.\-Ee]/u', '',$decimal);
        $patterns = explode(';', $decimal);

        if (count($patterns) == 1) {
            $patterns[1] = '-' . $patterns[0];
        }

        $symbols = Zend_Locale_Data::getList($options['locale'],'symbols');

        foreach($patterns as $pkey => $pattern) {
            $regex[$pkey]  = '/^';
            $rest   = 0;
            $end    = null;
            if (strpos($pattern, '.') !== false) {
                $end     = substr($pattern, strpos($pattern, '.') + 1);
                $pattern = substr($pattern, 0, -strlen($end) - 1);
            }

            if (strpos($pattern, ',') !== false) {
                $parts = explode(',', $pattern);
                $count = count($parts);
                foreach($parts as $key => $part) {
                    switch ($part) {
                        case '#':
                        case '-#':
                            if ($part[0] == '-') {
                                $regex[$pkey] .= '[' . $symbols['minus'] . '-]{0,1}';
                            } else {
                                $regex[$pkey] .= '[' . $symbols['plus'] . '+]{0,1}';
                            }

                            if (($parts[$key + 1]) == '##0')  {
                                $regex[$pkey] .= '[0-9]{1,3}';
                            } else if (($parts[$key + 1]) == '##') {
                                $regex[$pkey] .= '[0-9]{1,2}';
                            } else {
                                throw new Zend_Locale_Exception('Unsupported token for numberformat (Pos 1):"' . $pattern . '"');
                            }
                            break;
                        case '##':
                            if ($parts[$key + 1] == '##0') {
                                $regex[$pkey] .=  '(\\' . $symbols['group'] . '{0,1}[0-9]{2})*';
                            } else {
                                throw new Zend_Locale_Exception('Unsupported token for numberformat (Pos 2):"' . $pattern . '"');
                            }
                            break;
                        case '##0':
                            if ($parts[$key - 1] == '##') {
                                $regex[$pkey] .= '[0-9]';
                            } else if (($parts[$key - 1] == '#') || ($parts[$key - 1] == '-#')) {
                                $regex[$pkey] .= '(\\' . $symbols['group'] . '{0,1}[0-9]{3})*';
                            } else {
                                throw new Zend_Locale_Exception('Unsupported token for numberformat (Pos 3):"' . $pattern . '"');
                            }
                            break;
                        case '#0':
                            if ($key == 0) {
                                $regex[$pkey] .= '[0-9]*';
                            } else {
                                throw new Zend_Locale_Exception('Unsupported token for numberformat (Pos 4):"' . $pattern . '"');
                            }
                            break;
                    }
                }
            }

            if (strpos($pattern, 'E') !== false) {
                if (($pattern == '#E0') || ($pattern == '#E00')) {
                    $regex[$pkey] .= '[' . $symbols['plus']. '+]{0,1}[0-9]{1,}(\\' . $symbols['decimal'] . '[0-9]{1,})*[eE][' . $symbols['plus']. '+]{0,1}[0-9]{1,}';
                } else if (($pattern == '-#E0') || ($pattern == '-#E00')) {
                    $regex[$pkey] .= '[' . $symbols['minus']. '-]{0,1}[0-9]{1,}(\\' . $symbols['decimal'] . '[0-9]{1,})*[eE][' . $symbols['minus']. '-]{0,1}[0-9]{1,}';
                } else {
                    throw new Zend_Locale_Exception('Unsupported token for numberformat (Pos 5):"' . $pattern . '"');
                }
            }

            if (!empty($end)) {
                if ($end == '###') {
                    $regex[$pkey] .= '(\\' . $symbols['decimal'] . '{1}[0-9]{1,}){0,1}';
                } else if ($end == '###-') {
                    $regex[$pkey] .= '(\\' . $symbols['decimal'] . '{1}[0-9]{1,}){0,1}[' . $symbols['minus']. '-]';
                } else {
                    throw new Zend_Locale_Exception('Unsupported token for numberformat (Pos 6):"' . $pattern . '"');
                }
            }

            $regex[$pkey] .= '$/u';
        }

        return $regex;
    }

    /**
     * Alias for getNumber
     *
     * @param   string  $value    Number to localize
     * @param   array   $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return  float
     */
    public static function getFloat($input, array $options = array())
    {
        return floatval(self::getNumber($input, $options));
    }

    /**
     * Returns a locale formatted integer number
     * Alias for toNumber()
     *
     * @param   string  $value    Number to normalize
     * @param   array   $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return  string  Locale formatted number
     */
    public static function toFloat($value, array $options = array())
    {
        $options['number_format'] = Zend_Locale_Format::STANDARD;
        return self::toNumber($value, $options);
    }

    /**
     * Returns if a float was found
     * Alias for isNumber()
     *
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  boolean           Returns true if a number was found
     */
    public static function isFloat($value, array $options = array())
    {
        return self::isNumber($value, $options);
    }

    /**
     * Returns the first found integer from an string
     * Parsing depends on given locale (grouping and decimal)
     *
     * Examples for input:
     * '  2345.4356,1234' = 23455456
     * '+23,3452.123' = 233452
     * ' 12343 ' = 12343
     * '-9456km' = -9456
     * '0' = 0
     * '(-){0,1}(\d+(\.){0,1})*(\,){0,1})\d+'
     *
     * @param   string   $input    Input string to parse for numbers
     * @param   array    $options  Options: locale. See {@link setOptions()} for details.
     * @return  integer            Returns the extracted number
     */
    public static function getInteger($input, array $options = array())
    {
        $options['precision'] = 0;
        return intval(self::getFloat($input, $options));
    }

    /**
     * Returns a localized number
     *
     * @param   string  $value    Number to normalize
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  string            Locale formatted number
     */
    public static function toInteger($value, array $options = array())
    {
        $options['precision'] = 0;
        $options['number_format'] = Zend_Locale_Format::STANDARD;
        return self::toNumber($value, $options);
    }

    /**
     * Returns if a integer was found
     *
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  boolean           Returns true if a integer was found
     */
    public static function isInteger($value, array $options = array())
    {
        if (!self::isNumber($value, $options)) {
            return false;
        }

        if (self::getInteger($value, $options) == self::getFloat($value, $options)) {
            return true;
        }

        return false;
    }

    /**
     * Converts a format string from PHP's date format to ISO format
     * Remember that Zend Date always returns localized string, so a month name which returns the english
     * month in php's date() will return the translated month name with this function... use 'en' as locale
     * if you are in need of the original english names
     *
     * The conversion has the following restrictions:
     * 'a', 'A' - Meridiem is not explicit upper/lowercase, you have to upper/lowercase the translated value yourself
     *
     * @param  string  $format  Format string in PHP's date format
     * @return string           Format string in ISO format
     */
    public static function convertPhpToIsoFormat($format)
    {
        if ($format === null) {
            return null;
        }

        $convert = array('d' => 'dd'  , 'D' => 'EE'  , 'j' => 'd'   , 'l' => 'EEEE', 'N' => 'eee' , 'S' => 'SS'  ,
                         'w' => 'e'   , 'z' => 'D'   , 'W' => 'ww'  , 'F' => 'MMMM', 'm' => 'MM'  , 'M' => 'MMM' ,
                         'n' => 'M'   , 't' => 'ddd' , 'L' => 'l'   , 'o' => 'YYYY', 'Y' => 'yyyy', 'y' => 'yy'  ,
                         'a' => 'a'   , 'A' => 'a'   , 'B' => 'B'   , 'g' => 'h'   , 'G' => 'H'   , 'h' => 'hh'  ,
                         'H' => 'HH'  , 'i' => 'mm'  , 's' => 'ss'  , 'e' => 'zzzz', 'I' => 'I'   , 'O' => 'Z'   ,
                         'P' => 'ZZZZ', 'T' => 'z'   , 'Z' => 'X'   , 'c' => 'yyyy-MM-ddTHH:mm:ssZZZZ',
                         'r' => 'r'   , 'U' => 'U');
        $values = str_split($format);
        foreach ($values as $key => $value) {
            if (isset($convert[$value]) === true) {
                $values[$key] = $convert[$value];
            }
        }

        return join($values);
    }

    /**
     * Parse date and split in named array fields
     *
     * @param   string  $date     Date string to parse
     * @param   array   $options  Options: format_type, fix_date, locale, date_format. See {@link setOptions()} for details.
     * @return  array             Possible array members: day, month, year, hour, minute, second, fixed, format
     */
    private static function _parseDate($date, $options)
    {
        if (!self::_getUniCodeSupport()) {
            trigger_error("Sorry, your PCRE extension does not support UTF8 which is needed for the I18N core", E_USER_NOTICE);
        }

        $options = self::_checkOptions($options) + self::$_options;
        $test = array('h', 'H', 'm', 's', 'y', 'Y', 'M', 'd', 'D', 'E', 'S', 'l', 'B', 'I',
                       'X', 'r', 'U', 'G', 'w', 'e', 'a', 'A', 'Z', 'z', 'v');

        $format = $options['date_format'];
        $number = $date; // working copy
        $result['date_format'] = $format; // save the format used to normalize $number (convenience)
        $result['locale'] = $options['locale']; // save the locale used to normalize $number (convenience)

        $oenc = iconv_get_encoding('internal_encoding');
        iconv_set_encoding('internal_encoding', 'UTF-8');
        $day   = iconv_strpos($format, 'd');
        $month = iconv_strpos($format, 'M');
        $year  = iconv_strpos($format, 'y');
        $hour  = iconv_strpos($format, 'H');
        $min   = iconv_strpos($format, 'm');
        $sec   = iconv_strpos($format, 's');
        $am    = null;
        if ($hour === false) {
            $hour = iconv_strpos($format, 'h');
        }
        if ($year === false) {
            $year = iconv_strpos($format, 'Y');
        }
        if ($day === false) {
            $day = iconv_strpos($format, 'E');
            if ($day === false) {
                $day = iconv_strpos($format, 'D');
            }
        }

        if ($day !== false) {
            $parse[$day]   = 'd';
            if (!empty($options['locale']) && ($options['locale'] !== 'root') &&
                (!is_object($options['locale']) || ((string) $options['locale'] !== 'root'))) {
                // erase day string
                    $daylist = Zend_Locale_Data::getList($options['locale'], 'day');
                foreach($daylist as $key => $name) {
                    if (iconv_strpos($number, $name) !== false) {
                        $number = str_replace($name, "EEEE", $number);
                        break;
                    }
                }
            }
        }
        $position = false;

        if ($month !== false) {
            $parse[$month] = 'M';
            if (!empty($options['locale']) && ($options['locale'] !== 'root') &&
                (!is_object($options['locale']) || ((string) $options['locale'] !== 'root'))) {
                    // prepare to convert month name to their numeric equivalents, if requested,
                    // and we have a $options['locale']
                    $position = self::_replaceMonth($number, Zend_Locale_Data::getList($options['locale'],
                        'month'));
                if ($position === false) {
                    $position = self::_replaceMonth($number, Zend_Locale_Data::getList($options['locale'],
                        'month', array('gregorian', 'format', 'abbreviated')));
                }
            }
        }
        if ($year !== false) {
            $parse[$year]  = 'y';
        }
        if ($hour !== false) {
            $parse[$hour] = 'H';
        }
        if ($min !== false) {
            $parse[$min] = 'm';
        }
        if ($sec !== false) {
            $parse[$sec] = 's';
        }

        if (empty($parse)) {
            iconv_set_encoding('internal_encoding', $oenc);
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("Unknown date format, neither date nor time in '" . $format . "' found");
        }
        ksort($parse);

        // get daytime
        if (iconv_strpos($format, 'a') !== false) {
            if (iconv_strpos(strtoupper($number), strtoupper(Zend_Locale_Data::getContent($options['locale'], 'am'))) !== false) {
                $am = true;
            } else if (iconv_strpos(strtoupper($number), strtoupper(Zend_Locale_Data::getContent($options['locale'], 'pm'))) !== false) {
                $am = false;
            }
        }

        // split number parts
        $split = false;
        preg_match_all('/\d+/u', $number, $splitted);

        if (count($splitted[0]) == 0) {
            iconv_set_encoding('internal_encoding', $oenc);
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("No date part in '$date' found.");
        }
        if (count($splitted[0]) == 1) {
            $split = 0;
        }
        $cnt = 0;
        foreach($parse as $key => $value) {

            switch($value) {
                case 'd':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['day']    = $splitted[0][$cnt];
                        }
                    } else {
                        $result['day'] = iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 'M':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['month']  = $splitted[0][$cnt];
                        }
                    } else {
                        $result['month'] = iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 'y':
                    $length = 2;
                    if ((iconv_substr($format, $year, 4) == 'yyyy')
                     || (iconv_substr($format, $year, 4) == 'YYYY')) {
                        $length = 4;
                    }

                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['year']   = $splitted[0][$cnt];
                        }
                    } else {
                        $result['year']   = iconv_substr($splitted[0][0], $split, $length);
                        $split += $length;
                    }

                    ++$cnt;
                    break;
                case 'H':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['hour']   = $splitted[0][$cnt];
                        }
                    } else {
                        $result['hour']   = iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 'm':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['minute'] = $splitted[0][$cnt];
                        }
                    } else {
                        $result['minute'] = iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 's':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['second'] = $splitted[0][$cnt];
                        }
                    } else {
                        $result['second'] = iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
            }
        }

        // AM/PM correction
        if ($hour !== false) {
            if (($am === true) and ($result['hour'] == 12)){
                $result['hour'] = 0;
            } else if (($am === false) and ($result['hour'] != 12)) {
                $result['hour'] += 12;
            }
        }

        if ($options['fix_date'] === true) {
            $result['fixed'] = 0; // nothing has been "fixed" by swapping date parts around (yet)
        }

        if ($day !== false) {
            // fix false month
            if (isset($result['day']) and isset($result['month'])) {
                if (($position !== false) and ((iconv_strpos($date, $result['day']) === false) or
                                               (isset($result['year']) and (iconv_strpos($date, $result['year']) === false)))) {
                    if ($options['fix_date'] !== true) {
                        iconv_set_encoding('internal_encoding', $oenc);
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Unable to parse date '$date' using '" . $format
                            . "' (false month, $position, $month)");
                    }
                    $temp = $result['day'];
                    $result['day']   = $result['month'];
                    $result['month'] = $temp;
                    $result['fixed'] = 1;
                }
            }

            // fix switched values d <> y
            if (isset($result['day']) and isset($result['year'])) {
                if ($result['day'] > 31) {
                    if ($options['fix_date'] !== true) {
                        iconv_set_encoding('internal_encoding', $oenc);
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Unable to parse date '$date' using '"
                                                      . $format . "' (d <> y)");
                    }
                    $temp = $result['year'];
                    $result['year'] = $result['day'];
                    $result['day']  = $temp;
                    $result['fixed'] = 2;
                }
            }

            // fix switched values M <> y
            if (isset($result['month']) and isset($result['year'])) {
                if ($result['month'] > 31) {
                    if ($options['fix_date'] !== true) {
                        iconv_set_encoding('internal_encoding', $oenc);
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Unable to parse date '$date' using '"
                                                      . $format . "' (M <> y)");
                    }
                    $temp = $result['year'];
                    $result['year']  = $result['month'];
                    $result['month'] = $temp;
                    $result['fixed'] = 3;
                }
            }

            // fix switched values M <> d
            if (isset($result['month']) and isset($result['day'])) {
                if ($result['month'] > 12) {
                    if ($options['fix_date'] !== true || $result['month'] > 31) {
                        iconv_set_encoding('internal_encoding', $oenc);
                        #require_once 'Zend/Locale/Exception.php';
                        throw new Zend_Locale_Exception("Unable to parse date '$date' using '"
                                                      . $format . "' (M <> d)");
                    }
                    $temp = $result['day'];
                    $result['day']   = $result['month'];
                    $result['month'] = $temp;
                    $result['fixed'] = 4;
                }
            }
        }

        if (isset($result['year'])) {
            if (((iconv_strlen($result['year']) == 2) && ($result['year'] < 10)) ||
                (((iconv_strpos($format, 'yy') !== false) && (iconv_strpos($format, 'yyyy') === false)) ||
                ((iconv_strpos($format, 'YY') !== false) && (iconv_strpos($format, 'YYYY') === false)))) {
                if (($result['year'] >= 0) && ($result['year'] < 100)) {
                    if ($result['year'] < 70) {
                        $result['year'] = (int) $result['year'] + 100;
                    }

                    $result['year'] = (int) $result['year'] + 1900;
                }
            }
        }

        iconv_set_encoding('internal_encoding', $oenc);
        return $result;
    }

    /**
     * Search $number for a month name found in $monthlist, and replace if found.
     *
     * @param  string  $number     Date string (modified)
     * @param  array   $monthlist  List of month names
     *
     * @return int|false           Position of replaced string (false if nothing replaced)
     */
    protected static function _replaceMonth(&$number, $monthlist)
    {
        // If $locale was invalid, $monthlist will default to a "root" identity
        // mapping for each month number from 1 to 12.
        // If no $locale was given, or $locale was invalid, do not use this identity mapping to normalize.
        // Otherwise, translate locale aware month names in $number to their numeric equivalents.
        $position = false;
        if ($monthlist && $monthlist[1] != 1) {
            foreach($monthlist as $key => $name) {
                if (($position = iconv_strpos($number, $name, 0, 'UTF-8')) !== false) {
                    $number   = str_ireplace($name, $key, $number);
                    return $position;
                }
            }
        }

        return false;
    }

    /**
     * Returns the default date format for $locale.
     *
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale of $number, possibly in string form (e.g. 'de_AT')
     * @return string  format
     * @throws Zend_Locale_Exception  throws an exception when locale data is broken
     */
    public static function getDateFormat($locale = null)
    {
        $format = Zend_Locale_Data::getContent($locale, 'date');
        if (empty($format)) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("failed to receive data from locale $locale");
        }

        return $format;
    }

    /**
     * Returns an array with the normalized date from an locale date
     * a input of 10.01.2006 without a $locale would return:
     * array ('day' => 10, 'month' => 1, 'year' => 2006)
     * The 'locale' option is only used to convert human readable day
     * and month names to their numeric equivalents.
     * The 'format' option allows specification of self-defined date formats,
     * when not using the default format for the 'locale'.
     *
     * @param   string  $date     Date string
     * @param   array   $options  Options: format_type, fix_date, locale, date_format. See {@link setOptions()} for details.
     * @return  array             Possible array members: day, month, year, hour, minute, second, fixed, format
     */
    public static function getDate($date, array $options = array())
    {
        $options = self::_checkOptions($options) + self::$_options;
        if (empty($options['date_format'])) {
            $options['format_type'] = 'iso';
            $options['date_format'] = self::getDateFormat($options['locale']);
        }

        return self::_parseDate($date, $options);
    }

    /**
     * Returns if the given datestring contains all date parts from the given format.
     * If no format is given, the default date format from the locale is used
     * If you want to check if the date is a proper date you should use Zend_Date::isDate()
     *
     * @param   string  $date     Date string
     * @param   array   $options  Options: format_type, fix_date, locale, date_format. See {@link setOptions()} for details.
     * @return  boolean
     */
    public static function checkDateFormat($date, array $options = array())
    {
        try {
            $date = self::getDate($date, $options);
        } catch (Exception $e) {
            return false;
        }

        if (empty($options['date_format'])) {
            $options['format_type'] = 'iso';
            $options['date_format'] = self::getDateFormat($options['locale']);
        }
        $options = self::_checkOptions($options) + self::$_options;

        // day expected but not parsed
        if ((iconv_strpos($options['date_format'], 'd', 0, 'UTF-8') !== false) and (!isset($date['day']) or ($date['day'] === ""))) {
            return false;
        }

        // month expected but not parsed
        if ((iconv_strpos($options['date_format'], 'M', 0, 'UTF-8') !== false) and (!isset($date['month']) or ($date['month'] === ""))) {
            return false;
        }

        // year expected but not parsed
        if (((iconv_strpos($options['date_format'], 'Y', 0, 'UTF-8') !== false) or
             (iconv_strpos($options['date_format'], 'y', 0, 'UTF-8') !== false)) and (!isset($date['year']) or ($date['year'] === ""))) {
            return false;
        }

        // second expected but not parsed
        if ((iconv_strpos($options['date_format'], 's', 0, 'UTF-8') !== false) and (!isset($date['second']) or ($date['second'] === ""))) {
            return false;
        }

        // minute expected but not parsed
        if ((iconv_strpos($options['date_format'], 'm', 0, 'UTF-8') !== false) and (!isset($date['minute']) or ($date['minute'] === ""))) {
            return false;
        }

        // hour expected but not parsed
        if (((iconv_strpos($options['date_format'], 'H', 0, 'UTF-8') !== false) or
             (iconv_strpos($options['date_format'], 'h', 0, 'UTF-8') !== false)) and (!isset($date['hour']) or ($date['hour'] === ""))) {
            return false;
        }

        return true;
    }

    /**
     * Returns the default time format for $locale.
     *
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale of $number, possibly in string form (e.g. 'de_AT')
     * @return string  format
     */
    public static function getTimeFormat($locale = null)
    {
        $format = Zend_Locale_Data::getContent($locale, 'time');
        if (empty($format)) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("failed to receive data from locale $locale");
        }
        return $format;
    }

    /**
     * Returns an array with 'hour', 'minute', and 'second' elements extracted from $time
     * according to the order described in $format.  For a format of 'H:m:s', and
     * an input of 11:20:55, getTime() would return:
     * array ('hour' => 11, 'minute' => 20, 'second' => 55)
     * The optional $locale parameter may be used to help extract times from strings
     * containing both a time and a day or month name.
     *
     * @param   string  $time     Time string
     * @param   array   $options  Options: format_type, fix_date, locale, date_format. See {@link setOptions()} for details.
     * @return  array             Possible array members: day, month, year, hour, minute, second, fixed, format
     */
    public static function getTime($time, array $options = array())
    {
        $options = self::_checkOptions($options) + self::$_options;
        if (empty($options['date_format'])) {
            $options['format_type'] = 'iso';
            $options['date_format'] = self::getTimeFormat($options['locale']);
        }
        return self::_parseDate($time, $options);
    }

    /**
     * Returns the default datetime format for $locale.
     *
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale of $number, possibly in string form (e.g. 'de_AT')
     * @return string  format
     */
    public static function getDateTimeFormat($locale = null)
    {
        $format = Zend_Locale_Data::getContent($locale, 'datetime');
        if (empty($format)) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("failed to receive data from locale $locale");
        }
        return $format;
    }

    /**
     * Returns an array with 'year', 'month', 'day', 'hour', 'minute', and 'second' elements
     * extracted from $datetime according to the order described in $format.  For a format of 'd.M.y H:m:s',
     * and an input of 10.05.1985 11:20:55, getDateTime() would return:
     * array ('year' => 1985, 'month' => 5, 'day' => 10, 'hour' => 11, 'minute' => 20, 'second' => 55)
     * The optional $locale parameter may be used to help extract times from strings
     * containing both a time and a day or month name.
     *
     * @param   string  $datetime DateTime string
     * @param   array   $options  Options: format_type, fix_date, locale, date_format. See {@link setOptions()} for details.
     * @return  array             Possible array members: day, month, year, hour, minute, second, fixed, format
     */
    public static function getDateTime($datetime, array $options = array())
    {
        $options = self::_checkOptions($options) + self::$_options;
        if (empty($options['date_format'])) {
            $options['format_type'] = 'iso';
            $options['date_format'] = self::getDateTimeFormat($options['locale']);
        }
        return self::_parseDate($datetime, $options);
    }

    /**
     * Internal method to detect of Unicode supports UTF8
     * which should be enabled within vanilla php installations
     *
     * @return boolean
     */
    protected static function _getUniCodeSupport()
    {
        return (@preg_match('/\pL/u', 'a')) ? true : false;
    }
}
