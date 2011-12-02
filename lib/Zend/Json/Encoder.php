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
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Encoder.php 22452 2010-06-18 18:13:23Z ralph $
 */

/**
 * Encode PHP constructs to JSON
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Encoder
{
    /**
     * Whether or not to check for possible cycling
     *
     * @var boolean
     */
    protected $_cycleCheck;

    /**
     * Additional options used during encoding
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Array of visited objects; used to prevent cycling.
     *
     * @var array
     */
    protected $_visited = array();

    /**
     * Constructor
     *
     * @param boolean $cycleCheck Whether or not to check for recursion when encoding
     * @param array $options Additional options used during encoding
     * @return void
     */
    protected function __construct($cycleCheck = false, $options = array())
    {
        $this->_cycleCheck = $cycleCheck;
        $this->_options = $options;
    }

    /**
     * Use the JSON encoding scheme for the value specified
     *
     * @param mixed $value The value to be encoded
     * @param boolean $cycleCheck Whether or not to check for possible object recursion when encoding
     * @param array $options Additional options used during encoding
     * @return string  The encoded value
     */
    public static function encode($value, $cycleCheck = false, $options = array())
    {
        $encoder = new self(($cycleCheck) ? true : false, $options);

        return $encoder->_encodeValue($value);
    }

    /**
     * Recursive driver which determines the type of value to be encoded
     * and then dispatches to the appropriate method. $values are either
     *    - objects (returns from {@link _encodeObject()})
     *    - arrays (returns from {@link _encodeArray()})
     *    - basic datums (e.g. numbers or strings) (returns from {@link _encodeDatum()})
     *
     * @param $value mixed The value to be encoded
     * @return string Encoded value
     */
    protected function _encodeValue(&$value)
    {
        if (is_object($value)) {
            return $this->_encodeObject($value);
        } else if (is_array($value)) {
            return $this->_encodeArray($value);
        }

        return $this->_encodeDatum($value);
    }



    /**
     * Encode an object to JSON by encoding each of the public properties
     *
     * A special property is added to the JSON object called '__className'
     * that contains the name of the class of $value. This is used to decode
     * the object on the client into a specific class.
     *
     * @param $value object
     * @return string
     * @throws Zend_Json_Exception If recursive checks are enabled and the object has been serialized previously
     */
    protected function _encodeObject(&$value)
    {
        if ($this->_cycleCheck) {
            if ($this->_wasVisited($value)) {

                if (isset($this->_options['silenceCyclicalExceptions'])
                    && $this->_options['silenceCyclicalExceptions']===true) {

                    return '"* RECURSION (' . get_class($value) . ') *"';

                } else {
                    #require_once 'Zend/Json/Exception.php';
                    throw new Zend_Json_Exception(
                        'Cycles not supported in JSON encoding, cycle introduced by '
                        . 'class "' . get_class($value) . '"'
                    );
                }
            }

            $this->_visited[] = $value;
        }

        $props = '';

        if ($value instanceof Iterator) {
            $propCollection = $value;
        } else {
            $propCollection = get_object_vars($value);
        }

        foreach ($propCollection as $name => $propValue) {
            if (isset($propValue)) {
                $props .= ','
                        . $this->_encodeString($name)
                        . ':'
                        . $this->_encodeValue($propValue);
            }
        }

        return '{"__className":"' . get_class($value) . '"'
                . $props . '}';
    }


    /**
     * Determine if an object has been serialized already
     *
     * @param mixed $value
     * @return boolean
     */
    protected function _wasVisited(&$value)
    {
        if (in_array($value, $this->_visited, true)) {
            return true;
        }

        return false;
    }


    /**
     * JSON encode an array value
     *
     * Recursively encodes each value of an array and returns a JSON encoded
     * array string.
     *
     * Arrays are defined as integer-indexed arrays starting at index 0, where
     * the last index is (count($array) -1); any deviation from that is
     * considered an associative array, and will be encoded as such.
     *
     * @param $array array
     * @return string
     */
    protected function _encodeArray(&$array)
    {
        $tmpArray = array();

        // Check for associative array
        if (!empty($array) && (array_keys($array) !== range(0, count($array) - 1))) {
            // Associative array
            $result = '{';
            foreach ($array as $key => $value) {
                $key = (string) $key;
                $tmpArray[] = $this->_encodeString($key)
                            . ':'
                            . $this->_encodeValue($value);
            }
            $result .= implode(',', $tmpArray);
            $result .= '}';
        } else {
            // Indexed array
            $result = '[';
            $length = count($array);
            for ($i = 0; $i < $length; $i++) {
                $tmpArray[] = $this->_encodeValue($array[$i]);
            }
            $result .= implode(',', $tmpArray);
            $result .= ']';
        }

        return $result;
    }


    /**
     * JSON encode a basic data type (string, number, boolean, null)
     *
     * If value type is not a string, number, boolean, or null, the string
     * 'null' is returned.
     *
     * @param $value mixed
     * @return string
     */
    protected function _encodeDatum(&$value)
    {
        $result = 'null';

        if (is_int($value) || is_float($value)) {
            $result = (string) $value;
            $result = str_replace(",", ".", $result);
        } elseif (is_string($value)) {
            $result = $this->_encodeString($value);
        } elseif (is_bool($value)) {
            $result = $value ? 'true' : 'false';
        }

        return $result;
    }


    /**
     * JSON encode a string value by escaping characters as necessary
     *
     * @param $value string
     * @return string
     */
    protected function _encodeString(&$string)
    {
        // Escape these characters with a backslash:
        // " \ / \n \r \t \b \f
        $search  = array('\\', "\n", "\t", "\r", "\b", "\f", '"', '/');
        $replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\"', '\\/');
        $string  = str_replace($search, $replace, $string);

        // Escape certain ASCII characters:
        // 0x08 => \b
        // 0x0c => \f
        $string = str_replace(array(chr(0x08), chr(0x0C)), array('\b', '\f'), $string);
        $string = self::encodeUnicodeString($string);

        return '"' . $string . '"';
    }


    /**
     * Encode the constants associated with the ReflectionClass
     * parameter. The encoding format is based on the class2 format
     *
     * @param $cls ReflectionClass
     * @return string Encoded constant block in class2 format
     */
    private static function _encodeConstants(ReflectionClass $cls)
    {
        $result    = "constants : {";
        $constants = $cls->getConstants();

        $tmpArray = array();
        if (!empty($constants)) {
            foreach ($constants as $key => $value) {
                $tmpArray[] = "$key: " . self::encode($value);
            }

            $result .= implode(', ', $tmpArray);
        }

        return $result . "}";
    }


    /**
     * Encode the public methods of the ReflectionClass in the
     * class2 format
     *
     * @param $cls ReflectionClass
     * @return string Encoded method fragment
     *
     */
    private static function _encodeMethods(ReflectionClass $cls)
    {
        $methods = $cls->getMethods();
        $result = 'methods:{';

        $started = false;
        foreach ($methods as $method) {
            if (! $method->isPublic() || !$method->isUserDefined()) {
                continue;
            }

            if ($started) {
                $result .= ',';
            }
            $started = true;

            $result .= '' . $method->getName(). ':function(';

            if ('__construct' != $method->getName()) {
                $parameters  = $method->getParameters();
                $paramCount  = count($parameters);
                $argsStarted = false;

                $argNames = "var argNames=[";
                foreach ($parameters as $param) {
                    if ($argsStarted) {
                        $result .= ',';
                    }

                    $result .= $param->getName();

                    if ($argsStarted) {
                        $argNames .= ',';
                    }

                    $argNames .= '"' . $param->getName() . '"';

                    $argsStarted = true;
                }
                $argNames .= "];";

                $result .= "){"
                         . $argNames
                         . 'var result = ZAjaxEngine.invokeRemoteMethod('
                         . "this, '" . $method->getName()
                         . "',argNames,arguments);"
                         . 'return(result);}';
            } else {
                $result .= "){}";
            }
        }

        return $result . "}";
    }


    /**
     * Encode the public properties of the ReflectionClass in the class2
     * format.
     *
     * @param $cls ReflectionClass
     * @return string Encode properties list
     *
     */
    private static function _encodeVariables(ReflectionClass $cls)
    {
        $properties = $cls->getProperties();
        $propValues = get_class_vars($cls->getName());
        $result = "variables:{";
        $cnt = 0;

        $tmpArray = array();
        foreach ($properties as $prop) {
            if (! $prop->isPublic()) {
                continue;
            }

            $tmpArray[] = $prop->getName()
                        . ':'
                        . self::encode($propValues[$prop->getName()]);
        }
        $result .= implode(',', $tmpArray);

        return $result . "}";
    }

    /**
     * Encodes the given $className into the class2 model of encoding PHP
     * classes into JavaScript class2 classes.
     * NOTE: Currently only public methods and variables are proxied onto
     * the client machine
     *
     * @param $className string The name of the class, the class must be
     * instantiable using a null constructor
     * @param $package string Optional package name appended to JavaScript
     * proxy class name
     * @return string The class2 (JavaScript) encoding of the class
     * @throws Zend_Json_Exception
     */
    public static function encodeClass($className, $package = '')
    {
        $cls = new ReflectionClass($className);
        if (! $cls->isInstantiable()) {
            #require_once 'Zend/Json/Exception.php';
            throw new Zend_Json_Exception("$className must be instantiable");
        }

        return "Class.create('$package$className',{"
                . self::_encodeConstants($cls)    .","
                . self::_encodeMethods($cls)      .","
                . self::_encodeVariables($cls)    .'});';
    }


    /**
     * Encode several classes at once
     *
     * Returns JSON encoded classes, using {@link encodeClass()}.
     *
     * @param array $classNames
     * @param string $package
     * @return string
     */
    public static function encodeClasses(array $classNames, $package = '')
    {
        $result = '';
        foreach ($classNames as $className) {
            $result .= self::encodeClass($className, $package);
        }

        return $result;
    }

    /**
     * Encode Unicode Characters to \u0000 ASCII syntax.
     *
     * This algorithm was originally developed for the
     * Solar Framework by Paul M. Jones
     *
     * @link   http://solarphp.com/
     * @link   http://svn.solarphp.com/core/trunk/Solar/Json.php
     * @param  string $value
     * @return string
     */
    public static function encodeUnicodeString($value)
    {
        $strlen_var = strlen($value);
        $ascii = "";

        /**
         * Iterate over every character in the string,
         * escaping with a slash or encoding to UTF-8 where necessary
         */
        for($i = 0; $i < $strlen_var; $i++) {
            $ord_var_c = ord($value[$i]);

            switch (true) {
                case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                    // characters U-00000000 - U-0000007F (same as ASCII)
                    $ascii .= $value[$i];
                    break;

                case (($ord_var_c & 0xE0) == 0xC0):
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c, ord($value[$i + 1]));
                    $i += 1;
                    $utf16 = self::_utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xF0) == 0xE0):
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                                 ord($value[$i + 1]),
                                 ord($value[$i + 2]));
                    $i += 2;
                    $utf16 = self::_utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xF8) == 0xF0):
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                                 ord($value[$i + 1]),
                                 ord($value[$i + 2]),
                                 ord($value[$i + 3]));
                    $i += 3;
                    $utf16 = self::_utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xFC) == 0xF8):
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                                 ord($value[$i + 1]),
                                 ord($value[$i + 2]),
                                 ord($value[$i + 3]),
                                 ord($value[$i + 4]));
                    $i += 4;
                    $utf16 = self::_utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xFE) == 0xFC):
                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                                 ord($value[$i + 1]),
                                 ord($value[$i + 2]),
                                 ord($value[$i + 3]),
                                 ord($value[$i + 4]),
                                 ord($value[$i + 5]));
                    $i += 5;
                    $utf16 = self::_utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
            }
        }

        return $ascii;
     }

    /**
     * Convert a string from one UTF-8 char to one UTF-16 char.
     *
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibye string extension.
     *
     * This method is from the Solar Framework by Paul M. Jones
     *
     * @link   http://solarphp.com
     * @param string $utf8 UTF-8 character
     * @return string UTF-16 character
     */
    protected static function _utf82utf16($utf8)
    {
        // Check for mb extension otherwise do by hand.
        if( function_exists('mb_convert_encoding') ) {
            return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
        }

        switch (strlen($utf8)) {
            case 1:
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return $utf8;

            case 2:
                // return a UTF-16 character from a 2-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x07 & (ord($utf8{0}) >> 2))
                     . chr((0xC0 & (ord($utf8{0}) << 6))
                         | (0x3F & ord($utf8{1})));

            case 3:
                // return a UTF-16 character from a 3-byte UTF-8 char
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr((0xF0 & (ord($utf8{0}) << 4))
                         | (0x0F & (ord($utf8{1}) >> 2)))
                     . chr((0xC0 & (ord($utf8{1}) << 6))
                         | (0x7F & ord($utf8{2})));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }
}

