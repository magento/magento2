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
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_Ldap_Filter_Abstract provides a base implementation for filters.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Ldap_Filter_Abstract
{
    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    abstract public function toString();

    /**
     * Returns a string representation of the filter.
     * @see toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Negates the filter.
     *
     * @return Zend_Ldap_Filter_Abstract
     */
    public function negate()
    {
        /**
         * Zend_Ldap_Filter_Not
         */
        #require_once 'Zend/Ldap/Filter/Not.php';
        return new Zend_Ldap_Filter_Not($this);
    }

    /**
     * Creates an 'and' filter.
     *
     * @param  Zend_Ldap_Filter_Abstract $filter,...
     * @return Zend_Ldap_Filter_And
     */
    public function addAnd($filter)
    {
        /**
         * Zend_Ldap_Filter_And
         */
        #require_once 'Zend/Ldap/Filter/And.php';
        $fa = func_get_args();
        $args = array_merge(array($this), $fa);
        return new Zend_Ldap_Filter_And($args);
    }

    /**
     * Creates an 'or' filter.
     *
     * @param  Zend_Ldap_Filter_Abstract $filter,...
     * @return Zend_Ldap_Filter_Or
     */
    public function addOr($filter)
    {
        /**
         * Zend_Ldap_Filter_Or
         */
        #require_once 'Zend/Ldap/Filter/Or.php';
        $fa = func_get_args();
        $args = array_merge(array($this), $fa);
        return new Zend_Ldap_Filter_Or($args);
    }

    /**
     * Escapes the given VALUES according to RFC 2254 so that they can be safely used in LDAP filters.
     *
     * Any control characters with an ACII code < 32 as well as the characters with special meaning in
     * LDAP filters "*", "(", ")", and "\" (the backslash) are converted into the representation of a
     * backslash followed by two hex digits representing the hexadecimal value of the character.
     * @see Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of values to escape
     * @return array Array $values, but escaped
     */
    public static function escapeValue($values = array())
    {
        /**
         * @see Zend_Ldap_Converter
         */
        #require_once 'Zend/Ldap/Converter.php';

        if (!is_array($values)) $values = array($values);
        foreach ($values as $key => $val) {
            // Escaping of filter meta characters
            $val = str_replace(array('\\', '*', '(', ')'), array('\5c', '\2a', '\28', '\29'), $val);
            // ASCII < 32 escaping
            $val = Zend_Ldap_Converter::ascToHex32($val);
            if (null === $val) $val = '\0';  // apply escaped "null" if string is empty
            $values[$key] = $val;
        }
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * Undoes the conversion done by {@link escapeValue()}.
     *
     * Converts any sequences of a backslash followed by two hex digits into the corresponding character.
     * @see Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of values to escape
     * @return array Array $values, but unescaped
     */
    public static function unescapeValue($values = array())
    {
        /**
         * @see Zend_Ldap_Converter
         */
        #require_once 'Zend/Ldap/Converter.php';

        if (!is_array($values)) $values = array($values);
        foreach ($values as $key => $value) {
            // Translate hex code into ascii
            $values[$key] = Zend_Ldap_Converter::hex32ToAsc($value);
        }
        return (count($values) == 1) ? $values[0] : $values;
    }
}