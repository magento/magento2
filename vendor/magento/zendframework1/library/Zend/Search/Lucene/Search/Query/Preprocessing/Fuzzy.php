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
class Zend_Search_Lucene_Search_Query_Preprocessing_Fuzzy extends Zend_Search_Lucene_Search_Query_Preprocessing
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
     * A value between 0 and 1 to set the required similarity
     *  between the query term and the matching terms. For example, for a
     *  _minimumSimilarity of 0.5 a term of the same length
     *  as the query term is considered similar to the query term if the edit distance
     *  between both terms is less than length(term)*0.5
     *
     * @var float
     */
    private $_minimumSimilarity;

    /**
     * Class constructor.  Create a new preprocessing object for prase query.
     *
     * @param string $word       Non-tokenized word (query parser lexeme) to search.
     * @param string $encoding   Word encoding.
     * @param string $fieldName  Field name.
     * @param float  $minimumSimilarity minimum similarity
     */
    public function __construct($word, $encoding, $fieldName, $minimumSimilarity)
    {
        $this->_word     = $word;
        $this->_encoding = $encoding;
        $this->_field    = $fieldName;
        $this->_minimumSimilarity = $minimumSimilarity;
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
            #require_once 'Zend/Search/Lucene/Search/Query/Boolean.php';
            $query = new Zend_Search_Lucene_Search_Query_Boolean();

            $hasInsignificantSubqueries = false;

            #require_once 'Zend/Search/Lucene.php';
            if (Zend_Search_Lucene::getDefaultSearchField() === null) {
                $searchFields = $index->getFieldNames(true);
            } else {
                $searchFields = array(Zend_Search_Lucene::getDefaultSearchField());
            }

            #require_once 'Zend/Search/Lucene/Search/Query/Preprocessing/Fuzzy.php';
            foreach ($searchFields as $fieldName) {
                $subquery = new Zend_Search_Lucene_Search_Query_Preprocessing_Fuzzy($this->_word,
                                                                                    $this->_encoding,
                                                                                    $fieldName,
                                                                                    $this->_minimumSimilarity);

                $rewrittenSubquery = $subquery->rewrite($index);

                if ( !($rewrittenSubquery instanceof Zend_Search_Lucene_Search_Query_Insignificant  ||
                       $rewrittenSubquery instanceof Zend_Search_Lucene_Search_Query_Empty) ) {
                    $query->addSubquery($rewrittenSubquery);
                }

                if ($rewrittenSubquery instanceof Zend_Search_Lucene_Search_Query_Insignificant) {
                    $hasInsignificantSubqueries = true;
                }
            }

            $subqueries = $query->getSubqueries();

            if (count($subqueries) == 0) {
                $this->_matches = array();
                if ($hasInsignificantSubqueries) {
                    #require_once 'Zend/Search/Lucene/Search/Query/Insignificant.php';
                    return new Zend_Search_Lucene_Search_Query_Insignificant();
                } else {
                    #require_once 'Zend/Search/Lucene/Search/Query/Empty.php';
                    return new Zend_Search_Lucene_Search_Query_Empty();
                }
            }

            if (count($subqueries) == 1) {
                $query = reset($subqueries);
            }

            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }

        // -------------------------------------
        // Recognize exact term matching (it corresponds to Keyword fields stored in the index)
        // encoding is not used since we expect binary matching
        #require_once 'Zend/Search/Lucene/Index/Term.php';
        $term = new Zend_Search_Lucene_Index_Term($this->_word, $this->_field);
        if ($index->hasTerm($term)) {
            #require_once 'Zend/Search/Lucene/Search/Query/Fuzzy.php';
            $query = new Zend_Search_Lucene_Search_Query_Fuzzy($term, $this->_minimumSimilarity);
            $query->setBoost($this->getBoost());

            // Get rewritten query. Important! It also fills terms matching container.
            $rewrittenQuery = $query->rewrite($index);
            $this->_matches = $query->getQueryTerms();

            return $rewrittenQuery;
        }


        // -------------------------------------
        // Recognize wildcard queries

        /** @todo check for PCRE unicode support may be performed through Zend_Environment in some future */
        if (@preg_match('/\pL/u', 'a') == 1) {
            $subPatterns = preg_split('/[*?]/u', iconv($this->_encoding, 'UTF-8', $this->_word));
        } else {
            $subPatterns = preg_split('/[*?]/', $this->_word);
        }
        if (count($subPatterns) > 1) {
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Fuzzy search doesn\'t support wildcards (except within Keyword fields).');
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
            #require_once 'Zend/Search/Lucene/Search/Query/Fuzzy.php';
            $query = new Zend_Search_Lucene_Search_Query_Fuzzy($term, $this->_minimumSimilarity);
            $query->setBoost($this->getBoost());

            // Get rewritten query. Important! It also fills terms matching container.
            $rewrittenQuery = $query->rewrite($index);
            $this->_matches = $query->getQueryTerms();

            return $rewrittenQuery;
        }

        // Word is tokenized into several tokens
        #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
        throw new Zend_Search_Lucene_Search_QueryParserException('Fuzzy search is supported only for non-multiple word terms');
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
            $subPatterns = preg_split('/[*?]/u', iconv($this->_encoding, 'UTF-8', $this->_word));
        } else {
            $subPatterns = preg_split('/[*?]/', $this->_word);
        }
        if (count($subPatterns) > 1) {
            // Do nothing
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
            #require_once 'Zend/Search/Lucene/Index/Term.php';
            $term  = new Zend_Search_Lucene_Index_Term($tokens[0]->getTermText(), $this->_field);
            #require_once 'Zend/Search/Lucene/Search/Query/Fuzzy.php';
            $query = new Zend_Search_Lucene_Search_Query_Fuzzy($term, $this->_minimumSimilarity);

            $query->_highlightMatches($highlighter);
            return;
        }

        // Word is tokenized into several tokens
        // But fuzzy search is supported only for non-multiple word terms
        // Do nothing
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
