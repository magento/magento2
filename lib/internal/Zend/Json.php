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
 * @version    $Id: Json.php 20615 2010-01-25 19:54:12Z matthew $
 */

/**
 * Zend_Json_Expr.
 *
 * @see Zend_Json_Expr
 */
#require_once 'Zend/Json/Expr.php';


/**
 * Class for encoding to and decoding from JSON.
 *
 * @category   Zend
 * @package    Zend_Json
 * @uses       Zend_Json_Expr
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json
{
    /**
     * How objects should be encoded -- arrays or as StdClass. TYPE_ARRAY is 1
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
    public static $maxRecursionDepthAllowed=25;

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
     * objects. See {@link Zend_Json_Decoder::decode()} for details.
     * @return mixed
     */
    public static function decode($encodedValue, $objectDecodeType = Zend_Json::TYPE_ARRAY)
    {
        $encodedValue = (string) $encodedValue;
        if (function_exists('json_decode') && self::$useBuiltinEncoderDecoder !== true) {
            $decode = json_decode($encodedValue, $objectDecodeType);

            // php < 5.3
            if (!function_exists('json_last_error')) {
                if ($decode === $encodedValue) {
                    #require_once 'Zend/Json/Exception.php';
                    throw new Zend_Json_Exception('Decoding failed');
                }
            // php >= 5.3
            } elseif (($jsonLastErr = json_last_error()) != JSON_ERROR_NONE) {
                #require_once 'Zend/Json/Exception.php';
                switch ($jsonLastErr) {
                    case JSON_ERROR_DEPTH:
                        throw new Zend_Json_Exception('Decoding failed: Maximum stack depth exceeded');
                    case JSON_ERROR_CTRL_CHAR:
                        throw new Zend_Json_Exception('Decoding failed: Unexpected control character found');
                    case JSON_ERROR_SYNTAX:
                        throw new Zend_Json_Exception('Decoding failed: Syntax error');
                    default:
                        throw new Zend_Json_Exception('Decoding failed');
                }
            }

            return $decode;
        }

        #require_once 'Zend/Json/Decoder.php';
        return Zend_Json_Decoder::decode($encodedValue, $objectDecodeType);
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
     * NOTE: Encoding native javascript expressions are possible using Zend_Json_Expr.
     *       You can enable this by setting $options['enableJsonExprFinder'] = true
     *
     * @see Zend_Json_Expr
     *
     * @param  mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string JSON encoded object
     */
    public static function encode($valueToEncode, $cycleCheck = false, $options = array())
    {
        if (is_object($valueToEncode) && method_exists($valueToEncode, 'toJson')) {
            return $valueToEncode->toJson();
        }

        // Pre-encoding look for Zend_Json_Expr objects and replacing by tmp ids
        $javascriptExpressions = array();
        if(isset($options['enableJsonExprFinder'])
           && ($options['enableJsonExprFinder'] == true)
        ) {
            /**
             * @see Zend_Json_Encoder
             */
            #require_once "Zend/Json/Encoder.php";
            $valueToEncode = self::_recursiveJsonExprFinder($valueToEncode, $javascriptExpressions);
        }

        // Encoding
        if (function_exists('json_encode') && self::$useBuiltinEncoderDecoder !== true) {
            $encodedResult = json_encode($valueToEncode);
        } else {
            #require_once 'Zend/Json/Encoder.php';
            $encodedResult = Zend_Json_Encoder::encode($valueToEncode, $cycleCheck, $options);
        }

        //only do post-proccessing to revert back the Zend_Json_Expr if any.
        if (count($javascriptExpressions) > 0) {
            $count = count($javascriptExpressions);
            for($i = 0; $i < $count; $i++) {
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
     * Check & Replace Zend_Json_Expr for tmp ids in the valueToEncode
     *
     * Check if the value is a Zend_Json_Expr, and if replace its value
     * with a magic key and save the javascript expression in an array.
     *
     * NOTE this method is recursive.
     *
     * NOTE: This method is used internally by the encode method.
     *
     * @see encode
     * @param mixed $valueToCheck a string - object property to be encoded
     * @return void
     */
    protected static function _recursiveJsonExprFinder(
        &$value, array &$javascriptExpressions, $currentKey = null
    ) {
         if ($value instanceof Zend_Json_Expr) {
            // TODO: Optimize with ascii keys, if performance is bad
            $magicKey = "____" . $currentKey . "_" . (count($javascriptExpressions));
            $javascriptExpressions[] = array(

                //if currentKey is integer, encodeUnicodeString call is not required.
                "magicKey" => (is_int($currentKey)) ? $magicKey : Zend_Json_Encoder::encodeUnicodeString($magicKey),
                "value"    => $value->__toString(),
            );
            $value = $magicKey;
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::_recursiveJsonExprFinder($value[$k], $javascriptExpressions, $k);
            }
        } elseif (is_object($value)) {
            foreach ($value as $k => $v) {
                $value->$k = self::_recursiveJsonExprFinder($value->$k, $javascriptExpressions, $k);
            }
        }
        return $value;
    }

    /**
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
     * converts that PHP array into JSON by calling the "encode" static funcion.
     *
     * Throws a Zend_Json_Exception if the input not a XML formatted string.
     * NOTE: Encoding native javascript expressions via Zend_Json_Expr is not possible.
     *
     * @static
     * @access public
     * @param string $xmlStringContents XML String to be converted
     * @param boolean $ignoreXmlAttributes Include or exclude XML attributes in
     * the xml2json conversion process.
     * @return mixed - JSON formatted string on success
     * @throws Zend_Json_Exception
     */
    public static function fromXml ($xmlStringContents, $ignoreXmlAttributes=true) {
        // Load the XML formatted string into a Simple XML Element object.
        $simpleXmlElementObject = simplexml_load_string($xmlStringContents);

        // If it is not a valid XML content, throw an exception.
        if ($simpleXmlElementObject == null) {
            #require_once 'Zend/Json/Exception.php';
            throw new Zend_Json_Exception('Function fromXml was called with an invalid XML formatted string.');
        } // End of if ($simpleXmlElementObject == null)

        $resultArray = null;

        // Call the recursive function to convert the XML into a PHP array.
        $resultArray = self::_processXml($simpleXmlElementObject, $ignoreXmlAttributes);

        // Convert the PHP array to JSON using Zend_Json encode method.
        // It is just that simple.
        $jsonStringOutput = self::encode($resultArray);
        return($jsonStringOutput);
    } // End of function fromXml.

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
     * Throws a Zend_Json_Exception if the XML tree is deeper than the allowed limit.
     *
     * @static
     * @access protected
     * @param SimpleXMLElement $simpleXmlElementObject XML element to be converted
     * @param boolean $ignoreXmlAttributes Include or exclude XML attributes in
     * the xml2json conversion process.
     * @param int $recursionDepth Current recursion depth of this function
     * @return mixed - On success, a PHP associative array of traversed XML elements
     * @throws Zend_Json_Exception
     */
    protected static function _processXml ($simpleXmlElementObject, $ignoreXmlAttributes, $recursionDepth=0) {
        // Keep an eye on how deeply we are involved in recursion.
        if ($recursionDepth > self::$maxRecursionDepthAllowed) {
            // XML tree is too deep. Exit now by throwing an exception.
            #require_once 'Zend/Json/Exception.php';
            throw new Zend_Json_Exception(
                "Function _processXml exceeded the allowed recursion depth of " .
                self::$maxRecursionDepthAllowed);
        } // End of if ($recursionDepth > self::$maxRecursionDepthAllowed)

        if ($recursionDepth == 0) {
            // Store the original SimpleXmlElementObject sent by the caller.
            // We will need it at the very end when we return from here for good.
            $callerProvidedSimpleXmlElementObject = $simpleXmlElementObject;
        } // End of if ($recursionDepth == 0)

        if ($simpleXmlElementObject instanceof SimpleXMLElement) {
            // Get a copy of the simpleXmlElementObject
            $copyOfSimpleXmlElementObject = $simpleXmlElementObject;
            // Get the object variables in the SimpleXmlElement object for us to iterate.
            $simpleXmlElementObject = get_object_vars($simpleXmlElementObject);
        } // End of if (get_class($simpleXmlElementObject) == "SimpleXMLElement")

        // It needs to be an array of object variables.
        if (is_array($simpleXmlElementObject)) {
            // Initialize a result array.
            $resultArray = array();
            // Is the input array size 0? Then, we reached the rare CDATA text if any.
            if (count($simpleXmlElementObject) <= 0) {
                // Let us return the lonely CDATA. It could even be
                // an empty element or just filled with whitespaces.
                return (trim(strval($copyOfSimpleXmlElementObject)));
            } // End of if (count($simpleXmlElementObject) <= 0)

            // Let us walk through the child elements now.
            foreach($simpleXmlElementObject as $key=>$value) {
                // Check if we need to ignore the XML attributes.
                // If yes, you can skip processing the XML attributes.
                // Otherwise, add the XML attributes to the result array.
                if(($ignoreXmlAttributes == true) && (is_string($key)) && ($key == "@attributes")) {
                    continue;
                } // End of if(($ignoreXmlAttributes == true) && ($key == "@attributes"))

                // Let us recursively process the current XML element we just visited.
                // Increase the recursion depth by one.
                $recursionDepth++;
                $resultArray[$key] = self::_processXml ($value, $ignoreXmlAttributes, $recursionDepth);

                // Decrease the recursion depth by one.
                $recursionDepth--;
            } // End of foreach($simpleXmlElementObject as $key=>$value) {

            if ($recursionDepth == 0) {
                // That is it. We are heading to the exit now.
                // Set the XML root element name as the root [top-level] key of
                // the associative array that we are going to return to the original
                // caller of this recursive function.
                $tempArray = $resultArray;
                $resultArray = array();
                $resultArray[$callerProvidedSimpleXmlElementObject->getName()] = $tempArray;
            } // End of if ($recursionDepth == 0)

            return($resultArray);
        } else {
            // We are now looking at either the XML attribute text or
            // the text between the XML tags.

            // In order to allow Zend_Json_Expr from xml, we check if the node
            // matchs the pattern that try to detect if it is a new Zend_Json_Expr
            // if it matches, we return a new Zend_Json_Expr instead of a text node
            $pattern = '/^[\s]*new Zend_Json_Expr[\s]*\([\s]*[\"\']{1}(.*)[\"\']{1}[\s]*\)[\s]*$/';
            $matchings = array();
            $match = preg_match ($pattern, $simpleXmlElementObject, $matchings);
            if ($match) {
                return new Zend_Json_Expr($matchings[1]);
            } else {
                return (trim(strval($simpleXmlElementObject)));
            }

        } // End of if (is_array($simpleXmlElementObject))
    } // End of function _processXml.
    
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
        
        $ind = "\t";
        if(isset($options['indent'])) {
            $ind = $options['indent'];
        }
        
        foreach($tokens as $token) {
            if($token == "") continue;
            
            $prefix = str_repeat($ind, $indent);
            if($token == "{" || $token == "[") {
                $indent++;
                if($result != "" && $result[strlen($result)-1] == "\n") {
                    $result .= $prefix;
                }
                $result .= "$token\n";
            } else if($token == "}" || $token == "]") {
                $indent--;
                $prefix = str_repeat($ind, $indent);
                $result .= "\n$prefix$token";                
            } else if($token == ",") {
                $result .= "$token\n";
            } else {
                $result .= $prefix.$token;
            }
        }
        return $result;
   }
}
