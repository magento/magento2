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
 * @version    $Id: Token.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Analysis_Token
{
    /**
     * The text of the term.
     *
     * @var string
     */
    private $_termText;

    /**
     * Start in source text.
     *
     * @var integer
     */
    private $_startOffset;

    /**
     * End in source text
     *
     * @var integer
     */
    private $_endOffset;

    /**
     * The position of this token relative to the previous Token.
     *
     * The default value is one.
     *
     * Some common uses for this are:
     * Set it to zero to put multiple terms in the same position.  This is
     * useful if, e.g., a word has multiple stems.  Searches for phrases
     * including either stem will match.  In this case, all but the first stem's
     * increment should be set to zero: the increment of the first instance
     * should be one.  Repeating a token with an increment of zero can also be
     * used to boost the scores of matches on that token.
     *
     * Set it to values greater than one to inhibit exact phrase matches.
     * If, for example, one does not want phrases to match across removed stop
     * words, then one could build a stop word filter that removes stop words and
     * also sets the increment to the number of stop words removed before each
     * non-stop word.  Then exact phrase queries will only match when the terms
     * occur with no intervening stop words.
     *
     * @var integer
     */
    private $_positionIncrement;


    /**
     * Object constructor
     *
     * @param string  $text
     * @param integer $start
     * @param integer $end
     * @param string  $type
     */
    public function __construct($text, $start, $end)
    {
        $this->_termText    = $text;
        $this->_startOffset = $start;
        $this->_endOffset   = $end;

        $this->_positionIncrement = 1;
    }


    /**
     * positionIncrement setter
     *
     * @param integer $positionIncrement
     */
    public function setPositionIncrement($positionIncrement)
    {
        $this->_positionIncrement = $positionIncrement;
    }

    /**
     * Returns the position increment of this Token.
     *
     * @return integer
     */
    public function getPositionIncrement()
    {
        return $this->_positionIncrement;
    }

    /**
     * Returns the Token's term text.
     *
     * @return string
     */
    public function getTermText()
    {
        return $this->_termText;
    }

    /**
     * Returns this Token's starting offset, the position of the first character
     * corresponding to this token in the source text.
     *
     * Note:
     * The difference between getEndOffset() and getStartOffset() may not be equal
     * to strlen(Zend_Search_Lucene_Analysis_Token::getTermText()), as the term text may have been altered
     * by a stemmer or some other filter.
     *
     * @return integer
     */
    public function getStartOffset()
    {
        return $this->_startOffset;
    }

    /**
     * Returns this Token's ending offset, one greater than the position of the
     * last character corresponding to this token in the source text.
     *
     * @return integer
     */
    public function getEndOffset()
    {
        return $this->_endOffset;
    }
}

