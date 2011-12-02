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
 * @version    $Id: Decoder.php 22653 2010-07-22 18:41:39Z mabe $
 */

/**
 * @see Zend_Json
 */
#require_once 'Zend/Json.php';

/**
 * Decode JSON encoded string to PHP variable constructs
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Decoder
{
    /**
     * Parse tokens used to decode the JSON object. These are not
     * for public consumption, they are just used internally to the
     * class.
     */
    const EOF         = 0;
    const DATUM        = 1;
    const LBRACE    = 2;
    const LBRACKET    = 3;
    const RBRACE     = 4;
    const RBRACKET    = 5;
    const COMMA       = 6;
    const COLON        = 7;

    /**
     * Use to maintain a "pointer" to the source being decoded
     *
     * @var string
     */
    protected $_source;

    /**
     * Caches the source length
     *
     * @var int
     */
    protected $_sourceLength;

    /**
     * The offset within the souce being decoded
     *
     * @var int
     *
     */
    protected $_offset;

    /**
     * The current token being considered in the parser cycle
     *
     * @var int
     */
    protected $_token;

    /**
     * Flag indicating how objects should be decoded
     *
     * @var int
     * @access protected
     */
    protected $_decodeType;

    /**
     * Constructor
     *
     * @param string $source String source to decode
     * @param int $decodeType How objects should be decoded -- see
     * {@link Zend_Json::TYPE_ARRAY} and {@link Zend_Json::TYPE_OBJECT} for
     * valid values
     * @return void
     */
    protected function __construct($source, $decodeType)
    {
        // Set defaults
        $this->_source       = self::decodeUnicodeString($source);
        $this->_sourceLength = strlen($this->_source);
        $this->_token        = self::EOF;
        $this->_offset       = 0;

        // Normalize and set $decodeType
        if (!in_array($decodeType, array(Zend_Json::TYPE_ARRAY, Zend_Json::TYPE_OBJECT)))
        {
            $decodeType = Zend_Json::TYPE_ARRAY;
        }
        $this->_decodeType   = $decodeType;

        // Set pointer at first token
        $this->_getNextToken();
    }

    /**
     * Decode a JSON source string
     *
     * Decodes a JSON encoded string. The value returned will be one of the
     * following:
     *        - integer
     *        - float
     *        - boolean
     *        - null
     *      - StdClass
     *      - array
     *         - array of one or more of the above types
     *
     * By default, decoded objects will be returned as associative arrays; to
     * return a StdClass object instead, pass {@link Zend_Json::TYPE_OBJECT} to
     * the $objectDecodeType parameter.
     *
     * Throws a Zend_Json_Exception if the source string is null.
     *
     * @static
     * @access public
     * @param string $source String to be decoded
     * @param int $objectDecodeType How objects should be decoded; should be
     * either or {@link Zend_Json::TYPE_ARRAY} or
     * {@link Zend_Json::TYPE_OBJECT}; defaults to TYPE_ARRAY
     * @return mixed
     * @throws Zend_Json_Exception
     */
    public static function decode($source = null, $objectDecodeType = Zend_Json::TYPE_ARRAY)
    {
        if (null === $source) {
            #require_once 'Zend/Json/Exception.php';
            throw new Zend_Json_Exception('Must specify JSON encoded source for decoding');
        } elseif (!is_string($source)) {
            #require_once 'Zend/Json/Exception.php';
            throw new Zend_Json_Exception('Can only decode JSON encoded strings');
        }

        $decoder = new self($source, $objectDecodeType);

        return $decoder->_decodeValue();
    }


    /**
     * Recursive driving rountine for supported toplevel tops
     *
     * @return mixed
     */
    protected function _decodeValue()
    {
        switch ($this->_token) {
            case self::DATUM:
                $result  = $this->_tokenValue;
                $this->_getNextToken();
                return($result);
                break;
            case self::LBRACE:
                return($this->_decodeObject());
                break;
            case self::LBRACKET:
                return($this->_decodeArray());
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Decodes an object of the form:
     *  { "attribute: value, "attribute2" : value,...}
     *
     * If Zend_Json_Encoder was used to encode the original object then
     * a special attribute called __className which specifies a class
     * name that should wrap the data contained within the encoded source.
     *
     * Decodes to either an array or StdClass object, based on the value of
     * {@link $_decodeType}. If invalid $_decodeType present, returns as an
     * array.
     *
     * @return array|StdClass
     */
    protected function _decodeObject()
    {
        $members = array();
        $tok = $this->_getNextToken();

        while ($tok && $tok != self::RBRACE) {
            if ($tok != self::DATUM || ! is_string($this->_tokenValue)) {
                #require_once 'Zend/Json/Exception.php';
                throw new Zend_Json_Exception('Missing key in object encoding: ' . $this->_source);
            }

            $key = $this->_tokenValue;
            $tok = $this->_getNextToken();

            if ($tok != self::COLON) {
                #require_once 'Zend/Json/Exception.php';
                throw new Zend_Json_Exception('Missing ":" in object encoding: ' . $this->_source);
            }

            $tok = $this->_getNextToken();
            $members[$key] = $this->_decodeValue();
            $tok = $this->_token;

            if ($tok == self::RBRACE) {
                break;
            }

            if ($tok != self::COMMA) {
                #require_once 'Zend/Json/Exception.php';
                throw new Zend_Json_Exception('Missing "," in object encoding: ' . $this->_source);
            }

            $tok = $this->_getNextToken();
        }

        switch ($this->_decodeType) {
            case Zend_Json::TYPE_OBJECT:
                // Create new StdClass and populate with $members
                $result = new StdClass();
                foreach ($members as $key => $value) {
                    $result->$key = $value;
                }
                break;
            case Zend_Json::TYPE_ARRAY:
            default:
                $result = $members;
                break;
        }

        $this->_getNextToken();
        return $result;
    }

    /**
     * Decodes a JSON array format:
     *    [element, element2,...,elementN]
     *
     * @return array
     */
    protected function _decodeArray()
    {
        $result = array();
        $starttok = $tok = $this->_getNextToken(); // Move past the '['
        $index  = 0;

        while ($tok && $tok != self::RBRACKET) {
            $result[$index++] = $this->_decodeValue();

            $tok = $this->_token;

            if ($tok == self::RBRACKET || !$tok) {
                break;
            }

            if ($tok != self::COMMA) {
                #require_once 'Zend/Json/Exception.php';
                throw new Zend_Json_Exception('Missing "," in array encoding: ' . $this->_source);
            }

            $tok = $this->_getNextToken();
        }

        $this->_getNextToken();
        return($result);
    }


    /**
     * Removes whitepsace characters from the source input
     */
    protected function _eatWhitespace()
    {
        if (preg_match(
                '/([\t\b\f\n\r ])*/s',
                $this->_source,
                $matches,
                PREG_OFFSET_CAPTURE,
                $this->_offset)
            && $matches[0][1] == $this->_offset)
        {
            $this->_offset += strlen($matches[0][0]);
        }
    }


    /**
     * Retrieves the next token from the source stream
     *
     * @return int Token constant value specified in class definition
     */
    protected function _getNextToken()
    {
        $this->_token      = self::EOF;
        $this->_tokenValue = null;
        $this->_eatWhitespace();

        if ($this->_offset >= $this->_sourceLength) {
            return(self::EOF);
        }

        $str        = $this->_source;
        $str_length = $this->_sourceLength;
        $i          = $this->_offset;
        $start      = $i;

        switch ($str{$i}) {
            case '{':
               $this->_token = self::LBRACE;
               break;
            case '}':
                $this->_token = self::RBRACE;
                break;
            case '[':
                $this->_token = self::LBRACKET;
                break;
            case ']':
                $this->_token = self::RBRACKET;
                break;
            case ',':
                $this->_token = self::COMMA;
                break;
            case ':':
                $this->_token = self::COLON;
                break;
            case  '"':
                $result = '';
                do {
                    $i++;
                    if ($i >= $str_length) {
                        break;
                    }

                    $chr = $str{$i};

                    if ($chr == '\\') {
                        $i++;
                        if ($i >= $str_length) {
                            break;
                        }
                        $chr = $str{$i};
                        switch ($chr) {
                            case '"' :
                                $result .= '"';
                                break;
                            case '\\':
                                $result .= '\\';
                                break;
                            case '/' :
                                $result .= '/';
                                break;
                            case 'b' :
                                $result .= "\x08";
                                break;
                            case 'f' :
                                $result .= "\x0c";
                                break;
                            case 'n' :
                                $result .= "\x0a";
                                break;
                            case 'r' :
                                $result .= "\x0d";
                                break;
                            case 't' :
                                $result .= "\x09";
                                break;
                            case '\'' :
                                $result .= '\'';
                                break;
                            default:
                                #require_once 'Zend/Json/Exception.php';
                                throw new Zend_Json_Exception("Illegal escape "
                                    .  "sequence '" . $chr . "'");
                        }
                    } elseif($chr == '"') {
                        break;
                    } else {
                        $result .= $chr;
                    }
                } while ($i < $str_length);

                $this->_token = self::DATUM;
                //$this->_tokenValue = substr($str, $start + 1, $i - $start - 1);
                $this->_tokenValue = $result;
                break;
            case 't':
                if (($i+ 3) < $str_length && substr($str, $start, 4) == "true") {
                    $this->_token = self::DATUM;
                }
                $this->_tokenValue = true;
                $i += 3;
                break;
            case 'f':
                if (($i+ 4) < $str_length && substr($str, $start, 5) == "false") {
                    $this->_token = self::DATUM;
                }
                $this->_tokenValue = false;
                $i += 4;
                break;
            case 'n':
                if (($i+ 3) < $str_length && substr($str, $start, 4) == "null") {
                    $this->_token = self::DATUM;
                }
                $this->_tokenValue = NULL;
                $i += 3;
                break;
        }

        if ($this->_token != self::EOF) {
            $this->_offset = $i + 1; // Consume the last token character
            return($this->_token);
        }

        $chr = $str{$i};
        if ($chr == '-' || $chr == '.' || ($chr >= '0' && $chr <= '9')) {
            if (preg_match('/-?([0-9])*(\.[0-9]*)?((e|E)((-|\+)?)[0-9]+)?/s',
                $str, $matches, PREG_OFFSET_CAPTURE, $start) && $matches[0][1] == $start) {

                $datum = $matches[0][0];

                if (is_numeric($datum)) {
                    if (preg_match('/^0\d+$/', $datum)) {
                        #require_once 'Zend/Json/Exception.php';
                        throw new Zend_Json_Exception("Octal notation not supported by JSON (value: $datum)");
                    } else {
                        $val  = intval($datum);
                        $fVal = floatval($datum);
                        $this->_tokenValue = ($val == $fVal ? $val : $fVal);
                    }
                } else {
                    #require_once 'Zend/Json/Exception.php';
                    throw new Zend_Json_Exception("Illegal number format: $datum");
                }

                $this->_token = self::DATUM;
                $this->_offset = $start + strlen($datum);
            }
        } else {
            #require_once 'Zend/Json/Exception.php';
            throw new Zend_Json_Exception('Illegal Token');
        }

        return($this->_token);
    }

    /**
     * Decode Unicode Characters from \u0000 ASCII syntax.
     *
     * This algorithm was originally developed for the
     * Solar Framework by Paul M. Jones
     *
     * @link   http://solarphp.com/
     * @link   http://svn.solarphp.com/core/trunk/Solar/Json.php
     * @param  string $value
     * @return string
     */
    public static function decodeUnicodeString($chrs)
    {
        $delim       = substr($chrs, 0, 1);
        $utf8        = '';
        $strlen_chrs = strlen($chrs);

        for($i = 0; $i < $strlen_chrs; $i++) {

            $substr_chrs_c_2 = substr($chrs, $i, 2);
            $ord_chrs_c = ord($chrs[$i]);

            switch (true) {
                case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $i, 6)):
                    // single, escaped unicode character
                    $utf16 = chr(hexdec(substr($chrs, ($i + 2), 2)))
                           . chr(hexdec(substr($chrs, ($i + 4), 2)));
                    $utf8 .= self::_utf162utf8($utf16);
                    $i += 5;
                    break;
                case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                    $utf8 .= $chrs{$i};
                    break;
                case ($ord_chrs_c & 0xE0) == 0xC0:
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 2);
                    ++$i;
                    break;
                case ($ord_chrs_c & 0xF0) == 0xE0:
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 3);
                    $i += 2;
                    break;
                case ($ord_chrs_c & 0xF8) == 0xF0:
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 4);
                    $i += 3;
                    break;
                case ($ord_chrs_c & 0xFC) == 0xF8:
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 5);
                    $i += 4;
                    break;
                case ($ord_chrs_c & 0xFE) == 0xFC:
                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 6);
                    $i += 5;
                    break;
            }
        }

        return $utf8;
    }

    /**
     * Convert a string from one UTF-16 char to one UTF-8 char.
     *
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibye string extension.
     *
     * This method is from the Solar Framework by Paul M. Jones
     *
     * @link   http://solarphp.com
     * @param  string $utf16 UTF-16 character
     * @return string UTF-8 character
     */
    protected static function _utf162utf8($utf16)
    {
        // Check for mb extension otherwise do by hand.
        if( function_exists('mb_convert_encoding') ) {
            return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
        }

        $bytes = (ord($utf16{0}) << 8) | ord($utf16{1});

        switch (true) {
            case ((0x7F & $bytes) == $bytes):
                // this case should never be reached, because we are in ASCII range
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x7F & $bytes);

            case (0x07FF & $bytes) == $bytes:
                // return a 2-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xC0 | (($bytes >> 6) & 0x1F))
                     . chr(0x80 | ($bytes & 0x3F));

            case (0xFFFF & $bytes) == $bytes:
                // return a 3-byte UTF-8 character
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xE0 | (($bytes >> 12) & 0x0F))
                     . chr(0x80 | (($bytes >> 6) & 0x3F))
                     . chr(0x80 | ($bytes & 0x3F));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }
}

