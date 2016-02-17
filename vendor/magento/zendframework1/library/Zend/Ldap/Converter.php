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
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Ldap_Converter is a collection of useful LDAP related conversion functions.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Converter
{
    const STANDARD         = 0;
    const BOOLEAN          = 1;
    const GENERALIZED_TIME = 2;

    /**
     * Converts all ASCII chars < 32 to "\HEX"
     *
     * @see Net_LDAP2_Util::asc2hex32() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string $string String to convert
     * @return string
     */
    public static function ascToHex32($string)
    {
        for ($i = 0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            if (ord($char)<32) {
                $hex = dechex(ord($char));
                if (strlen($hex) == 1) $hex = '0' . $hex;
                $string = str_replace($char, '\\' . $hex, $string);
            }
        }
        return $string;
    }

    /**
     * Converts all Hex expressions ("\HEX") to their original ASCII characters
     *
     * @see Net_LDAP2_Util::hex2asc() from Benedikt Hallinger <beni@php.net>,
     * heavily based on work from DavidSmith@byu.net
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>, heavily based on work from DavidSmith@byu.net
     *
     * @param  string $string String to convert
     * @return string
     */
    public static function hex32ToAsc($string)
    {
        // Using a callback, since PHP 5.5 has deprecated the /e modifier in preg_replace.
        $string = preg_replace_callback("/\\\([0-9A-Fa-f]{2})/", array('Zend_Ldap_Converter', '_charHex32ToAsc'), $string);
        return $string;
    }

    /**
     * Convert a single slash-prefixed character from Hex32 to ASCII.
     * Used as a callback in @see hex32ToAsc()
     * @param array $matches
     *
     * @return string
     */
    private static function _charHex32ToAsc(array $matches)
    {
        return chr(hexdec($matches[0]));
    }

    /**
     * Convert any value to an LDAP-compatible value.
     *
     * By setting the <var>$type</var>-parameter the conversion of a certain
     * type can be forced
     *
     * @todo write more tests
     *
     * @param    mixed     $value     The value to convert
     * @param    int       $ytpe      The conversion type to use
     * @return    string
     * @throws    Zend_Ldap_Converter_Exception
     */
    public static function toLdap($value, $type = self::STANDARD)
    {
        try {
            switch ($type) {
                case self::BOOLEAN:
                    return self::toldapBoolean($value);
                    break;
                case self::GENERALIZED_TIME:
                    return self::toLdapDatetime($value);
                    break;
                default:
                    if (is_string($value)) {
                        return $value;
                    } else if (is_int($value) || is_float($value)) {
                        return (string)$value;
                    } else if (is_bool($value)) {
                        return self::toldapBoolean($value);
                    } else if (is_object($value)) {
                        if ($value instanceof DateTime) {
                            return self::toLdapDatetime($value);
                        } else if ($value instanceof Zend_Date) {
                            return self::toLdapDatetime($value);
                        } else {
                            return self::toLdapSerialize($value);
                        }
                    } else if (is_array($value)) {
                        return self::toLdapSerialize($value);
                    } else if (is_resource($value) && get_resource_type($value) === 'stream') {
                        return stream_get_contents($value);
                    } else {
                        return null;
                    }
                    break;
            }
        } catch (Exception $e) {
            throw new Zend_Ldap_Converter_Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Converts a date-entity to an LDAP-compatible date-string
     *
     * The date-entity <var>$date</var> can be either a timestamp, a
     * DateTime Object, a string that is parseable by strtotime() or a Zend_Date
     * Object.
     *
     * @param    integer|string|DateTimt|Zend_Date        $date    The date-entity
     * @param    boolean                                    $asUtc    Whether to return the LDAP-compatible date-string
     *                                                          as UTC or as local value
     * @return    string
     * @throws    InvalidArgumentException
     */
    public static function toLdapDateTime($date, $asUtc = true)
    {
        if (!($date instanceof DateTime)) {
            if (is_int($date)) {
                $date = new DateTime('@' . $date);
                $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
            } else if (is_string($date)) {
                $date = new DateTime($date);
            } else if ($date instanceof Zend_Date) {
                $date = new DateTime($date->get(Zend_Date::ISO_8601));
            } else {
                throw new InvalidArgumentException('Parameter $date is not of the expected type');
            }
        }
        $timezone = $date->format('O');
        if (true === $asUtc) {
            $date->setTimezone(new DateTimeZone('UTC'));
            $timezone = 'Z';
        }
        if ( '+0000' === $timezone ) {
            $timezone = 'Z';
        }
        return $date->format('YmdHis') . $timezone;
    }

    /**
     * Convert a boolean value to an LDAP-compatible string
     *
     * This converts a boolean value of TRUE, an integer-value of 1 and a
     * case-insensitive string 'true' to an LDAP-compatible 'TRUE'. All other
     * other values are converted to an LDAP-compatible 'FALSE'.
     *
     * @param    boolean|integer|string        $value    The boolean value to encode
     * @return    string
     */
    public static function toLdapBoolean($value)
    {
        $return = 'FALSE';
        if (!is_scalar($value)) {
            return $return;
        }
        if (true === $value || 'true' === strtolower($value) || 1 === $value) {
            $return = 'TRUE';
        }
        return $return;
    }

    /**
     * Serialize any value for storage in LDAP
     *
     * @param    mixed        $value    The value to serialize
     * @return    string
     */
    public static function toLdapSerialize($value)
    {
        return serialize($value);
    }

    /**
     * Convert an LDAP-compatible value to a corresponding PHP-value.
     *
     * By setting the <var>$type</var>-parameter the conversion of a certain
     * type can be forced
     * .
     * @param    string    $value             The value to convert
     * @param    int        $ytpe              The conversion type to use
     * @param    boolean    $dateTimeAsUtc    Return DateTime values in UTC timezone
     * @return    mixed
     * @throws    Zend_Ldap_Converter_Exception
     */
    public static function fromLdap($value, $type = self::STANDARD, $dateTimeAsUtc = true)
    {
        switch ($type) {
            case self::BOOLEAN:
                return self::fromldapBoolean($value);
                break;
            case self::GENERALIZED_TIME:
                return self::fromLdapDateTime($value);
                break;
            default:
                if (is_numeric($value)) {
                    // prevent numeric values to be treated as date/time
                    return $value;
                } else if ('TRUE' === $value || 'FALSE' === $value) {
                    return self::fromLdapBoolean($value);
                }
                if (preg_match('/^\d{4}[\d\+\-Z\.]*$/', $value)) {
                    return self::fromLdapDateTime($value, $dateTimeAsUtc);
                }
                try {
                    return self::fromLdapUnserialize($value);
                } catch (UnexpectedValueException $e) { }
                break;
        }
        return $value;
    }

    /**
     * Convert an LDAP-Generalized-Time-entry into a DateTime-Object
     *
     * CAVEAT: The DateTime-Object returned will alwasy be set to UTC-Timezone.
     *
     * @param    string        $date    The generalized-Time
     * @param    boolean        $asUtc    Return the DateTime with UTC timezone
     * @return    DateTime
     * @throws    InvalidArgumentException if a non-parseable-format is given
     */
    public static function fromLdapDateTime($date, $asUtc = true)
    {
        $datepart = array ();
        if (!preg_match('/^(\d{4})/', $date, $datepart) ) {
            throw new InvalidArgumentException('Invalid date format found');
        }

        if ($datepart[1] < 4) {
            throw new InvalidArgumentException('Invalid date format found (too short)');
        }

        $time = array (
            // The year is mandatory!
            'year'   => $datepart[1],
            'month'  => 1,
            'day'    => 1,
            'hour'   => 0,
            'minute' => 0,
            'second' => 0,
            'offdir' => '+',
            'offsethours' => 0,
            'offsetminutes' => 0
        );

        $length = strlen($date);

        // Check for month.
        if ($length >= 6) {
            $month = substr($date, 4, 2);
            if ($month < 1 || $month > 12) {
                throw new InvalidArgumentException('Invalid date format found (invalid month)');
            }
            $time['month'] = $month;
        }

        // Check for day
        if ($length >= 8) {
            $day = substr($date, 6, 2);
            if ($day < 1 || $day > 31) {
                throw new InvalidArgumentException('Invalid date format found (invalid day)');
            }
            $time['day'] = $day;
        }

        // Check for Hour
        if ($length >= 10) {
            $hour = substr($date, 8, 2);
            if ($hour < 0 || $hour > 23) {
                throw new InvalidArgumentException('Invalid date format found (invalid hour)');
            }
            $time['hour'] = $hour;
        }

        // Check for minute
        if ($length >= 12) {
            $minute = substr($date, 10, 2);
            if ($minute < 0 || $minute > 59) {
                throw new InvalidArgumentException('Invalid date format found (invalid minute)');
            }
            $time['minute'] = $minute;
        }

        // Check for seconds
        if ($length >= 14) {
            $second = substr($date, 12, 2);
            if ($second < 0 || $second > 59) {
                throw new InvalidArgumentException('Invalid date format found (invalid second)');
            }
            $time['second'] = $second;
        }

        // Set Offset
        $offsetRegEx = '/([Z\-\+])(\d{2}\'?){0,1}(\d{2}\'?){0,1}$/';
        $off         = array ();
        if (preg_match($offsetRegEx, $date, $off)) {
            $offset = $off[1];
            if ($offset == '+' || $offset == '-') {
                $time['offdir'] = $offset;
                // we have an offset, so lets calculate it.
                if (isset($off[2])) {
                    $offsetHours = substr($off[2], 0, 2);
                    if ($offsetHours < 0 || $offsetHours > 12) {
                        throw new InvalidArgumentException('Invalid date format found (invalid offset hour)');
                    }
                    $time['offsethours'] = $offsetHours;
                }
                if (isset($off[3])) {
                    $offsetMinutes = substr($off[3], 0, 2);
                    if ($offsetMinutes < 0 || $offsetMinutes > 59) {
                        throw new InvalidArgumentException('Invalid date format found (invalid offset minute)');
                    }
                    $time['offsetminutes'] = $offsetMinutes;
                }
            }
        }

        // Raw-Data is present, so lets create a DateTime-Object from it.
        $offset = $time['offdir']
                . str_pad($time['offsethours'],2,'0',STR_PAD_LEFT)
                . str_pad($time['offsetminutes'],2,'0',STR_PAD_LEFT);
        $timestring = $time['year'] . '-'
                    . str_pad($time['month'], 2, '0', STR_PAD_LEFT) . '-'
                    . str_pad($time['day'], 2, '0', STR_PAD_LEFT) . ' '
                    . str_pad($time['hour'], 2, '0', STR_PAD_LEFT) . ':'
                    . str_pad($time['minute'], 2, '0', STR_PAD_LEFT) . ':'
                    . str_pad($time['second'], 2, '0', STR_PAD_LEFT)
                    . $time['offdir']
                    . str_pad($time['offsethours'], 2, '0', STR_PAD_LEFT)
                    . str_pad($time['offsetminutes'], 2, '0', STR_PAD_LEFT);
        $date = new DateTime($timestring);
        if ($asUtc) {
            $date->setTimezone(new DateTimeZone('UTC'));
        }
        return $date;
    }

    /**
     * Convert an LDAP-compatible boolean value into a PHP-compatible one
     *
     * @param    string        $value        The value to convert
     * @return    boolean
     * @throws    InvalidArgumentException
     */
    public static function fromLdapBoolean($value)
    {
        if ( 'TRUE' === $value ) {
            return true;
        } else if ( 'FALSE' === $value ) {
            return false;
        } else {
            throw new InvalidArgumentException('The given value is not a boolean value');
        }
    }

    /**
     * Unserialize a serialized value to return the corresponding object
     *
     * @param    string        $value    The value to convert
     * @return    mixed
     * @throws    UnexpectedValueException
     */
    public static function fromLdapUnserialize($value)
    {
        $v = @unserialize($value);
        if (false===$v && $value != 'b:0;') {
            throw new UnexpectedValueException('The given value could not be unserialized');
        }
        return $v;
    }
}
