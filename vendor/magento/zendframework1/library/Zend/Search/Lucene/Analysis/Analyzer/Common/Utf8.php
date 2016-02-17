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
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Search_Lucene_Analysis_Analyzer_Common */
#require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8 extends Zend_Search_Lucene_Analysis_Analyzer_Common
{
    /**
     * Current char position in an UTF-8 stream
     *
     * @var integer
     */
    private $_position;

    /**
     * Current binary position in an UTF-8 stream
     *
     * @var integer
     */
    private $_bytePosition;

    /**
     * Object constructor
     *
     * @throws Zend_Search_Lucene_Exception
     */
    public function __construct()
    {
        if (@preg_match('/\pL/u', 'a') != 1) {
            // PCRE unicode support is turned off
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Utf8 analyzer needs PCRE unicode support to be enabled.');
        }
    }

    /**
     * Reset token stream
     */
    public function reset()
    {
        $this->_position     = 0;
        $this->_bytePosition = 0;

        // convert input into UTF-8
        if (strcasecmp($this->_encoding, 'utf8' ) != 0  &&
            strcasecmp($this->_encoding, 'utf-8') != 0 ) {
                $this->_input = iconv($this->_encoding, 'UTF-8', $this->_input);
                $this->_encoding = 'UTF-8';
        }
    }

    /**
     * Tokenization stream API
     * Get next token
     * Returns null at the end of stream
     *
     * @return Zend_Search_Lucene_Analysis_Token|null
     */
    public function nextToken()
    {
        if ($this->_input === null) {
            return null;
        }

        do {
            if (! preg_match('/[\p{L}]+/u', $this->_input, $match, PREG_OFFSET_CAPTURE, $this->_bytePosition)) {
                // It covers both cases a) there are no matches (preg_match(...) === 0)
                // b) error occured (preg_match(...) === FALSE)
                return null;
            }

            // matched string
            $matchedWord = $match[0][0];

            // binary position of the matched word in the input stream
            $binStartPos = $match[0][1];

            // character position of the matched word in the input stream
            $startPos = $this->_position +
                        iconv_strlen(substr($this->_input,
                                            $this->_bytePosition,
                                            $binStartPos - $this->_bytePosition),
                                     'UTF-8');
            // character postion of the end of matched word in the input stream
            $endPos = $startPos + iconv_strlen($matchedWord, 'UTF-8');

            $this->_bytePosition = $binStartPos + strlen($matchedWord);
            $this->_position     = $endPos;

            $token = $this->normalize(new Zend_Search_Lucene_Analysis_Token($matchedWord, $startPos, $endPos));
        } while ($token === null); // try again if token is skipped

        return $token;
    }
}

