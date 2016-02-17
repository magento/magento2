<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json;

use stdClass;
use Zend\Json\Exception\InvalidArgumentException;
use Zend\Json\Exception\RuntimeException;

/**
 * Decode JSON encoded string to PHP variable constructs
 */
class Decoder
{
    /**
     * Parse tokens used to decode the JSON object. These are not
     * for public consumption, they are just used internally to the
     * class.
     */
    const EOF       = 0;
    const DATUM     = 1;
    const LBRACE    = 2;
    const LBRACKET  = 3;
    const RBRACE    = 4;
    const RBRACKET  = 5;
    const COMMA     = 6;
    const COLON     = 7;

    /**
     * Use to maintain a "pointer" to the source being decoded
     *
     * @var string
     */
    protected $source;

    /**
     * Caches the source length
     *
     * @var int
     */
    protected $sourceLength;

    /**
     * The offset within the source being decoded
     *
     * @var int
     *
     */
    protected $offset;

    /**
     * The current token being considered in the parser cycle
     *
     * @var int
     */
    protected $token;

    /**
     * Flag indicating how objects should be decoded
     *
     * @var int
     * @access protected
     */
    protected $decodeType;

    /**
     * @var $_tokenValue
     */
    protected $tokenValue;

    /**
     * Decode Unicode Characters from \u0000 ASCII syntax.
     *
     * This algorithm was originally developed for the
     * Solar Framework by Paul M. Jones
     *
     * @link   http://solarphp.com/
     * @link   https://github.com/solarphp/core/blob/master/Solar/Json.php
     * @param  string $chrs
     * @return string
     */
    public static function decodeUnicodeString($chrs)
    {
        $chrs       = (string) $chrs;
        $utf8       = '';
        $strlenChrs = strlen($chrs);

        for ($i = 0; $i < $strlenChrs; $i++) {
            $ordChrsC = ord($chrs[$i]);

            switch (true) {
                case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $i, 6)):
                    // single, escaped unicode character
                    $utf16 = chr(hexdec(substr($chrs, ($i + 2), 2)))
                           . chr(hexdec(substr($chrs, ($i + 4), 2)));
                    $utf8char = self::_utf162utf8($utf16);
                    $search  = array('\\', "\n", "\t", "\r", chr(0x08), chr(0x0C), '"', '\'', '/');
                    if (in_array($utf8char, $search)) {
                        $replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\\"', '\\\'', '\\/');
                        $utf8char  = str_replace($search, $replace, $utf8char);
                    }
                    $utf8 .= $utf8char;
                    $i += 5;
                    break;
                case ($ordChrsC >= 0x20) && ($ordChrsC <= 0x7F):
                    $utf8 .= $chrs{$i};
                    break;
                case ($ordChrsC & 0xE0) == 0xC0:
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 2);
                    ++$i;
                    break;
                case ($ordChrsC & 0xF0) == 0xE0:
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 3);
                    $i += 2;
                    break;
                case ($ordChrsC & 0xF8) == 0xF0:
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 4);
                    $i += 3;
                    break;
                case ($ordChrsC & 0xFC) == 0xF8:
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $utf8 .= substr($chrs, $i, 5);
                    $i += 4;
                    break;
                case ($ordChrsC & 0xFE) == 0xFC:
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
     * Constructor
     *
     * @param string $source     String source to decode
     * @param int    $decodeType How objects should be decoded -- see
     * {@link Zend\Json\Json::TYPE_ARRAY} and {@link Zend\Json\Json::TYPE_OBJECT} for
     * valid values
     * @throws InvalidArgumentException
     */
    protected function __construct($source, $decodeType)
    {
        // Set defaults
        $this->source       = self::decodeUnicodeString($source);
        $this->sourceLength = strlen($this->source);
        $this->token        = self::EOF;
        $this->offset       = 0;

        switch ($decodeType) {
            case Json::TYPE_ARRAY:
            case Json::TYPE_OBJECT:
                $this->decodeType = $decodeType;
                break;
            default:
                throw new InvalidArgumentException("Unknown decode type '{$decodeType}', please use one of the constants Json::TYPE_*");
        }

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
     *      - stdClass
     *      - array
     *         - array of one or more of the above types
     *
     * By default, decoded objects will be returned as associative arrays; to
     * return a stdClass object instead, pass {@link Zend\Json\Json::TYPE_OBJECT} to
     * the $objectDecodeType parameter.
     *
     * @static
     * @access public
     * @param string $source String to be decoded
     * @param int $objectDecodeType How objects should be decoded; should be
     * either or {@link Zend\Json\Json::TYPE_ARRAY} or
     * {@link Zend\Json\Json::TYPE_OBJECT}; defaults to TYPE_ARRAY
     * @return mixed
     */
    public static function decode($source, $objectDecodeType = Json::TYPE_OBJECT)
    {
        $decoder = new static($source, $objectDecodeType);
        return $decoder->_decodeValue();
    }

    /**
     * Recursive driving routine for supported toplevel tops
     *
     * @return mixed
     */
    protected function _decodeValue()
    {
        switch ($this->token) {
            case self::DATUM:
                $result  = $this->tokenValue;
                $this->_getNextToken();
                return($result);
            case self::LBRACE:
                return($this->_decodeObject());
            case self::LBRACKET:
                return($this->_decodeArray());
            default:
                return;
        }
    }

    /**
     * Decodes an object of the form:
     *  { "attribute: value, "attribute2" : value,...}
     *
     * If Zend\Json\Encoder was used to encode the original object then
     * a special attribute called __className which specifies a class
     * name that should wrap the data contained within the encoded source.
     *
     * Decodes to either an array or stdClass object, based on the value of
     * {@link $decodeType}. If invalid $decodeType present, returns as an
     * array.
     *
     * @return array|stdClass
     * @throws RuntimeException
     */
    protected function _decodeObject()
    {
        $members = array();
        $tok = $this->_getNextToken();

        while ($tok && $tok != self::RBRACE) {
            if ($tok != self::DATUM || ! is_string($this->tokenValue)) {
                throw new RuntimeException('Missing key in object encoding: ' . $this->source);
            }

            $key = $this->tokenValue;
            $tok = $this->_getNextToken();

            if ($tok != self::COLON) {
                throw new RuntimeException('Missing ":" in object encoding: ' . $this->source);
            }

            $this->_getNextToken();
            $members[$key] = $this->_decodeValue();
            $tok = $this->token;

            if ($tok == self::RBRACE) {
                break;
            }

            if ($tok != self::COMMA) {
                throw new RuntimeException('Missing "," in object encoding: ' . $this->source);
            }

            $tok = $this->_getNextToken();
        }

        switch ($this->decodeType) {
            case Json::TYPE_OBJECT:
                // Create new stdClass and populate with $members
                $result = new stdClass();
                foreach ($members as $key => $value) {
                    if ($key === '') {
                        $key = '_empty_';
                    }
                    $result->$key = $value;
                }
                break;
            case Json::TYPE_ARRAY:
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
     * @throws RuntimeException
     */
    protected function _decodeArray()
    {
        $result = array();
        $tok = $this->_getNextToken(); // Move past the '['
        $index  = 0;

        while ($tok && $tok != self::RBRACKET) {
            $result[$index++] = $this->_decodeValue();

            $tok = $this->token;

            if ($tok == self::RBRACKET || !$tok) {
                break;
            }

            if ($tok != self::COMMA) {
                throw new RuntimeException('Missing "," in array encoding: ' . $this->source);
            }

            $tok = $this->_getNextToken();
        }

        $this->_getNextToken();
        return $result;
    }

    /**
     * Removes whitespace characters from the source input
     */
    protected function _eatWhitespace()
    {
        if (preg_match('/([\t\b\f\n\r ])*/s', $this->source, $matches, PREG_OFFSET_CAPTURE, $this->offset)
            && $matches[0][1] == $this->offset) {
            $this->offset += strlen($matches[0][0]);
        }
    }

    /**
     * Retrieves the next token from the source stream
     *
     * @return int Token constant value specified in class definition
     * @throws RuntimeException
     */
    protected function _getNextToken()
    {
        $this->token      = self::EOF;
        $this->tokenValue = null;
        $this->_eatWhitespace();

        if ($this->offset >= $this->sourceLength) {
            return(self::EOF);
        }

        $str       = $this->source;
        $strLength = $this->sourceLength;
        $i         = $this->offset;
        $start     = $i;

        switch ($str{$i}) {
            case '{':
                $this->token = self::LBRACE;
                break;
            case '}':
                $this->token = self::RBRACE;
                break;
            case '[':
                $this->token = self::LBRACKET;
                break;
            case ']':
                $this->token = self::RBRACKET;
                break;
            case ',':
                $this->token = self::COMMA;
                break;
            case ':':
                $this->token = self::COLON;
                break;
            case '"':
                $result = '';
                do {
                    $i++;
                    if ($i >= $strLength) {
                        break;
                    }

                    $chr = $str{$i};

                    if ($chr == '\\') {
                        $i++;
                        if ($i >= $strLength) {
                            break;
                        }
                        $chr = $str{$i};
                        switch ($chr) {
                            case '"':
                                $result .= '"';
                                break;
                            case '\\':
                                $result .= '\\';
                                break;
                            case '/':
                                $result .= '/';
                                break;
                            case 'b':
                                $result .= "\x08";
                                break;
                            case 'f':
                                $result .= "\x0c";
                                break;
                            case 'n':
                                $result .= "\x0a";
                                break;
                            case 'r':
                                $result .= "\x0d";
                                break;
                            case 't':
                                $result .= "\x09";
                                break;
                            case '\'':
                                $result .= '\'';
                                break;
                            default:
                                throw new RuntimeException("Illegal escape sequence '{$chr}'");
                        }
                    } elseif ($chr == '"') {
                        break;
                    } else {
                        $result .= $chr;
                    }
                } while ($i < $strLength);

                $this->token = self::DATUM;
                //$this->tokenValue = substr($str, $start + 1, $i - $start - 1);
                $this->tokenValue = $result;
                break;
            case 't':
                if (($i+ 3) < $strLength && substr($str, $start, 4) == "true") {
                    $this->token = self::DATUM;
                }
                $this->tokenValue = true;
                $i += 3;
                break;
            case 'f':
                if (($i+ 4) < $strLength && substr($str, $start, 5) == "false") {
                    $this->token = self::DATUM;
                }
                $this->tokenValue = false;
                $i += 4;
                break;
            case 'n':
                if (($i+ 3) < $strLength && substr($str, $start, 4) == "null") {
                    $this->token = self::DATUM;
                }
                $this->tokenValue = null;
                $i += 3;
                break;
        }

        if ($this->token != self::EOF) {
            $this->offset = $i + 1; // Consume the last token character
            return($this->token);
        }

        $chr = $str{$i};
        if ($chr == '-' || $chr == '.' || ($chr >= '0' && $chr <= '9')) {
            if (preg_match('/-?([0-9])*(\.[0-9]*)?((e|E)((-|\+)?)[0-9]+)?/s', $str, $matches, PREG_OFFSET_CAPTURE, $start) && $matches[0][1] == $start) {
                $datum = $matches[0][0];

                if (is_numeric($datum)) {
                    if (preg_match('/^0\d+$/', $datum)) {
                        throw new RuntimeException("Octal notation not supported by JSON (value: {$datum})");
                    } else {
                        $val  = intval($datum);
                        $fVal = floatval($datum);
                        $this->tokenValue = ($val == $fVal ? $val : $fVal);
                    }
                } else {
                    throw new RuntimeException("Illegal number format: {$datum}");
                }

                $this->token = self::DATUM;
                $this->offset = $start + strlen($datum);
            }
        } else {
            throw new RuntimeException('Illegal Token');
        }

        return $this->token;
    }

    /**
     * Convert a string from one UTF-16 char to one UTF-8 char.
     *
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibyte string extension.
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
        if (function_exists('mb_convert_encoding')) {
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
