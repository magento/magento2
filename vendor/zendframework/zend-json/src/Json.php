<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json;

use SimpleXMLElement;
use Zend\Json\Exception\RecursionException;
use Zend\Json\Exception\RuntimeException;
use ZendXml\Security as XmlSecurity;

/**
 * Class for encoding to and decoding from JSON.
 */
class Json
{
    /**
     * How objects should be encoded -- arrays or as stdClass. TYPE_ARRAY is 1
     * so that it is a boolean true value, allowing it to be used with
     * ext/json's functions.
     */
    const TYPE_ARRAY  = 1;
    const TYPE_OBJECT = 0;

     /**
      * To check the allowed nesting depth of the XML tree during xml2json conversion.
      *
      * @var int
      */
    public static $maxRecursionDepthAllowed = 25;

    /**
     * @var bool
     */
    public static $useBuiltinEncoderDecoder = false;

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * Uses ext/json's json_decode if available.
     *
     * @param string $encodedValue Encoded in JSON format
     * @param int $objectDecodeType Optional; flag indicating how to decode
     * objects. See {@link Zend\Json\Decoder::decode()} for details.
     * @return mixed
     * @throws RuntimeException
     */
    public static function decode($encodedValue, $objectDecodeType = self::TYPE_OBJECT)
    {
        $encodedValue = (string) $encodedValue;
        if (function_exists('json_decode') && static::$useBuiltinEncoderDecoder !== true) {
            $decode = json_decode($encodedValue, $objectDecodeType);

            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
                    throw new RuntimeException('Decoding failed: Maximum stack depth exceeded');
                case JSON_ERROR_CTRL_CHAR:
                    throw new RuntimeException('Decoding failed: Unexpected control character found');
                case JSON_ERROR_SYNTAX:
                    throw new RuntimeException('Decoding failed: Syntax error');
                default:
                    throw new RuntimeException('Decoding failed');
            }

            return $decode;
        }

        return Decoder::decode($encodedValue, $objectDecodeType);
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * Encodes using ext/json's json_encode() if available.
     *
     * NOTE: Object should not contain cycles; the JSON format
     * does not allow object reference.
     *
     * NOTE: Only public variables will be encoded
     *
     * NOTE: Encoding native javascript expressions are possible using Zend\Json\Expr.
     *       You can enable this by setting $options['enableJsonExprFinder'] = true
     *
     * @see Zend\Json\Expr
     *
     * @param  mixed $valueToEncode
     * @param  bool $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string JSON encoded object
     */
    public static function encode($valueToEncode, $cycleCheck = false, $options = array())
    {
        if (is_object($valueToEncode)) {
            if (method_exists($valueToEncode, 'toJson')) {
                return $valueToEncode->toJson();
            } elseif (method_exists($valueToEncode, 'toArray')) {
                return static::encode($valueToEncode->toArray(), $cycleCheck, $options);
            }
        }

        // Pre-encoding look for Zend\Json\Expr objects and replacing by tmp ids
        $javascriptExpressions = array();
        if (isset($options['enableJsonExprFinder'])
           && ($options['enableJsonExprFinder'] == true)
        ) {
            $valueToEncode = static::_recursiveJsonExprFinder($valueToEncode, $javascriptExpressions);
        }

        $prettyPrint = (isset($options['prettyPrint']) && ($options['prettyPrint'] == true));

        // Encoding
        if (function_exists('json_encode') && static::$useBuiltinEncoderDecoder !== true) {
            $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

            if ($prettyPrint && defined('JSON_PRETTY_PRINT')) {
                $encodeOptions |= JSON_PRETTY_PRINT;
                $prettyPrint = false;
            }

            $encodedResult = json_encode(
                $valueToEncode,
                $encodeOptions
            );
        } else {
            $encodedResult = Encoder::encode($valueToEncode, $cycleCheck, $options);
        }

        if ($prettyPrint) {
            $encodedResult = self::prettyPrint($encodedResult, array("intent" => "    "));
        }

        //only do post-processing to revert back the Zend\Json\Expr if any.
        if (count($javascriptExpressions) > 0) {
            $count = count($javascriptExpressions);
            for ($i = 0; $i < $count; $i++) {
                $magicKey = $javascriptExpressions[$i]['magicKey'];
                $value    = $javascriptExpressions[$i]['value'];

                $encodedResult = str_replace(
                    //instead of replacing "key:magicKey", we replace directly magicKey by value because "key" never changes.
                    '"' . $magicKey . '"',
                    $value,
                    $encodedResult
                );
            }
        }

        return $encodedResult;
    }

