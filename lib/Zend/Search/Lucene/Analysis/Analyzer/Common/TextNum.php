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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TextNum.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Search_Lucene_Analysis_Analyzer_Common */
#require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum extends Zend_Search_Lucene_Analysis_Analyzer_Common
{
    /**
     * Current position in a stream
     *
     * @var integer
     */
    private $_position;

    /**
     * Reset token stream
     */
    public function reset()
    {
        $this->_position = 0;

        if ($this->_input === null) {
            return;
        }

        // convert input into ascii
        if (PHP_OS != 'AIX') {
            $this->_input = iconv($this->_encoding, 'ASCII//TRANSLIT', $this->_input);
        }
        $this->_encoding = 'ASCII';
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
            if (! preg_match('/[a-zA-Z0-9]+/', $this->_input, $match, PREG_OFFSET_CAPTURE, $this->_position)) {
                // It covers both cases a) there are no matches (preg_match(...) === 0)
                // b) error occured (preg_match(...) === FALSE)
                return null;
            }

            $str = $match[0][0];
            $pos = $match[0][1];
            $endpos = $pos + strlen($str);

            $this->_position = $endpos;

            $token = $this->normalize(new Zend_Search_Lucene_Analysis_Token($str, $pos, $endpos));
        } while ($token === null); // try again if token is skipped

        return $token;
    }
}

