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


/** User land classes and interfaces turned on by Zend/Search/Analyzer.php file inclusion. */
/** @todo Section should be removed with ZF 2.0 release as obsolete                      */
if (!defined('ZEND_SEARCH_LUCENE_COMMON_ANALYZER_PROCESSED')) {
    /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8 */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8.php';

    /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8/CaseInsensitive.php';

    /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8Num.php';

    /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8Num/CaseInsensitive.php';

    /** Zend_Search_Lucene_Analysis_Analyzer_Common_Text */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Text.php';

    /** Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Text/CaseInsensitive.php';

    /** Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/TextNum.php';

    /** Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive */
    #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/TextNum/CaseInsensitive.php';
}


/**
 * An Analyzer is used to analyze text.
 * It thus represents a policy for extracting index terms from text.
 *
 * Note:
 * Lucene Java implementation is oriented to streams. It provides effective work
 * with a huge documents (more then 20Mb).
 * But engine itself is not oriented such documents.
 * Thus Zend_Search_Lucene analysis API works with data strings and sets (arrays).
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

abstract class Zend_Search_Lucene_Analysis_Analyzer
{
    /**
     * The Analyzer implementation used by default.
     *
     * @var Zend_Search_Lucene_Analysis_Analyzer
     */
    private static $_defaultImpl;

    /**
     * Input string
     *
     * @var string
     */
    protected $_input = null;

    /**
     * Input string encoding
     *
     * @var string
     */
    protected $_encoding = '';

    /**
     * Tokenize text to a terms
     * Returns array of Zend_Search_Lucene_Analysis_Token objects
     *
     * Tokens are returned in UTF-8 (internal Zend_Search_Lucene encoding)
     *
     * @param string $data
     * @return array
     */
    public function tokenize($data, $encoding = '')
    {
        $this->setInput($data, $encoding);

        $tokenList = array();
        while (($nextToken = $this->nextToken()) !== null) {
            $tokenList[] = $nextToken;
        }

        return $tokenList;
    }


    /**
     * Tokenization stream API
     * Set input
     *
     * @param string $data
     */
    public function setInput($data, $encoding = '')
    {
        $this->_input    = $data;
        $this->_encoding = $encoding;
        $this->reset();
    }

    /**
     * Reset token stream
     */
    abstract public function reset();

    /**
     * Tokenization stream API
     * Get next token
     * Returns null at the end of stream
     *
     * Tokens are returned in UTF-8 (internal Zend_Search_Lucene encoding)
     *
     * @return Zend_Search_Lucene_Analysis_Token|null
     */
    abstract public function nextToken();




    /**
     * Set the default Analyzer implementation used by indexing code.
     *
     * @param Zend_Search_Lucene_Analysis_Analyzer $similarity
     */
    public static function setDefault(Zend_Search_Lucene_Analysis_Analyzer $analyzer)
    {
        self::$_defaultImpl = $analyzer;
    }


    /**
     * Return the default Analyzer implementation used by indexing code.
     *
     * @return Zend_Search_Lucene_Analysis_Analyzer
     */
    public static function getDefault()
    {
        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive */
        #require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Text/CaseInsensitive.php';

        if (!self::$_defaultImpl instanceof Zend_Search_Lucene_Analysis_Analyzer) {
            self::$_defaultImpl = new Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive();
        }

        return self::$_defaultImpl;
    }
}

