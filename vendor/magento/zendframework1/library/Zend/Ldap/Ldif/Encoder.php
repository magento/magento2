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
 * @subpackage Ldif
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Ldap_Ldif_Encoder provides methods to encode and decode LDAP data into/from LDIF.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Ldif
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Ldif_Encoder
{
    /**
     * Additional options used during encoding
     *
     * @var array
     */
    protected $_options = array(
        'sort'    => true,
        'version' => 1,
        'wrap'    => 78
    );

    /**
     * @var boolean
     */
    protected $_versionWritten = false;

    /**
     * Constructor.
     *
     * @param  array $options Additional options used during encoding
     * @return void
     */
    protected function __construct(array $options = array())
    {
        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * Decodes the string $string into an array of LDIF items
     *
     * @param  string $string
     * @return array
     */
    public static function decode($string)
    {
        $encoder = new self(array());
        return $encoder->_decode($string);
    }

    /**
     * Decodes the string $string into an array of LDIF items
     *
     * @param  string $string
     * @return array
     */
    protected function _decode($string)
    {
        $items = array();
        $item = array();
        $last = null;
        foreach (explode("\n", $string) as $line) {
            $line = rtrim($line, "\x09\x0A\x0D\x00\x0B");
            $matches = array();
            if (substr($line, 0, 1) === ' ' && $last !== null) {
                $last[2] .= substr($line, 1);
            } else if (substr($line, 0, 1) === '#') {
                continue;
            } else if (preg_match('/^([a-z0-9;-]+)(:[:<]?\s*)([^:<]*)$/i', $line, $matches)) {
                $name = strtolower($matches[1]);
                $type = trim($matches[2]);
                $value = $matches[3];
                if ($last !== null) {
                    $this->_pushAttribute($last, $item);
                }
                if ($name === 'version') {
                    continue;
                } else if (count($item) > 0 && $name === 'dn') {
                    $items[] = $item;
                    $item = array();
                    $last = null;
                }
                $last = array($name, $type, $value);
            } else if (trim($line) === '') {
                continue;
            }
        }
        if ($last !== null) {
            $this->_pushAttribute($last, $item);
        }
        $items[] = $item;
        return (count($items)>1) ? $items : $items[0];
    }

    /**
     * Pushes a decoded attribute to the stack
     *
     * @param array $attribute
     * @param array $entry
     */
    protected function _pushAttribute(array $attribute, array &$entry)
    {
        $name = $attribute[0];
        $type = $attribute[1];
        $value = $attribute[2];
        if ($type === '::') {
            $value = base64_decode($value);
        }
        if ($name === 'dn') {
            $entry[$name] = $value;
        } else if (isset($entry[$name]) && $value !== '') {
            $entry[$name][] = $value;
        } else {
            $entry[$name] = ($value !== '') ? array($value) : array();
        }
    }

    /**
     * Encode $value into a LDIF representation
     *
     * @param  mixed $value   The value to be encoded
     * @param  array $options Additional options used during encoding
     * @return string The encoded value
     */
    public static function encode($value, array $options = array())
    {
        $encoder = new self($options);
        return $encoder->_encode($value);
    }

    /**
     * Recursive driver which determines the type of value to be encoded
     * and then dispatches to the appropriate method.
     *
     * @param  mixed $value The value to be encoded
     * @return string Encoded value
     */
    protected function _encode($value)
    {
        if (is_scalar($value)) {
            return $this->_encodeString($value);
        } else if (is_array($value)) {
            return $this->_encodeAttributes($value);
        } else if ($value instanceof Zend_Ldap_Node) {
            return $value->toLdif($this->_options);
        }
        return null;
    }

    /**
     * Encodes $string according to RFC2849
     *
     * @link http://www.faqs.org/rfcs/rfc2849.html
     *
     * @param  string $string
     * @param  boolen $base64
     * @return string
     */
    protected function _encodeString($string, &$base64 = null)
    {
        $string = (string)$string;
        if (!is_numeric($string) && empty($string)) {
            return '';
        }

        /*
         * SAFE-INIT-CHAR = %x01-09 / %x0B-0C / %x0E-1F /
         *                  %x21-39 / %x3B / %x3D-7F
         *                ; any value <= 127 except NUL, LF, CR,
         *                ; SPACE, colon (":", ASCII 58 decimal)
         *                ; and less-than ("<" , ASCII 60 decimal)
         *
         */
        $unsafe_init_char = array(0, 10, 13, 32, 58, 60);
        /*
         * SAFE-CHAR      = %x01-09 / %x0B-0C / %x0E-7F
         *                ; any value <= 127 decimal except NUL, LF,
         *                ; and CR
         */
        $unsafe_char      = array(0, 10, 13);

        $base64 = false;
        for ($i = 0; $i < strlen($string); $i++) {
            $char = ord(substr($string, $i, 1));
            if ($char >= 127) {
                $base64 = true;
                break;
            } else if ($i === 0 && in_array($char, $unsafe_init_char)) {
                $base64 = true;
                break;
            } else if (in_array($char, $unsafe_char)) {
                $base64 = true;
                break;
            }
        }
        // Test for ending space
        if (substr($string, -1) == ' ') {
            $base64 = true;
        }

        if ($base64 === true) {
            $string = base64_encode($string);
        }

        return $string;
    }

    /**
     * Encodes an attribute with $name and $value according to RFC2849
     *
     * @link http://www.faqs.org/rfcs/rfc2849.html
     *
     * @param  string       $name
     * @param  array|string $value
     * @return string
     */
    protected function _encodeAttribute($name, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        $output = '';

        if (count($value) < 1) {
            return $name . ': ';
        }

        foreach ($value as $v) {
            $base64 = null;
            $v = $this->_encodeString($v, $base64);
            $attribute = $name . ':';
            if ($base64 === true) {
                $attribute .= ': ' . $v;
            } else {
                $attribute .= ' ' . $v;
            }
            if (isset($this->_options['wrap']) && strlen($attribute) > $this->_options['wrap']) {
                $attribute = trim(chunk_split($attribute, $this->_options['wrap'], PHP_EOL . ' '));
            }
            $output .= $attribute . PHP_EOL;
        }
        return trim($output, PHP_EOL);
    }

    /**
     * Encodes a collection of attributes according to RFC2849
     *
     * @link http://www.faqs.org/rfcs/rfc2849.html
     *
     * @param  array $attributes
     * @return string
     */
    protected function _encodeAttributes(array $attributes)
    {
        $string = '';
        $attributes = array_change_key_case($attributes, CASE_LOWER);
        if (!$this->_versionWritten && array_key_exists('dn', $attributes) && isset($this->_options['version'])
                && array_key_exists('objectclass', $attributes)) {
            $string .= sprintf('version: %d', $this->_options['version']) . PHP_EOL;
            $this->_versionWritten = true;
        }

        if (isset($this->_options['sort']) && $this->_options['sort'] === true) {
            ksort($attributes, SORT_STRING);
            if (array_key_exists('objectclass', $attributes)) {
                $oc = $attributes['objectclass'];
                unset($attributes['objectclass']);
                $attributes = array_merge(array('objectclass' => $oc), $attributes);
            }
            if (array_key_exists('dn', $attributes)) {
                $dn = $attributes['dn'];
                unset($attributes['dn']);
                $attributes = array_merge(array('dn' => $dn), $attributes);
            }
        }
        foreach ($attributes as $key => $value) {
            $string .= $this->_encodeAttribute($key, $value) . PHP_EOL;
        }
        return trim($string, PHP_EOL);
    }
}
