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


/** Define constant used to provide correct file processing order    */
/** @todo Section should be removed with ZF 2.0 release as obsolete  */
if (!defined('ZEND_SEARCH_LUCENE_COMMON_ANALYZER_PROCESSED')) {
    define('ZEND_SEARCH_LUCENE_COMMON_ANALYZER_PROCESSED', true);
}


/** Zend_Search_Lucene_Analysis_Analyzer */
#require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';

/** Zend_Search_Lucene_Analysis_Token */
#require_once 'Zend/Search/Lucene/Analysis/Token.php';

/** Zend_Search_Lucene_Analysis_TokenFilter */
#require_once 'Zend/Search/Lucene/Analysis/TokenFilter.php';


/**
 * Common implementation of the Zend_Search_Lucene_Analysis_Analyzer interface.
 * There are several standard standard subclasses provided by Zend_Search_Lucene/Analysis
 * subpackage: Zend_Search_Lucene_Analysis_Analyzer_Common_Text, ZSearchHTMLAnalyzer, ZSearchXMLAnalyzer.
 *
 * @todo ZSearchHTMLAnalyzer and ZSearchXMLAnalyzer implementation
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_Analysis_Analyzer_Common extends Zend_Search_Lucene_Analysis_Analyzer
{
    /**
     * The set of Token filters applied to the Token stream.
     * Array of Zend_Search_Lucene_Analysis_TokenFilter objects.
     *
     * @var array
     */
    private $_filters = array();

    /**
     * Add Token filter to the Analyzer
     *
     * @param Zend_Search_Lucene_Analysis_TokenFilter $filter
     */
    public function addFilter(Zend_Search_Lucene_Analysis_TokenFilter $filter)
    {
        $this->_filters[] = $filter;
    }

    /**
     * Apply filters to the token. Can return null when the token was removed.
     *
     * @param Zend_Search_Lucene_Analysis_Token $token
     * @return Zend_Search_Lucene_Analysis_Token
     */
    public function normalize(Zend_Search_Lucene_Analysis_Token $token)
    {
        foreach ($this->_filters as $filter) {
            $token = $filter->normalize($token);

            // resulting token can be null if the filter removes it
            if ($token === null) {
                return null;
            }
        }

        return $token;
    }
}

