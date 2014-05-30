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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Boolean.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Search_Lucene_Search_Query */
#require_once 'Zend/Search/Lucene/Search/Query.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Query_Boolean extends Zend_Search_Lucene_Search_Query
{

    /**
     * Subqueries
     * Array of Zend_Search_Lucene_Search_Query
     *
     * @var array
     */
    private $_subqueries = array();

    /**
     * Subqueries signs.
     * If true then subquery is required.
     * If false then subquery is prohibited.
     * If null then subquery is neither prohibited, nor required
     *
     * If array is null then all subqueries are required
     *
     * @var array
     */
    private $_signs = array();

    /**
     * Result vector.
     *
     * @var array
     */
    private $_resVector = null;

    /**
     * A score factor based on the fraction of all query subqueries
     * that a document contains.
     * float for conjunction queries
     * array of float for non conjunction queries
     *
     * @var mixed
     */
    private $_coord = null;


    /**
     * Class constructor.  Create a new Boolean query object.
     *
     * if $signs array is omitted then all subqueries are required
     * it differs from addSubquery() behavior, but should never be used
     *
     * @param array $subqueries    Array of Zend_Search_Search_Query objects
     * @param array $signs    Array of signs.  Sign is boolean|null.
     * @return void
     */
    public function __construct($subqueries = null, $signs = null)
    {
        if (is_array($subqueries)) {
            $this->_subqueries = $subqueries;

            $this->_signs = null;
            // Check if all subqueries are required
            if (is_array($signs)) {
                foreach ($signs as $sign ) {
                    if ($sign !== true) {
                        $this->_signs = $signs;
                        break;
                    }
                }
            }
        }
    }


    /**
     * Add a $subquery (Zend_Search_Lucene_Search_Query) to this query.
     *
     * The sign is specified as:
     *     TRUE  - subquery is required
     *     FALSE - subquery is prohibited
     *     NULL  - subquery is neither prohibited, nor required
     *
     * @param  Zend_Search_Lucene_Search_Query $subquery
     * @param  boolean|null $sign
     * @return void
     */
    public function addSubquery(Zend_Search_Lucene_Search_Query $subquery, $sign=null) {
        if ($sign !== true || $this->_signs !== null) {       // Skip, if all subqueries are required
            if ($this->_signs === null) {                     // Check, If all previous subqueries are required
                $this->_signs = array();
                foreach ($this->_subqueries as $prevSubquery) {
                    $this->_signs[] = true;
                }
            }
            $this->_signs[] = $sign;
        }

        $this->_subqueries[] = $subquery;
    }

    /**
     * Re-write queries into primitive queries
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        $query = new Zend_Search_Lucene_Search_Query_Boolean();
        $query->setBoost($this->getBoost());

        foreach ($this->_subqueries as $subqueryId => $subquery) {
            $query->addSubquery($subquery->rewrite($index),
                                ($this->_signs === null)?  true : $this->_signs[$subqueryId]);
        }

        return $query;
    }

    /**
     * Optimize query in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        $subqueries = array();
        $signs      = array();

        // Optimize all subqueries
        foreach ($this->_subqueries as $id => $subquery) {
            $subqueries[] = $subquery->optimize($index);
            $signs[]      = ($this->_signs === null)? true : $this->_signs[$id];
        }

        // Remove insignificant subqueries
        foreach ($subqueries as $id => $subquery) {
            if ($subquery instanceof Zend_Search_Lucene_Search_Query_Insignificant) {
                // Insignificant subquery has to be removed anyway
                unset($subqueries[$id]);
                unset($signs[$id]);
            }
        }
        if (count($subqueries) == 0) {
            // Boolean query doesn't has non-insignificant subqueries
            #require_once 'Zend/Search/Lucene/Search/Query/Insignificant.php';
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }
        // Check if all non-insignificant subqueries are prohibited
        $allProhibited = true;
        foreach ($signs as $sign) {
            if ($sign !== false) {
                $allProhibited = false;
                break;
            }
        }
        if ($allProhibited) {
            #require_once 'Zend/Search/Lucene/Search/Query/Insignificant.php';
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }


        // Check for empty subqueries
        foreach ($subqueries as $id => $subquery) {
            if ($subquery instanceof Zend_Search_Lucene_Search_Query_Empty) {
                if ($signs[$id] === true) {
                    // Matching is required, but is actually empty
                    #require_once 'Zend/Search/Lucene/Search/Query/Empty.php';
                    return new Zend_Search_Lucene_Search_Query_Empty();
                } else {
                    // Matching is optional or prohibited, but is empty
                    // Remove it from subqueries and signs list
                    unset($subqueries[$id]);
                    unset($signs[$id]);
                }
            }
        }

        // Check, if reduced subqueries list is empty
        if (count($subqueries) == 0) {
            #require_once 'Zend/Search/Lucene/Search/Query/Empty.php';
            return new Zend_Search_Lucene_Search_Query_Empty();
        }

        // Check if all non-empty subqueries are prohibited
        $allProhibited = true;
        foreach ($signs as $sign) {
            if ($sign !== false) {
                $allProhibited = false;
                break;
            }
        }
        if ($allProhibited) {
            #require_once 'Zend/Search/Lucene/Search/Query/Empty.php';
            return new Zend_Search_Lucene_Search_Query_Empty();
        }


        // Check, if reduced subqueries list has only one entry
        if (count($subqueries) == 1) {
            // It's a query with only one required or optional clause
            // (it's already checked, that it's not a prohibited clause)

            if ($this->getBoost() == 1) {
                return reset($subqueries);
            }

            $optimizedQuery = clone reset($subqueries);
            $optimizedQuery->setBoost($optimizedQuery->getBoost()*$this->getBoost());

            return $optimizedQuery;
        }


        // Prepare first candidate for optimized query
        $optimizedQuery = new Zend_Search_Lucene_Search_Query_Boolean($subqueries, $signs);
        $optimizedQuery->setBoost($this->getBoost());


        $terms        = array();
        $tsigns       = array();
        $boostFactors = array();

        // Try to decompose term and multi-term subqueries
        foreach ($subqueries as $id => $subquery) {
            if ($subquery instanceof Zend_Search_Lucene_Search_Query_Term) {
                $terms[]        = $subquery->getTerm();
                $tsigns[]       = $signs[$id];
                $boostFactors[] = $subquery->getBoost();

                // remove subquery from a subqueries list
                unset($subqueries[$id]);
                unset($signs[$id]);
           } else if ($subquery instanceof Zend_Search_Lucene_Search_Query_MultiTerm) {
                $subTerms = $subquery->getTerms();
                $subSigns = $subquery->getSigns();

                if ($signs[$id] === true) {
                    // It's a required multi-term subquery.
                    // Something like '... +(+term1 -term2 term3 ...) ...'

                    // Multi-term required subquery can be decomposed only if it contains
                    // required terms and doesn't contain prohibited terms:
                    // ... +(+term1 term2 ...) ... => ... +term1 term2 ...
                    //
                    // Check this
                    $hasRequired   = false;
                    $hasProhibited = false;
                    if ($subSigns === null) {
                        // All subterms are required
                        $hasRequired = true;
                    } else {
                        foreach ($subSigns as $sign) {
                            if ($sign === true) {
                                $hasRequired   = true;
                            } else if ($sign === false) {
                                $hasProhibited = true;
                                break;
                            }
                        }
                    }
                    // Continue if subquery has prohibited terms or doesn't have required terms
                    if ($hasProhibited  ||  !$hasRequired) {
                        continue;
                    }

                    foreach ($subTerms as $termId => $term) {
                        $terms[]        = $term;
                        $tsigns[]       = ($subSigns === null)? true : $subSigns[$termId];
                        $boostFactors[] = $subquery->getBoost();
                    }

                    // remove subquery from a subqueries list
                    unset($subqueries[$id]);
                    unset($signs[$id]);

                } else { // $signs[$id] === null  ||  $signs[$id] === false
                    // It's an optional or prohibited multi-term subquery.
                    // Something like '... (+term1 -term2 term3 ...) ...'
                    // or
                    // something like '... -(+term1 -term2 term3 ...) ...'

                    // Multi-term optional and required subqueries can be decomposed
                    // only if all terms are optional.
                    //
                    // Check if all terms are optional.
                    $onlyOptional = true;
                    if ($subSigns === null) {
                        // All subterms are required
                        $onlyOptional = false;
                    } else {
                        foreach ($subSigns as $sign) {
                            if ($sign !== null) {
                                $onlyOptional = false;
                                break;
                            }
                        }
                    }

                    // Continue if non-optional terms are presented in this multi-term subquery
                    if (!$onlyOptional) {
                        continue;
                    }

                    foreach ($subTerms as $termId => $term) {
                        $terms[]  = $term;
                        $tsigns[] = ($signs[$id] === null)? null  /* optional */ :
                                                            false /* prohibited */;
                        $boostFactors[] = $subquery->getBoost();
                    }

                    // remove subquery from a subqueries list
                    unset($subqueries[$id]);
                    unset($signs[$id]);
                }
            }
        }


        // Check, if there are no decomposed subqueries
        if (count($terms) == 0 ) {
            // return prepared candidate
            return $optimizedQuery;
        }


        // Check, if all subqueries have been decomposed and all terms has the same boost factor
        if (count($subqueries) == 0  &&  count(array_unique($boostFactors)) == 1) {
            #require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';
            $optimizedQuery = new Zend_Search_Lucene_Search_Query_MultiTerm($terms, $tsigns);
            $optimizedQuery->setBoost(reset($boostFactors)*$this->getBoost());

            return $optimizedQuery;
        }


        // This boolean query can't be transformed to Term/MultiTerm query and still contains
        // several subqueries

        // Separate prohibited terms
        $prohibitedTerms        = array();
        foreach ($terms as $id => $term) {
            if ($tsigns[$id] === false) {
                $prohibitedTerms[]        = $term;

                unset($terms[$id]);
                unset($tsigns[$id]);
                unset($boostFactors[$id]);
            }
        }

        if (count($terms) == 1) {
            #require_once 'Zend/Search/Lucene/Search/Query/Term.php';
            $clause = new Zend_Search_Lucene_Search_Query_Term(reset($terms));
            $clause->setBoost(reset($boostFactors));

            $subqueries[] = $clause;
            $signs[]      = reset($tsigns);

            // Clear terms list
            $terms = array();
        } else if (count($terms) > 1  &&  count(array_unique($boostFactors)) == 1) {
            #require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';
            $clause = new Zend_Search_Lucene_Search_Query_MultiTerm($terms, $tsigns);
            $clause->setBoost(reset($boostFactors));

            $subqueries[] = $clause;
            // Clause sign is 'required' if clause contains required terms. 'Optional' otherwise.
            $signs[]      = (in_array(true, $tsigns))? true : null;

            // Clear terms list
            $terms = array();
        }

        if (count($prohibitedTerms) == 1) {
            // (boost factors are not significant for prohibited clauses)
            #require_once 'Zend/Search/Lucene/Search/Query/Term.php';
            $subqueries[] = new Zend_Search_Lucene_Search_Query_Term(reset($prohibitedTerms));
            $signs[]      = false;

            // Clear prohibited terms list
            $prohibitedTerms = array();
        } else if (count($prohibitedTerms) > 1) {
            // prepare signs array
            $prohibitedSigns = array();
            foreach ($prohibitedTerms as $id => $term) {
                // all prohibited term are grouped as optional into multi-term query
                $prohibitedSigns[$id] = null;
            }

            // (boost factors are not significant for prohibited clauses)
            #require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';
            $subqueries[] = new Zend_Search_Lucene_Search_Query_MultiTerm($prohibitedTerms, $prohibitedSigns);
            // Clause sign is 'prohibited'
            $signs[]      = false;

            // Clear terms list
            $prohibitedTerms = array();
        }

        /** @todo Group terms with the same boost factors together */

        // Check, that all terms are processed
        // Replace candidate for optimized query
        if (count($terms) == 0  &&  count($prohibitedTerms) == 0) {
            $optimizedQuery = new Zend_Search_Lucene_Search_Query_Boolean($subqueries, $signs);
            $optimizedQuery->setBoost($this->getBoost());
        }

        return $optimizedQuery;
    }

    /**
     * Returns subqueries
     *
     * @return array
     */
    public function getSubqueries()
    {
        return $this->_subqueries;
    }


    /**
     * Return subqueries signs
     *
     * @return array
     */
    public function getSigns()
    {
        return $this->_signs;
    }


    /**
     * Constructs an appropriate Weight implementation for this query.
     *
     * @param Zend_Search_Lucene_Interface $reader
     * @return Zend_Search_Lucene_Search_Weight
     */
    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        #require_once 'Zend/Search/Lucene/Search/Weight/Boolean.php';
        $this->_weight = new Zend_Search_Lucene_Search_Weight_Boolean($this, $reader);
        return $this->_weight;
    }


    /**
     * Calculate result vector for Conjunction query
     * (like '<subquery1> AND <subquery2> AND <subquery3>')
     */
    private function _calculateConjunctionResult()
    {
        $this->_resVector = null;

        if (count($this->_subqueries) == 0) {
            $this->_resVector = array();
        }

        $resVectors      = array();
        $resVectorsSizes = array();
        $resVectorsIds   = array(); // is used to prevent arrays comparison
        foreach ($this->_subqueries as $subqueryId => $subquery) {
            $resVectors[]      = $subquery->matchedDocs();
            $resVectorsSizes[] = count(end($resVectors));
            $resVectorsIds[]   = $subqueryId;
        }
        // sort resvectors in order of subquery cardinality increasing
        array_multisort($resVectorsSizes, SORT_ASC, SORT_NUMERIC,
                        $resVectorsIds,   SORT_ASC, SORT_NUMERIC,
                        $resVectors);

        foreach ($resVectors as $nextResVector) {
            if($this->_resVector === null) {
                $this->_resVector = $nextResVector;
            } else {
                //$this->_resVector = array_intersect_key($this->_resVector, $nextResVector);

                /**
                 * This code is used as workaround for array_intersect_key() slowness problem.
                 */
                $updatedVector = array();
                foreach ($this->_resVector as $id => $value) {
                    if (isset($nextResVector[$id])) {
                        $updatedVector[$id] = $value;
                    }
                }
                $this->_resVector = $updatedVector;
            }

            if (count($this->_resVector) == 0) {
                // Empty result set, we don't need to check other terms
                break;
            }
        }

        // ksort($this->_resVector, SORT_NUMERIC);
        // Used algorithm doesn't change elements order
    }


    /**
     * Calculate result vector for non Conjunction query
     * (like '<subquery1> AND <subquery2> AND NOT <subquery3> OR <subquery4>')
     */
    private function _calculateNonConjunctionResult()
    {
        $requiredVectors      = array();
        $requiredVectorsSizes = array();
        $requiredVectorsIds   = array(); // is used to prevent arrays comparison

        $optional = array();

        foreach ($this->_subqueries as $subqueryId => $subquery) {
            if ($this->_signs[$subqueryId] === true) {
                // required
                $requiredVectors[]      = $subquery->matchedDocs();
                $requiredVectorsSizes[] = count(end($requiredVectors));
                $requiredVectorsIds[]   = $subqueryId;
            } elseif ($this->_signs[$subqueryId] === false) {
                // prohibited
                // Do nothing. matchedDocs() may include non-matching id's
                // Calculating prohibited vector may take significant time, but do not affect the result
                // Skipped.
            } else {
                // neither required, nor prohibited
                // array union
                $optional += $subquery->matchedDocs();
            }
        }

        // sort resvectors in order of subquery cardinality increasing
        array_multisort($requiredVectorsSizes, SORT_ASC, SORT_NUMERIC,
                        $requiredVectorsIds,   SORT_ASC, SORT_NUMERIC,
                        $requiredVectors);

        $required = null;
        foreach ($requiredVectors as $nextResVector) {
            if($required === null) {
                $required = $nextResVector;
            } else {
                //$required = array_intersect_key($required, $nextResVector);

                /**
                 * This code is used as workaround for array_intersect_key() slowness problem.
                 */
                $updatedVector = array();
                foreach ($required as $id => $value) {
                    if (isset($nextResVector[$id])) {
                        $updatedVector[$id] = $value;
                    }
                }
                $required = $updatedVector;
            }

            if (count($required) == 0) {
                // Empty result set, we don't need to check other terms
                break;
            }
        }


        if ($required !== null) {
            $this->_resVector = &$required;
        } else {
            $this->_resVector = &$optional;
        }

        ksort($this->_resVector, SORT_NUMERIC);
    }


    /**
     * Score calculator for conjunction queries (all subqueries are required)
     *
     * @param integer $docId
     * @param Zend_Search_Lucene_Interface $reader
     * @return float
     */
    public function _conjunctionScore($docId, Zend_Search_Lucene_Interface $reader)
    {
        if ($this->_coord === null) {
            $this->_coord = $reader->getSimilarity()->coord(count($this->_subqueries),
                                                            count($this->_subqueries) );
        }

        $score = 0;

        foreach ($this->_subqueries as $subquery) {
            $subscore = $subquery->score($docId, $reader);

            if ($subscore == 0) {
                return 0;
            }

            $score += $subquery->score($docId, $reader) * $this->_coord;
        }

        return $score * $this->_coord * $this->getBoost();
    }


    /**
     * Score calculator for non conjunction queries (not all subqueries are required)
     *
     * @param integer $docId
     * @param Zend_Search_Lucene_Interface $reader
     * @return float
     */
    public function _nonConjunctionScore($docId, Zend_Search_Lucene_Interface $reader)
    {
        if ($this->_coord === null) {
            $this->_coord = array();

            $maxCoord = 0;
            foreach ($this->_signs as $sign) {
                if ($sign !== false /* not prohibited */) {
                    $maxCoord++;
                }
            }

            for ($count = 0; $count <= $maxCoord; $count++) {
                $this->_coord[$count] = $reader->getSimilarity()->coord($count, $maxCoord);
            }
        }

        $score = 0;
        $matchedSubqueries = 0;
        foreach ($this->_subqueries as $subqueryId => $subquery) {
            $subscore = $subquery->score($docId, $reader);

            // Prohibited
            if ($this->_signs[$subqueryId] === false && $subscore != 0) {
                return 0;
            }

            // is required, but doen't match
            if ($this->_signs[$subqueryId] === true &&  $subscore == 0) {
                return 0;
            }

            if ($subscore != 0) {
                $matchedSubqueries++;
                $score += $subscore;
            }
        }

        return $score * $this->_coord[$matchedSubqueries] * $this->getBoost();
    }

    /**
     * Execute query in context of index reader
     * It also initializes necessary internal structures
     *
     * @param Zend_Search_Lucene_Interface $reader
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     */
    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        // Initialize weight if it's not done yet
        $this->_initWeight($reader);

        if ($docsFilter === null) {
            // Create local documents filter if it's not provided by upper query
            #require_once 'Zend/Search/Lucene/Index/DocsFilter.php';
            $docsFilter = new Zend_Search_Lucene_Index_DocsFilter();
        }

        foreach ($this->_subqueries as $subqueryId => $subquery) {
            if ($this->_signs == null  ||  $this->_signs[$subqueryId] === true) {
                // Subquery is required
                $subquery->execute($reader, $docsFilter);
            } else {
                $subquery->execute($reader);
            }
        }

        if ($this->_signs === null) {
            $this->_calculateConjunctionResult();
        } else {
            $this->_calculateNonConjunctionResult();
        }
    }



    /**
     * Get document ids likely matching the query
     *
     * It's an array with document ids as keys (performance considerations)
     *
     * @return array
     */
    public function matchedDocs()
    {
        return $this->_resVector;
    }

    /**
     * Score specified document
     *
     * @param integer $docId
     * @param Zend_Search_Lucene_Interface $reader
     * @return float
     */
    public function score($docId, Zend_Search_Lucene_Interface $reader)
    {
        if (isset($this->_resVector[$docId])) {
            if ($this->_signs === null) {
                return $this->_conjunctionScore($docId, $reader);
            } else {
                return $this->_nonConjunctionScore($docId, $reader);
            }
        } else {
            return 0;
        }
    }

    /**
     * Return query terms
     *
     * @return array
     */
    public function getQueryTerms()
    {
        $terms = array();

        foreach ($this->_subqueries as $id => $subquery) {
            if ($this->_signs === null  ||  $this->_signs[$id] !== false) {
                $terms = array_merge($terms, $subquery->getQueryTerms());
            }
        }

        return $terms;
    }

    /**
     * Query specific matches highlighting
     *
     * @param Zend_Search_Lucene_Search_Highlighter_Interface $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Zend_Search_Lucene_Search_Highlighter_Interface $highlighter)
    {
        foreach ($this->_subqueries as $id => $subquery) {
            if ($this->_signs === null  ||  $this->_signs[$id] !== false) {
                $subquery->_highlightMatches($highlighter);
            }
        }
    }

    /**
     * Print a query
     *
     * @return string
     */
    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping

        $query = '';

        foreach ($this->_subqueries as $id => $subquery) {
            if ($id != 0) {
                $query .= ' ';
            }

            if ($this->_signs === null || $this->_signs[$id] === true) {
                $query .= '+';
            } else if ($this->_signs[$id] === false) {
                $query .= '-';
            }

            $query .= '(' . $subquery->__toString() . ')';
        }

        if ($this->getBoost() != 1) {
            $query = '(' . $query . ')^' . round($this->getBoost(), 4);
        }

        return $query;
    }
}

