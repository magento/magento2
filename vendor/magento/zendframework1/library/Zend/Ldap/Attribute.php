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
 * @see Zend_Ldap_Converter
 */
#require_once 'Zend/Ldap/Converter.php';

/**
 * Zend_Ldap_Attribute is a collection of LDAP attribute related functions.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Attribute
{
    const PASSWORD_HASH_MD5   = 'md5';
    const PASSWORD_HASH_SMD5  = 'smd5';
    const PASSWORD_HASH_SHA   = 'sha';
    const PASSWORD_HASH_SSHA  = 'ssha';
    const PASSWORD_UNICODEPWD = 'unicodePwd';

    /**
     * Sets a LDAP attribute.
     *
     * @param  array                    $data
     * @param  string                   $attribName
     * @param  scalar|array|Traversable $value
     * @param  boolean                  $append
     * @return void
     */
    public static function setAttribute(array &$data, $attribName, $value, $append = false)
    {
        $attribName = strtolower($attribName);
        $valArray = array();
        if (is_array($value) || ($value instanceof Traversable))
        {
            foreach ($value as $v)
            {
                $v = self::_valueToLdap($v);
                if ($v !== null) $valArray[] = $v;
            }
        }
        else if ($value !== null)
        {
            $value = self::_valueToLdap($value);
            if ($value !== null) $valArray[] = $value;
        }

        if ($append === true && isset($data[$attribName]))
        {
            if (is_string($data[$attribName])) $data[$attribName] = array($data[$attribName]);
            $data[$attribName] = array_merge($data[$attribName], $valArray);
        }
        else
        {
            $data[$attribName] = $valArray;
        }
    }

    /**
     * Gets a LDAP attribute.
     *
     * @param  array   $data
     * @param  string  $attribName
     * @param  integer $index
     * @return array|mixed
     */
    public static function getAttribute(array $data, $attribName, $index = null)
    {
        $attribName = strtolower($attribName);
        if ($index === null) {
            if (!isset($data[$attribName])) return array();
            $retArray = array();
            foreach ($data[$attribName] as $v)
            {
                $retArray[] = self::_valueFromLdap($v);
            }
            return $retArray;
        } else if (is_int($index)) {
            if (!isset($data[$attribName])) {
                return null;
            } else if ($index >= 0 && $index<count($data[$attribName])) {
                return self::_valueFromLdap($data[$attribName][$index]);
            } else {
                return null;
            }
        }
        return null;
    }

    /**
     * Checks if the given value(s) exist in the attribute
     *
     * @param array       $data
     * @param string      $attribName
     * @param mixed|array $value
     * @return boolean
     */
    public static function attributeHasValue(array &$data, $attribName, $value)
    {
        $attribName = strtolower($attribName);
        if (!isset($data[$attribName])) return false;

        if (is_scalar($value)) {
            $value = array($value);
        }

        foreach ($value as $v) {
            $v = self::_valueToLdap($v);
            if (!in_array($v, $data[$attribName], true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Removes duplicate values from a LDAP attribute
     *
     * @param array  $data
     * @param string $attribName
     * @return void
     */
    public static function removeDuplicatesFromAttribute(array &$data, $attribName)
    {
        $attribName = strtolower($attribName);
        if (!isset($data[$attribName])) return;
        $data[$attribName] = array_values(array_unique($data[$attribName]));
    }

    /**
     * Remove given values from a LDAP attribute
     *
     * @param array       $data
     * @param string      $attribName
     * @param mixed|array $value
     * @return void
     */
    public static function removeFromAttribute(array &$data, $attribName, $value)
    {
        $attribName = strtolower($attribName);
        if (!isset($data[$attribName])) return;

        if (is_scalar($value)) {
            $value = array($value);
        }

        $valArray = array();
        foreach ($value as $v)
        {
            $v = self::_valueToLdap($v);
            if ($v !== null) $valArray[] = $v;
        }

        $resultArray = $data[$attribName];
        foreach ($valArray as $rv) {
            $keys = array_keys($resultArray, $rv);
            foreach ($keys as $k) {
                unset($resultArray[$k]);
            }
        }
        $resultArray = array_values($resultArray);
        $data[$attribName] = $resultArray;
    }

    /**
     * @param  mixed $value
     * @return string|null
     */
    private static function _valueToLdap($value)
    {
        return Zend_Ldap_Converter::toLdap($value);
    }

    /**
     * @param  string $value
     * @return mixed
     */
    private static function _valueFromLdap($value)
    {
        try {
            $return = Zend_Ldap_Converter::fromLdap($value, Zend_Ldap_Converter::STANDARD, false);
            if ($return instanceof DateTime) {
                return Zend_Ldap_Converter::toLdapDateTime($return, false);
            } else {
                return $return;
            }
        } catch (InvalidArgumentException $e) {
            return $value;
        }
    }

    /**
     * Converts a PHP data type into its LDAP representation
     *
     * @deprected    use Zend_Ldap_Converter instead
     * @param          mixed $value
     * @return         string|null - null if the PHP data type cannot be converted.
     */
    public static function convertToLdapValue($value)
    {
        return self::_valueToLdap($value);
    }

    /**
     * Converts an LDAP value into its PHP data type
     *
     * @deprected    use Zend_Ldap_Converter instead
     * @param          string $value
     * @return         mixed
     */
    public static function convertFromLdapValue($value)
    {
        return self::_valueFromLdap($value);
    }

    /**
     * Converts a timestamp into its LDAP date/time representation
     *
     * @param  integer $value
     * @param  boolean $utc
     * @return string|null - null if the value cannot be converted.
     */
    public static function convertToLdapDateTimeValue($value, $utc = false)
    {
        return self::_valueToLdapDateTime($value, $utc);
    }

    /**
     * Converts LDAP date/time representation into a timestamp
     *
     * @param  string $value
     * @return integer|null - null if the value cannot be converted.
     */
    public static function convertFromLdapDateTimeValue($value)
    {
        return self::_valueFromLdapDateTime($value);
    }

    /**
     * Sets a LDAP password.
     *
     * @param  array       $data
     * @param  string      $password
     * @param  string      $hashType
     * @param  string|null $attribName
     * @return null
     */
    public static function setPassword(array &$data, $password, $hashType = self::PASSWORD_HASH_MD5,
        $attribName = null)
    {
        if ($attribName === null) {
            if ($hashType === self::PASSWORD_UNICODEPWD) {
                $attribName = 'unicodePwd';
            } else {
                $attribName = 'userPassword';
            }
        }

        $hash = self::createPassword($password, $hashType);
        self::setAttribute($data, $attribName, $hash, false);
    }

    /**
     * Creates a LDAP password.
     *
     * @param  string $password
     * @param  string $hashType
     * @return string
     */
    public static function createPassword($password, $hashType = self::PASSWORD_HASH_MD5)
    {
        switch ($hashType) {
            case self::PASSWORD_UNICODEPWD:
                /* see:
                 * http://msdn.microsoft.com/en-us/library/cc223248(PROT.10).aspx
                 */
                $password = '"' . $password . '"';
                if (function_exists('mb_convert_encoding')) {
                    $password = mb_convert_encoding($password, 'UTF-16LE', 'UTF-8');
                } else if (function_exists('iconv')) {
                    $password = iconv('UTF-8', 'UTF-16LE', $password);
                } else {
                    $len = strlen($password);
                    $new = '';
                    for($i=0; $i < $len; $i++) {
                        $new .= $password[$i] . "\x00";
                    }
                    $password = $new;
                }
                return $password;
            case self::PASSWORD_HASH_SSHA:
                $salt    = substr(sha1(uniqid(mt_rand(), true), true), 0, 4);
                $rawHash = sha1($password . $salt, true) . $salt;
                $method  = '{SSHA}';
                break;
            case self::PASSWORD_HASH_SHA:
                $rawHash = sha1($password, true);
                $method  = '{SHA}';
                break;
            case self::PASSWORD_HASH_SMD5:
                $salt    = substr(sha1(uniqid(mt_rand(), true), true), 0, 4);
                $rawHash = md5($password . $salt, true) . $salt;
                $method  = '{SMD5}';
                break;
            case self::PASSWORD_HASH_MD5:
            default:
                $rawHash = md5($password, true);
                $method  = '{MD5}';
                break;
        }
        return $method . base64_encode($rawHash);
    }

    /**
     * Sets a LDAP date/time attribute.
     *
     * @param  array                     $data
     * @param  string                    $attribName
     * @param  integer|array|Traversable $value
     * @param  boolean                   $utc
     * @param  boolean                   $append
     * @return null
     */
    public static function setDateTimeAttribute(array &$data, $attribName, $value, $utc = false,
        $append = false)
    {
        $convertedValues = array();
        if (is_array($value) || ($value instanceof Traversable))
        {
            foreach ($value as $v) {
                $v = self::_valueToLdapDateTime($v, $utc);
                if ($v !== null) $convertedValues[] = $v;
            }
        }
        else if ($value !== null) {
            $value = self::_valueToLdapDateTime($value, $utc);
            if ($value !== null) $convertedValues[] = $value;
        }
        self::setAttribute($data, $attribName, $convertedValues, $append);
    }

    /**
     * @param  integer $value
     * @param  boolean $utc
     * @return string|null
     */
    private static function _valueToLdapDateTime($value, $utc)
    {
        if (is_int($value)) {
            return Zend_Ldap_Converter::toLdapDateTime($value, $utc);
        }
        else return null;
    }

    /**
     * Gets a LDAP date/time attribute.
     *
     * @param  array   $data
     * @param  string  $attribName
     * @param  integer $index
     * @return array|integer
     */
    public static function getDateTimeAttribute(array $data, $attribName, $index = null)
    {
        $values = self::getAttribute($data, $attribName, $index);
        if (is_array($values)) {
            for ($i = 0; $i<count($values); $i++) {
                $newVal = self::_valueFromLdapDateTime($values[$i]);
                if ($newVal !== null) $values[$i] = $newVal;
            }
        }
        else {
            $newVal = self::_valueFromLdapDateTime($values);
            if ($newVal !== null) $values = $newVal;
        }
        return $values;
    }

    /**
     * @param  string|DateTime $value
     * @return integer|null
     */
    private static function _valueFromLdapDateTime($value)
    {
        if ($value instanceof DateTime) {
            return $value->format('U');
        } else if (is_string($value)) {
            try {
                return Zend_Ldap_Converter::fromLdapDateTime($value, false)->format('U');
            } catch (InvalidArgumentException $e) {
                return null;
            }
        } else return null;
    }
}