    /**
     * Check & Replace Zend\Json\Expr for tmp ids in the valueToEncode
     *
     * Check if the value is a Zend\Json\Expr, and if replace its value
     * with a magic key and save the javascript expression in an array.
     *
     * NOTE this method is recursive.
     *
     * NOTE: This method is used internally by the encode method.
     *
     * @see encode
     * @param mixed $value a string - object property to be encoded
     * @param array $javascriptExpressions
     * @param null|string|int $currentKey
     * @return mixed
     */
    protected static function _recursiveJsonExprFinder(
        &$value,
        array &$javascriptExpressions,
        $currentKey = null
    ) {
        if ($value instanceof Expr) {
            // TODO: Optimize with ascii keys, if performance is bad
            $magicKey = "____" . $currentKey . "_" . (count($javascriptExpressions));
            $javascriptExpressions[] = array(

                //if currentKey is integer, encodeUnicodeString call is not required.
                "magicKey" => (is_int($currentKey)) ? $magicKey : Encoder::encodeUnicodeString($magicKey),
                "value"    => $value->__toString(),
            );
            $value = $magicKey;
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::_recursiveJsonExprFinder($value[$k], $javascriptExpressions, $k);
            }
        } elseif (is_object($value)) {
            foreach ($value as $k => $v) {
                $value->$k = static::_recursiveJsonExprFinder($value->$k, $javascriptExpressions, $k);
            }
        }
        return $value;
    }
    /**
     * Return the value of an XML attribute text or the text between
     * the XML tags
     *
     * In order to allow Zend\Json\Expr from xml, we check if the node
     * matches the pattern that try to detect if it is a new Zend\Json\Expr
     * if it matches, we return a new Zend\Json\Expr instead of a text node
     *
     * @param SimpleXMLElement $simpleXmlElementObject
     * @return Expr|string
     */
    protected static function _getXmlValue($simpleXmlElementObject)
    {
        $pattern   = '/^[\s]*new Zend[_\\]Json[_\\]Expr[\s]*\([\s]*[\"\']{1}(.*)[\"\']{1}[\s]*\)[\s]*$/';
        $matchings = array();
        $match     = preg_match($pattern, $simpleXmlElementObject, $matchings);
        if ($match) {
            return new Expr($matchings[1]);
        }
        return (trim(strval($simpleXmlElementObject)));
    }

    /**
     * _processXml - Contains the logic for xml2json
     *
     * The logic in this function is a recursive one.
     *
     * The main caller of this function (i.e. fromXml) needs to provide
     * only the first two parameters i.e. the SimpleXMLElement object and
     * the flag for ignoring or not ignoring XML attributes. The third parameter
     * will be used internally within this function during the recursive calls.
     *
     * This function converts the SimpleXMLElement object into a PHP array by
     * calling a recursive (protected static) function in this class. Once all
     * the XML elements are stored in the PHP array, it is returned to the caller.
     *
     * @param SimpleXMLElement $simpleXmlElementObject
     * @param  bool $ignoreXmlAttributes
     * @param int $recursionDepth
     * @throws Exception\RecursionException if the XML tree is deeper than the allowed limit.
     * @return array
     */
    protected static function _processXml($simpleXmlElementObject, $ignoreXmlAttributes, $recursionDepth = 0)
    {
        // Keep an eye on how deeply we are involved in recursion.
        if ($recursionDepth > static::$maxRecursionDepthAllowed) {
            // XML tree is too deep. Exit now by throwing an exception.
            throw new RecursionException(
                "Function _processXml exceeded the allowed recursion depth of "
                .  static::$maxRecursionDepthAllowed
            );
        }

        $children   = $simpleXmlElementObject->children();
        $name       = $simpleXmlElementObject->getName();
        $value      = static::_getXmlValue($simpleXmlElementObject);
        $attributes = (array) $simpleXmlElementObject->attributes();

        if (!count($children)) {
            if (!empty($attributes) && !$ignoreXmlAttributes) {
                foreach ($attributes['@attributes'] as $k => $v) {
                    $attributes['@attributes'][$k] = static::_getXmlValue($v);
                }
                if (!empty($value)) {
                    $attributes['@text'] = $value;
                }
                return array($name => $attributes);
            }

            return array($name => $value);
        }

        $childArray = array();
        foreach ($children as $child) {
            $childname = $child->getName();
            $element   = static::_processXml($child, $ignoreXmlAttributes, $recursionDepth + 1);
            if (array_key_exists($childname, $childArray)) {
                if (empty($subChild[$childname])) {
                    $childArray[$childname] = array($childArray[$childname]);
                    $subChild[$childname]   = true;
                }
                $childArray[$childname][] = $element[$childname];
            } else {
                $childArray[$childname] = $element[$childname];
            }
        }

        if (!empty($attributes) && !$ignoreXmlAttributes) {
            foreach ($attributes['@attributes'] as $k => $v) {
                $attributes['@attributes'][$k] = static::_getXmlValue($v);
            }
            $childArray['@attributes'] = $attributes['@attributes'];
        }

        if (!empty($value)) {
            $childArray['@text'] = $value;
        }

        return array($name => $childArray);
    }

    /**
     * @deprecated by https://github.com/zendframework/zf2/pull/6778
     * fromXml - Converts XML to JSON
     *
     * Converts a XML formatted string into a JSON formatted string.
     * The value returned will be a string in JSON format.
     *
     * The caller of this function needs to provide only the first parameter,
     * which is an XML formatted String. The second parameter is optional, which
     * lets the user to select if the XML attributes in the input XML string
     * should be included or ignored in xml2json conversion.
     *
     * This function converts the XML formatted string into a PHP array by
     * calling a recursive (protected static) function in this class. Then, it
     * converts that PHP array into JSON by calling the "encode" static function.
     *
     * NOTE: Encoding native javascript expressions via Zend\Json\Expr is not possible.
     *
     * @static
     * @access public
     * @param string $xmlStringContents XML String to be converted
     * @param  bool $ignoreXmlAttributes Include or exclude XML attributes in
     * the xml2json conversion process.
     * @return mixed - JSON formatted string on success
     * @throws \Zend\Json\Exception\RuntimeException if the input not a XML formatted string
     */
    public static function fromXml($xmlStringContents, $ignoreXmlAttributes = true)
    {
        // Load the XML formatted string into a Simple XML Element object.
        $simpleXmlElementObject = XmlSecurity::scan($xmlStringContents);

        // If it is not a valid XML content, throw an exception.
        if (!$simpleXmlElementObject) {
            throw new RuntimeException('Function fromXml was called with an invalid XML formatted string.');
        } // End of if ($simpleXmlElementObject === null)

        // Call the recursive function to convert the XML into a PHP array.
        $resultArray = static::_processXml($simpleXmlElementObject, $ignoreXmlAttributes);

        // Convert the PHP array to JSON using Zend\Json\Json encode method.
        // It is just that simple.
        $jsonStringOutput = static::encode($resultArray);
        return($jsonStringOutput);
    }

    /**
     * Pretty-print JSON string
     *
     * Use 'indent' option to select indentation string - by default it's a tab
     *
     * @param string $json Original JSON string
     * @param array $options Encoding options
     * @return string
     */
    public static function prettyPrint($json, $options = array())
    {
        $tokens = preg_split('|([\{\}\]\[,])|', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = "";
        $indent = 0;

        $ind = "    ";
        if (isset($options['indent'])) {
            $ind = $options['indent'];
        }

        $inLiteral = false;
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token == "") {
                continue;
            }

            if (preg_match('/^("(?:.*)"):[ ]?(.*)$/', $token, $matches)) {
                $token = $matches[1] . ': ' . $matches[2];
            }

            $prefix = str_repeat($ind, $indent);
            if (!$inLiteral && ($token == "{" || $token == "[")) {
                $indent++;
                if ($result != "" && $result[strlen($result)-1] == "\n") {
                    $result .= $prefix;
                }
                $result .= "$token\n";
            } elseif (!$inLiteral && ($token == "}" || $token == "]")) {
                $indent--;
                $prefix = str_repeat($ind, $indent);
                $result .= "\n$prefix$token";
            } elseif (!$inLiteral && $token == ",") {
                $result .= "$token\n";
            } else {
                $result .= ($inLiteral ?  '' : $prefix) . $token;

                //remove escaped backslash sequences causing false positives in next check
                $token = str_replace('\\', '', $token);
                // Count # of unescaped double-quotes in token, subtract # of
                // escaped double-quotes and if the result is odd then we are
                // inside a string literal
                if ((substr_count($token, '"')-substr_count($token, '\\"')) % 2 != 0) {
                    $inLiteral = !$inLiteral;
                }
            }
        }
        return $result;
    }
}
