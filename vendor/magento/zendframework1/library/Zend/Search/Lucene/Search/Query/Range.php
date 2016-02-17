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


/** Zend_Search_Lucene_Search_Query */
#require_once 'Zend/Search/Lucene/Search/Query.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Query_Range extends Zend_Search_Lucene_Search_Query
{
    /**
     * Lower term.
     *
     * @var Zend_Search_Lucene_Index_Term
     */
    private $_lowerTerm;

    /**
     * Upper term.
     *
     * @var Zend_Search_Lucene_Index_Term
     */
    private $_upperTerm;


    /**
     * Search field
     *
     * @var string
     */
    private $_field;

    /**
     * Inclusive
     *
     * @var boolean
     */
    private $_inclusive;

    /**
     * Matched terms.
     *
     * Matched terms list.
     * It's filled during the search (rewrite operation) and may be used for search result
     * post-processing
     *
     * Array of Zend_Search_Lucene_Index_Term objects
     *
     * @var array
     */
    private $_matches = null;


    /**
     * Zend_Search_Lucene_Search_Query_Range constructor.
     *
     * @param Zend_Search_Lucene_Index_Term|null $lowerTerm
     * @param Zend_Search_Lucene_Index_Term|null $upperTerm
     * @param boolean $inclusive
     * @throws Zend_Search_Lucene_Exception
     */
    public function __construct($lowerTerm, $upperTerm, $inclusive)
    {
        if ($lowerTerm === null  &&  $upperTerm === null) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('At least one term must be non-null');
        }
        if ($lowerTerm !== null  &&  $upperTerm !== null  &&  $lowerTerm->field != $upperTerm->field) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Both terms must be for the same field');
        }

        $this->_field     = ($lowerTerm !== null)? $lowerTerm->field : $upperTerm->field;
        $this->_lowerTerm = $lowerTerm;
        $this->_upperTerm = $upperTerm;
        $this->_inclusive = $inclusive;
    }

    /**
     * Get query field name
     *
     * @return string|null
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Get lower term
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function getLowerTerm()
    {
        return $this->_lowerTerm;
    }

    /**
     * Get upper term
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function getUpperTerm()
    {
        return $this->_upperTerm;
    }

    /**
     * Get upper term
     *
     * @return boolean
     */
    public function isInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        $this->_matches = array();

        if ($this->_field === null) {
            // Search through all fields
            $fields = $index->getFieldNames(true /* indexed fields list */);
        } else {
            $fields = array($this->_field);
        }

        #require_once 'Zend/Search/Lucene.php';
        $maxTerms = Zend_Search_Lucene::getTermsPerQueryLimit();
        foreach ($fields as $field) {
            $index->resetTermsStream();

            #require_once 'Zend/Search/Lucene/Index/Term.php';
            if ($this->_lowerTerm !== null) {
                $lowerTerm = new Zend_Search_Lucene_Index_Term($this->_lowerTerm->text, $field);

                $index->skipTo($lowerTerm);

                if (!$this->_inclusive  &&
                    $index->currentTerm() == $lowerTerm) {
                    // Skip lower term
                    $index->nextTerm();
                }
            } else {
                $index->skipTo(new Zend_Search_Lucene_Index_Term('', $field));
            }


            if ($this->_upperTerm !== null) {
                // Walk up to the upper term
                $upperTerm = new Zend_Search_Lucene_Index_Term($this->_upperTerm->text, $field);

                while ($index->currentTerm() !== null          &&
                       $index->currentTerm()->field == $field  &&
                       strcmp($index->currentTerm()->text, $upperTerm->text) < 0) {
                    $this->_matches[] = $index->currentTerm();

                    if ($maxTerms != 0  &&  count($this->_matches) > $maxTerms) {
                        #require_once 'Zend/Search/Lucene/Exception.php';
                        throw new Zend_Search_Lucene_Exception('Terms per query limit is reached.');
                    }

                    $index->nextTerm();
                }

                if ($this->_inclusive  &&  $index->currentTerm() == $upperTerm) {
                    // Include upper term into result
                    $this->_matches[] = $upperTerm;
                }
            } else {
                // Walk up to the end of field data
                while ($index->currentTerm() !== null  &&  $index->currentTerm()->field == $field) {
                    $this->_matches[] = $index->currentTerm();

                    if ($maxTerms != 0  &&  count($this->_matches) > $maxTerms) {
                        #require_once 'Zend/Search/Lucene/Exception.php';
                        throw new Zend_Search_Lucene_Exception('Terms per query limit is reached.');
                    }

                    $index->nextTerm();
                }
            }

            $index->closeTermsStream();
        }

        if (count($this->_matches) == 0) {
            #require_once 'Zend/Search/Lucene/Search/Query/Empty.php';
            return new Zend_Search_Lucene_Search_Query_Empty();
        } else if (count($this->_matches) == 1) {
            #require_once 'Zend/Search/Lucene/Search/Query/Term.php';
            return new Zend_Search_Lucene_Search_Query_Term(reset($this->_matches));
        } else {
            #require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';
            $rewrittenQuery = new Zend_Search_Lucene_Search_Query_MultiTerm();

            foreach ($this->_matches as $matchedTerm) {
                $rewrittenQuery->addTerm($matchedTerm);
            }

            return $rewrittenQuery;
        }
    }

    /**
     * Optimize query in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Return query terms
     *
     * @return array
     * @throws Zend_Search_Lucene_Exception
     */
    public function getQueryTerms()
    {
        if ($this->_matches === null) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Search or rewrite operations have to be performed before.');
        }

        return $this->_matches;
    }

    /**
     * Constructs an appropriate Weight implementation for this query.
     *
     * @param Zend_Search_Lucene_Interface $reader
     * @return Zend_Search_Lucene_Search_Weight
     * @throws Zend_Search_Lucene_Exception
     */
    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }


    /**
     * Execute query in context of index reader
     * It also initializes necessary internal structures
     *
     * @param Zend_Search_Lucene_Interface $reader
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @throws Zend_Search_Lucene_Exception
     */
    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Get document ids likely matching the query
     *
     * It's an array with document ids as keys (performance considerations)
     *
     * @return array
     * @throws Zend_Search_Lucene_Exception
     */
    public function matchedDocs()
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Score specified document
     *
     * @param integer $docId
     * @param Zend_Search_Lucene_Interface $reader
     * @return float
     * @throws Zend_Search_Lucene_Exception
     */
    public function score($docId, Zend_Search_Lucene_Interface $reader)
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Query specific matches highlighting
     *
     * @param Zend_Search_Lucene_Search_Highlighter_Interface $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Zend_Search_Lucene_Search_Highlighter_Interface $highlighter)
    {
        $words = array();

        $docBody = $highlighter->getDocument()->getFieldUtf8Value('body');
        #require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';
        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($docBody, 'UTF-8');

        $lowerTermText = ($this->_lowerTerm !== null)? $this->_lowerTerm->text : null;
        $upperTermText = ($this->_upperTerm !== null)? $this->_upperTerm->text : null;

        if ($this->_inclusive) {
            foreach ($tokens as $token) {
                $termText = $token->getTermText();
                if (($lowerTermText == null  ||  $lowerTermText <= $termText)  &&
                    ($upperTermText == null  ||  $termText <= $upperTermText)) {
                    $words[] = $termText;
                }
            }
        } else {
            foreach ($tokens as $token) {
                $termText = $token->getTermText();
                if (($lowerTermText == null  ||  $lowerTermText < $termText)  &&
                    ($upperTermText == null  ||  $termText < $upperTermText)) {
                    $words[] = $termText;
                }
            }
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
        return (($this->_field === null)? '' : $this->_field . ':')
             . (($this->_inclusive)? '[' : '{')
             . (($this->_lowerTerm !== null)?  $this->_lowerTerm->text : 'null')
             . ' TO '
             . (($this->_upperTerm !== null)?  $this->_upperTerm->text : 'null')
             . (($this->_inclusive)? ']' : '}')
             . (($this->getBoost() != 1)? '^' . round($this->getBoost(), 4) : '');
    }
}

