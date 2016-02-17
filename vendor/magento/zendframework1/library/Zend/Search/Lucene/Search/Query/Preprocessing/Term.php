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
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Search_Lucene_Search_Query_Processing */
#require_once 'Zend/Search/Lucene/Search/Query/Preprocessing.php';


/**
 * It's an internal abstract class intended to finalize ase a query processing after query parsing.
 * This type of query is not actually involved into query execution.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @internal
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Query_Preprocessing_Term extends Zend_Search_Lucene_Search_Query_Preprocessing
{
    /**
     * word (query parser lexeme) to find.
     *
     * @var string
     */
    private $_word;

    /**
     * Word encoding (field name is always provided using UTF-8 encoding since it may be retrieved from index).
     *
     * @var string
     */
    private $_encoding;


    /**
     * Field name.
     *
     * @var string
     */
    private $_field;

    /**
     * Class constructor.  Create a new preprocessing object for prase query.
     *
     * @param string $word       Non-tokenized word (query parser lexeme) to search.
     * @param string $encoding   Word encoding.
     * @param string $fieldName  Field name.
     */
    public function __construct($word, $encoding, $fieldName)
    {
        $this->_word     = $word;
        $this->_encoding = $encoding;
        $this->_field    = $fieldName;
    }

    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        if ($this->_field === null) {
            #require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';
            $query = new Zend_Search_Lucene_Search_Query_MultiTerm();
            $query->setBoost($this->getBoost());

            $hasInsignificantSubqueries = false;

            #require_once 'Zend/Search/Lucene.php';
            if (Zend_Search_Lucene::getDefaultSearchField() === null) {
                $searchFields = $index->getFieldNames(true);
            } else {
                $searchFields = array(Zend_Search_Lucene::getDefaultSearchField());
            }

            #require_once 'Zend/Search/Lucene/Search/Query/Preprocessing/Term.php';
            foreach ($searchFields as $fieldName) {
                $subquery = new Zend_Search_Lucene_Search_Query_Preprocessing_Term($this->_word,
                                                                                   $this->_encoding,
                                                                                   $fieldName);
                $rewrittenSubquery = $subquery->rewrite($index);
                foreach ($rewrittenSubquery->getQueryTerms() as $term) {
                    $query->addTerm($term);
                }

                if ($rewrittenSubquery instanceof Zend_Search_Lucene_Search_Query_Insignificant) {
                    $hasInsignificantSubqueries = true;
                }
            }

            if (count($query->getTerms()) == 0) {
                $this->_matches = array();
                if ($hasInsignificantSubqueries) {
                    #require_once 'Zend/Search/Lucene/Search/Query/Insignificant.php';
                    return new Zend_Search_Lucene_Search_Query_Insignificant();
                } else {
                    #require_once 'Zend/Search/Lucene/Search/Query/Empty.php';
                    return new Zend_Search_Lucene_Search_Query_Empty();
                }
            }

            $this->_matches = $query->getQueryTerms();
            return $query;
        }

        // -------------------------------------
        // Recognize exact term matching (it corresponds to Keyword fields stored in the index)
        // encoding is not used since we expect binary matching
        #require_once 'Zend/Search/Lucene/Index/Term.php';
        $term = new Zend_Search_Lucene_Index_Term($this->_word, $this->_field);
        if ($index->hasTerm($term)) {
            #require_once 'Zend/Search/Lucene/Search/Query/Term.php';
            $query = new Zend_Search_Lucene_Search_Query_Term($term);
            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }


        // -------------------------------------
        // Recognize wildcard queries

        /** @todo check for PCRE unicode support may be performed through Zend_Environment in some future */
        if (@preg_match('/\pL/u', 'a') == 1) {
            $word = iconv($this->_encoding, 'UTF-8', $this->_word);
            $wildcardsPattern = '/[*?]/u';
            $subPatternsEncoding = 'UTF-8';
        } else {
            $word = $this->_word;
            $wildcardsPattern = '/[*?]/';
            $subPatternsEncoding = $this->_encoding;
        }

        $subPatterns = preg_split($wildcardsPattern, $word, -1, PREG_SPLIT_OFFSET_CAPTURE);

        if (count($subPatterns) > 1) {
            // Wildcard query is recognized

            $pattern = '';

            #require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';
            foreach ($subPatterns as $id => $subPattern) {
                // Append corresponding wildcard character to the pattern before each sub-pattern (except first)
                if ($id != 0) {
                    $pattern .= $word[ $subPattern[1] - 1 ];
                }

                // Check if each subputtern is a single word in terms of current analyzer
                $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($subPattern[0], $subPatternsEncoding);
                if (count($tokens) > 1) {
                    #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
                    throw new Zend_Search_Lucene_Search_QueryParserException('Wildcard search is supported only for non-multiple word terms');
                }
                foreach ($tokens as $token) {
                    $pattern .= $token->getTermText();
                }
            }

            #require_once 'Zend/Search/Lucene/Index/Term.php';
            $term  = new Zend_Search_Lucene_Index_Term($pattern, $this->_field);
            #require_once 'Zend/Search/Lucene/Search/Query/Wildcard.php';
            $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);
            $query->setBoost($this->getBoost());

            // Get rewritten query. Important! It also fills terms matching container.
            $rewrittenQuery = $query->rewrite($index);
            $this->_matches = $query->getQueryTerms();

            return $rewrittenQuery;
        }


        // -------------------------------------
        // Recognize one-term multi-term and "insignificant" queries
        #require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';
        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_word, $this->_encoding);

        if (count($tokens) == 0) {
            $this->_matches = array();
            #require_once 'Zend/Search/Lucene/Search/Query/Insignificant.php';
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }

        if (count($tokens) == 1) {
            #require_once 'Zend/Search/Lucene/Index/Term.php';
            $term  = new Zend_Search_Lucene_Index_Term($tokens[0]->getTermText(), $this->_field);
            #require_once 'Zend/Search/Lucene/Search/Query/Term.php';
            $query = new Zend_Search_Lucene_Search_Query_Term($term);
            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }

        //It's not insignificant or one term query
        #require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';
        $query = new Zend_Search_Lucene_Search_Query_MultiTerm();

        /**
         * @todo Process $token->getPositionIncrement() to support stemming, synonyms and other
         * analizer design features
         */
        #require_once 'Zend/Search/Lucene/Index/Term.php';
        foreach ($tokens as $token) {
            $term = new Zend_Search_Lucene_Index_Term($token->getTermText(), $this->_field);
            $query->addTerm($term, true); // all subterms are required
        }

        $query->setBoost($this->getBoost());

        $this->_matches = $query->getQueryTerms();
        return $query;
    }

    /**
     * Query specific matches highlighting
     *
     * @param Zend_Search_Lucene_Search_Highlighter_Interface $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Zend_Search_Lucene_Search_Highlighter_Interface $highlighter)
    {
        /** Skip fields detection. We don't need it, since we expect all fields presented in the HTML body and don't differentiate them */

        /** Skip exact term matching recognition, keyword fields highlighting is not supported */

        // -------------------------------------
        // Recognize wildcard queries
        /** @todo check for PCRE unicode support may be performed through Zend_Environment in some future */
        if (@preg_match('/\pL/u', 'a') == 1) {
            $word = iconv($this->_encoding, 'UTF-8', $this->_word);
            $wildcardsPattern = '/[*?]/u';
            $subPatternsEncoding = 'UTF-8';
        } else {
            $word = $this->_word;
            $wildcardsPattern = '/[*?]/';
            $subPatternsEncoding = $this->_encoding;
        }
        $subPatterns = preg_split($wildcardsPattern, $word, -1, PREG_SPLIT_OFFSET_CAPTURE);
        if (count($subPatterns) > 1) {
            // Wildcard query is recognized

            $pattern = '';

            #require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';
            foreach ($subPatterns as $id => $subPattern) {
                // Append corresponding wildcard character to the pattern before each sub-pattern (except first)
                if ($id != 0) {
                    $pattern .= $word[ $subPattern[1] - 1 ];
                }

                // Check if each subputtern is a single word in terms of current analyzer
                $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($subPattern[0], $subPatternsEncoding);
                if (count($tokens) > 1) {
                    // Do nothing (nothing is highlighted)
                    return;
                }
                foreach ($tokens as $token) {
                    $pattern .= $token->getTermText();
                }
            }

            #require_once 'Zend/Search/Lucene/Index/Term.php';
            $term  = new Zend_Search_Lucene_Index_Term($pattern, $this->_field);
            #require_once 'Zend/Search/Lucene/Search/Query/Wildcard.php';
            $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);

            $query->_highlightMatches($highlighter);
            return;
        }


        // -------------------------------------
        // Recognize one-term multi-term and "insignificant" queries
        #require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';
        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_word, $this->_encoding);

        if (count($tokens) == 0) {
            // Do nothing
            return;
        }

        if (count($tokens) == 1) {
            $highlighter->highlight($tokens[0]->getTermText());
            return;
        }

        //It's not insignificant or one term query
        $words = array();
        foreach ($tokens as $token) {
            $words[] = $token->getTermText();
        }
        $highlighter->highlight($words);
    }

    /**
     * Print a query
     *
     * @return string
     */
    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping
        if ($this->_field !== null) {
            $query = $this->_field . ':';
        } else {
            $query = '';
        }

        $query .= $this->_word;

        if ($this->getBoost() != 1) {
            $query .= '^' . round($this->getBoost(), 4);
        }

        return $query;
    }
}
