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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Dn.php 22662 2010-07-24 17:37:36Z mabe $
 */

/**
 * Zend_Ldap_Dn provides an API for DN manipulation
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Dn implements ArrayAccess
{
    const ATTR_CASEFOLD_NONE  = 'none';
    const ATTR_CASEFOLD_UPPER = 'upper';
    const ATTR_CASEFOLD_LOWER = 'lower';

    /**
     * The default case fold to use
     *
     * @var string
     */
    protected static $_defaultCaseFold = self::ATTR_CASEFOLD_NONE;

    /**
     * The case fold used for this instance
     *
     * @var string
     */
    protected $_caseFold;

    /**
     * The DN data
     *
     * @var array
     */
    protected $_dn;

    /**
     * Creates a DN from an array or a string
     *
     * @param  string|array $dn
     * @param  string|null  $caseFold
     * @return Zend_Ldap_Dn
     * @throws Zend_Ldap_Exception
     */
    public static function factory($dn, $caseFold = null)
    {
        if (is_array($dn)) {
            return self::fromArray($dn, $caseFold);
        } else if (is_string($dn)) {
            return self::fromString($dn, $caseFold);
        } else {
            /**
             * Zend_Ldap_Exception
             */
            #require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception(null, 'Invalid argument type for $dn');
        }
    }

    /**
     * Creates a DN from a string
     *
     * @param  string      $dn
     * @param  string|null $caseFold
     * @return Zend_Ldap_Dn
     * @throws Zend_Ldap_Exception
     */
    public static function fromString($dn, $caseFold = null)
    {
        $dn = trim($dn);
        if (empty($dn)) {
            $dnArray = array();
        } else {
            $dnArray = self::explodeDn((string)$dn);
        }
        return new self($dnArray, $caseFold);
    }

    /**
     * Creates a DN from an array
     *
     * @param  array       $dn
     * @param  string|null $caseFold
     * @return Zend_Ldap_Dn
     * @throws Zend_Ldap_Exception
     */
    public static function fromArray(array $dn, $caseFold = null)
    {
         return new self($dn, $caseFold);
    }

    /**
     * Constructor
     *
     * @param array       $dn
     * @param string|null $caseFold
     */
    protected function __construct(array $dn, $caseFold)
    {
        $this->_dn = $dn;
        $this->setCaseFold($caseFold);
    }

    /**
     * Gets the RDN of the current DN
     *
     * @param  string $caseFold
     * @return array
     * @throws Zend_Ldap_Exception if DN has no RDN (empty array)
     */
    public function getRdn($caseFold = null)
    {
        $caseFold = self::_sanitizeCaseFold($caseFold, $this->_caseFold);
        return self::_caseFoldRdn($this->get(0, 1, $caseFold), null);
    }

    /**
     * Gets the RDN of the current DN as a string
     *
     * @param  string $caseFold
     * @return string
     * @throws Zend_Ldap_Exception if DN has no RDN (empty array)
     */
    public function getRdnString($caseFold = null)
    {
        $caseFold = self::_sanitizeCaseFold($caseFold, $this->_caseFold);
        return self::implodeRdn($this->getRdn(), $caseFold);
    }

    /**
     * Get the parent DN $levelUp levels up the tree
     *
     * @param  int $levelUp
     * @return Zend_Ldap_Dn
     */
    public function getParentDn($levelUp = 1)
    {
        $levelUp = (int)$levelUp;
        if ($levelUp < 1 || $levelUp >= count($this->_dn)) {
            /**
             * Zend_Ldap_Exception
             */
            #require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception(null, 'Cannot retrieve parent DN with given $levelUp');
        }
        $newDn = array_slice($this->_dn, $levelUp);
        return new self($newDn, $this->_caseFold);
    }

    /**
     * Get a DN part
     *
     * @param  int    $index
     * @param  int    $length
     * @param  string $caseFold
     * @return array
     * @throws Zend_Ldap_Exception if index is illegal
     */
    public function get($index, $length = 1, $caseFold = null)
    {
        $caseFold = self::_sanitizeCaseFold($caseFold, $this->_caseFold);
        $this->_assertIndex($index);
        $length = (int)$length;
        if ($length <= 0) {
            $length = 1;
        }
        if ($length === 1) {
            return self::_caseFoldRdn($this->_dn[$index], $caseFold);
        }
        else {
            return self::_caseFoldDn(array_slice($this->_dn, $index, $length, false), $caseFold);
        }
    }

    /**
     * Set a DN part
     *
     * @param  int   $index
     * @param  array $value
     * @return Zend_Ldap_Dn Provides a fluent interface
     * @throws Zend_Ldap_Exception if index is illegal
     */
    public function set($index, array $value)
    {
        $this->_assertIndex($index);
        self::_assertRdn($value);
        $this->_dn[$index] = $value;
        return $this;
    }

    /**
     * Remove a DN part
     *
     * @param  int $index
     * @param  int $length
     * @return Zend_Ldap_Dn Provides a fluent interface
     * @throws Zend_Ldap_Exception if index is illegal
     */
    public function remove($index, $length = 1)
    {
        $this->_assertIndex($index);
        $length = (int)$length;
        if ($length <= 0) {
            $length = 1;
        }
        array_splice($this->_dn, $index, $length, null);
        return $this;
    }

    /**
     * Append a DN part
     *
     * @param  array $value
     * @return Zend_Ldap_Dn Provides a fluent interface
     */
    public function append(array $value)
    {
        self::_assertRdn($value);
        $this->_dn[] = $value;
        return $this;
    }

    /**
     * Prepend a DN part
     *
     * @param  array $value
     * @return Zend_Ldap_Dn Provides a fluent interface
     */
    public function prepend(array $value)
    {
        self::_assertRdn($value);
        array_unshift($this->_dn, $value);
        return $this;
    }

    /**
     * Insert a DN part
     *
     * @param  int   $index
     * @param  array $value
     * @return Zend_Ldap_Dn Provides a fluent interface
     * @throws Zend_Ldap_Exception if index is illegal
     */
    public function insert($index, array $value)
    {
        $this->_assertIndex($index);
        self::_assertRdn($value);
        $first = array_slice($this->_dn, 0, $index + 1);
        $second = array_slice($this->_dn, $index + 1);
        $this->_dn = array_merge($first, array($value), $second);
        return $this;
    }

    /**
     * Assert index is correct and usable
     *
     * @param  mixed $index
     * @return boolean
     * @throws Zend_Ldap_Exception
     */
    protected function _assertIndex($index)
    {
        if (!is_int($index)) {
            /**
             * Zend_Ldap_Exception
             */
            #require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception(null, 'Parameter $index must be an integer');
        }
        if ($index < 0 || $index >= count($this->_dn)) {
            /**
             * Zend_Ldap_Exception
             */
            #require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception(null, 'Parameter $index out of bounds');
        }
        return true;
    }

    /**
     * Assert if value is in a correct RDN format
     *
     * @param  array $value
     * @return boolean
     * @throws Zend_Ldap_Exception
     */
    protected static function _assertRdn(array $value)
    {
        if (count($value)<1) {
            /**
             * Zend_Ldap_Exception
             */
            #require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception(null, 'RDN Array is malformed: it must have at least one item');
        }

        foreach (array_keys($value) as $key) {
            if (!is_string($key)) {
                /**
                 * Zend_Ldap_Exception
                 */
                #require_once 'Zend/Ldap/Exception.php';
                throw new Zend_Ldap_Exception(null, 'RDN Array is malformed: it must use string keys');
            }
        }
    }

    /**
     * Sets the case fold
     *
     * @param string|null $caseFold
     */
    public function setCaseFold($caseFold)
    {
        $this->_caseFold = self::_sanitizeCaseFold($caseFold, self::$_defaultCaseFold);
    }

    /**
     * Return DN as a string
     *
     * @param  string $caseFold
     * @return string
     * @throws Zend_Ldap_Exception
     */
    public function toString($caseFold = null)
    {
        $caseFold = self::_sanitizeCaseFold($caseFold, $this->_caseFold);
        return self::implodeDn($this->_dn, $caseFold);
    }

    /**
     * Return DN as an array
     *
     * @param  string $caseFold
     * @return array
     */
    public function toArray($caseFold = null)
    {
        $caseFold = self::_sanitizeCaseFold($caseFold, $this->_caseFold);

        if ($caseFold === self::ATTR_CASEFOLD_NONE) {
            return $this->_dn;
        } else {
            return self::_caseFoldDn($this->_dn, $caseFold);
        }
    }

    /**
     * Do a case folding on a RDN
     *
     * @param  array  $part
     * @param  string $caseFold
     * @return array
     */
    protected static function _caseFoldRdn(array $part, $caseFold)
    {
        switch ($caseFold) {
            case self::ATTR_CASEFOLD_UPPER:
                return array_change_key_case($part, CASE_UPPER);
            case self::ATTR_CASEFOLD_LOWER:
                return array_change_key_case($part, CASE_LOWER);
            case self::ATTR_CASEFOLD_NONE:
            default:
                return $part;
        }
    }

    /**
     * Do a case folding on a DN ort part of it
     *
     * @param  array  $dn
     * @param  string $caseFold
     * @return array
     */
    protected static function _caseFoldDn(array $dn, $caseFold)
    {
        $return = array();
        foreach ($dn as $part) {
            $return[] = self::_caseFoldRdn($part, $caseFold);
        }
        return $return;
    }

    /**
     * Cast to string representation {@see toString()}
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Required by the ArrayAccess implementation
     *
     * @param  int $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $offset = (int)$offset;
        if ($offset < 0 || $offset >= count($this->_dn)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Proxy to {@see get()}
     * Required by the ArrayAccess implementation
     *
     * @param  int $offset
     * @return array
     */
     public function offsetGet($offset)
     {
         return $this->get($offset, 1, null);
     }

     /**
      * Proxy to {@see set()}
      * Required by the ArrayAccess implementation
      *
      * @param int   $offset
      * @param array $value
      */
     public function offsetSet($offset, $value)
     {
         $this->set($offset, $value);
     }

     /**
      * Proxy to {@see remove()}
      * Required by the ArrayAccess implementation
      *
      * @param int $offset
      */
     public function offsetUnset($offset)
     {
         $this->remove($offset, 1);
     }

    /**
     * Sets the default case fold
     *
     * @param string $caseFold
     */
    public static function setDefaultCaseFold($caseFold)
    {
        self::$_defaultCaseFold = self::_sanitizeCaseFold($caseFold, self::ATTR_CASEFOLD_NONE);
    }

    /**
     * Sanitizes the case fold
     *
     * @param  string $caseFold
     * @return string
     */
    protected static function _sanitizeCaseFold($caseFold, $default)
    {
        switch ($caseFold) {
            case self::ATTR_CASEFOLD_NONE:
            case self::ATTR_CASEFOLD_UPPER:
            case self::ATTR_CASEFOLD_LOWER:
                return $caseFold;
                break;
            default:
                return $default;
                break;
        }
    }

    /**
     * Escapes a DN value according to RFC 2253
     *
     * Escapes the given VALUES according to RFC 2253 so that they can be safely used in LDAP DNs.
     * The characters ",", "+", """, "\", "<", ">", ";", "#", " = " with a special meaning in RFC 2252
     * are preceeded by ba backslash. Control characters with an ASCII code < 32 are represented as \hexpair.
     * Finally all leading and trailing spaces are converted to sequences of \20.
     * @see Net_LDAP2_Util::escape_dn_value() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values An array containing the DN values that should be escaped
     * @return array The array $values, but escaped
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
            $val = str_replace(array('\\', ',', '+', '"', '<', '>', ';', '#', '=', ),
                array('\\\\', '\,', '\+', '\"', '\<', '\>', '\;', '\#', '\='), $val);
            $val = Zend_Ldap_Converter::ascToHex32($val);

            // Convert all leading and trailing spaces to sequences of \20.
            if (preg_match('/^(\s*)(.+?)(\s*)$/', $val, $matches)) {
                $val = $matches[2];
                for ($i = 0; $i<strlen($matches[1]); $i++) {
                    $val = '\20' . $val;
                }
                for ($i = 0; $i<strlen($matches[3]); $i++) {
                    $val = $val . '\20';
                }
            }
            if (null === $val) $val = '\0';  // apply escaped "null" if string is empty
            $values[$key] = $val;
        }
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * Undoes the conversion done by {@link escapeValue()}.
     *
     * Any escape sequence starting with a baskslash - hexpair or special character -
     * will be transformed back to the corresponding character.
     * @see Net_LDAP2_Util::escape_dn_value() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of DN Values
     * @return array Same as $values, but unescaped
     */
    public static function unescapeValue($values = array())
    {
        /**
         * @see Zend_Ldap_Converter
         */
        #require_once 'Zend/Ldap/Converter.php';

        if (!is_array($values)) $values = array($values);
        foreach ($values as $key => $val) {
            // strip slashes from special chars
            $val = str_replace(array('\\\\', '\,', '\+', '\"', '\<', '\>', '\;', '\#', '\='),
                array('\\', ',', '+', '"', '<', '>', ';', '#', '=', ), $val);
            $values[$key] = Zend_Ldap_Converter::hex32ToAsc($val);
        }
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * Creates an array containing all parts of the given DN.
     *
     * Array will be of type
     * array(
     *      array("cn" => "name1", "uid" => "user"),
     *      array("cn" => "name2"),
     *      array("dc" => "example"),
     *      array("dc" => "org")
     * )
     * for a DN of cn=name1+uid=user,cn=name2,dc=example,dc=org.
     *
     * @param  string $dn
     * @param  array  $keys     An optional array to receive DN keys (e.g. CN, OU, DC, ...)
     * @param  array  $vals     An optional array to receive DN values
     * @param  string $caseFold
     * @return array
     * @throws Zend_Ldap_Exception
     */
    public static function explodeDn($dn, array &$keys = null, array &$vals = null,
        $caseFold = self::ATTR_CASEFOLD_NONE)
    {
        $k = array();
        $v = array();
        if (!self::checkDn($dn, $k, $v, $caseFold)) {
            /**
             * Zend_Ldap_Exception
             */
            #require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception(null, 'DN is malformed');
        }
        $ret = array();
        for ($i = 0; $i < count($k); $i++) {
            if (is_array($k[$i]) && is_array($v[$i]) && (count($k[$i]) === count($v[$i]))) {
                $multi = array();
                for ($j = 0; $j < count($k[$i]); $j++) {
                    $key=$k[$i][$j];
                    $val=$v[$i][$j];
                    $multi[$key] = $val;
                }
                $ret[] = $multi;
            } else if (is_string($k[$i]) && is_string($v[$i])) {
                $ret[] = array($k[$i] => $v[$i]);
            }
        }
        if ($keys !== null) $keys = $k;
        if ($vals !== null) $vals = $v;
        return $ret;
    }

    /**
     * @param  string $dn       The DN to parse
     * @param  array  $keys     An optional array to receive DN keys (e.g. CN, OU, DC, ...)
     * @param  array  $vals     An optional array to receive DN values
     * @param  string $caseFold
     * @return boolean True if the DN was successfully parsed or false if the string is not a valid DN.
     */
    public static function checkDn($dn, array &$keys = null, array &$vals = null,
        $caseFold = self::ATTR_CASEFOLD_NONE)
    {
        /* This is a classic state machine parser. Each iteration of the
         * loop processes one character. State 1 collects the key. When equals ( = )
         * is encountered the state changes to 2 where the value is collected
         * until a comma (,) or semicolon (;) is encountered after which we switch back
         * to state 1. If a backslash (\) is encountered, state 3 is used to collect the
         * following character without engaging the logic of other states.
         */
        $key = null;
        $value = null;
        $slen = strlen($dn);
        $state = 1;
        $ko = $vo = 0;
        $multi = false;
        $ka = array();
        $va = array();
        for ($di = 0; $di <= $slen; $di++) {
            $ch = ($di == $slen) ? 0 : $dn[$di];
            switch ($state) {
                case 1: // collect key
                    if ($ch === '=') {
                        $key = trim(substr($dn, $ko, $di - $ko));
                        if ($caseFold == self::ATTR_CASEFOLD_LOWER) $key = strtolower($key);
                        else if ($caseFold == self::ATTR_CASEFOLD_UPPER) $key = strtoupper($key);
                        if (is_array($multi)) {
                            $keyId = strtolower($key);
                            if (in_array($keyId, $multi)) {
                                return false;
                            }
                            $ka[count($ka)-1][] = $key;
                            $multi[] = $keyId;
                        } else {
                            $ka[] = $key;
                        }
                        $state = 2;
                        $vo = $di + 1;
                    } else if ($ch === ',' || $ch === ';' || $ch === '+') {
                        return false;
                    }
                    break;
                case 2: // collect value
                    if ($ch === '\\') {
                        $state = 3;
                    } else if ($ch === ',' || $ch === ';' || $ch === 0 || $ch === '+') {
                        $value = self::unescapeValue(trim(substr($dn, $vo, $di - $vo)));
                        if (is_array($multi)) {
                            $va[count($va)-1][] = $value;
                        } else {
                            $va[] = $value;
                        }
                        $state = 1;
                        $ko = $di + 1;
                        if ($ch === '+' && $multi === false) {
                            $lastKey = array_pop($ka);
                            $lastVal = array_pop($va);
                            $ka[] = array($lastKey);
                            $va[] = array($lastVal);
                            $multi = array(strtolower($lastKey));
                        } else if ($ch === ','|| $ch === ';' || $ch === 0) {
                            $multi = false;
                        }
                    } else if ($ch === '=') {
                        return false;
                    }
                    break;
                case 3: // escaped
                    $state = 2;
                    break;
            }
        }

        if ($keys !== null) {
            $keys = $ka;
        }
        if ($vals !== null) {
            $vals = $va;
        }

        return ($state === 1 && $ko > 0);
    }

    /**
     * Returns a DN part in the form $attribute = $value
     *
     * This method supports the creation of multi-valued RDNs
     * $part must contain an even number of elemets.
     *
     * @param  array  $attribute
     * @param  string $caseFold
     * @return string
     * @throws Zend_Ldap_Exception
     */
    public static function implodeRdn(array $part, $caseFold = null)
    {
        self::_assertRdn($part);
        $part = self::_caseFoldRdn($part, $caseFold);
        $rdnParts = array();
        foreach ($part as $key => $value) {
            $value = self::escapeValue($value);
            $keyId = strtolower($key);
            $rdnParts[$keyId] =  implode('=', array($key, $value));
        }
        ksort($rdnParts, SORT_STRING);
        return implode('+', $rdnParts);
    }

    /**
     * Implodes an array in the form delivered by {@link explodeDn()}
     * to a DN string.
     *
     * $dnArray must be of type
     * array(
     *      array("cn" => "name1", "uid" => "user"),
     *      array("cn" => "name2"),
     *      array("dc" => "example"),
     *      array("dc" => "org")
     * )
     *
     * @param  array  $dnArray
     * @param  string $caseFold
     * @param  string $separator
     * @return string
     * @throws Zend_Ldap_Exception
     */
    public static function implodeDn(array $dnArray, $caseFold = null, $separator = ',')
    {
        $parts = array();
        foreach ($dnArray as $p) {
            $parts[] = self::implodeRdn($p, $caseFold);
        }
        return implode($separator, $parts);
    }

    /**
     * Checks if given $childDn is beneath $parentDn subtree.
     *
     * @param  string|Zend_Ldap_Dn $childDn
     * @param  string|Zend_Ldap_Dn $parentDn
     * @return boolean
     */
    public static function isChildOf($childDn, $parentDn)
    {
        try {
            $keys = array();
            $vals = array();
            if ($childDn instanceof Zend_Ldap_Dn) {
                $cdn = $childDn->toArray(Zend_Ldap_Dn::ATTR_CASEFOLD_LOWER);
            } else {
                $cdn = self::explodeDn($childDn, $keys, $vals, Zend_Ldap_Dn::ATTR_CASEFOLD_LOWER);
            }
            if ($parentDn instanceof Zend_Ldap_Dn) {
                $pdn = $parentDn->toArray(Zend_Ldap_Dn::ATTR_CASEFOLD_LOWER);
            } else {
                $pdn = self::explodeDn($parentDn, $keys, $vals, Zend_Ldap_Dn::ATTR_CASEFOLD_LOWER);
            }
        }
        catch (Zend_Ldap_Exception $e) {
            return false;
        }

        $startIndex = count($cdn)-count($pdn);
        if ($startIndex<0) return false;
        for ($i = 0; $i<count($pdn); $i++) {
            if ($cdn[$i+$startIndex] != $pdn[$i]) return false;
        }
        return true;
    }
}