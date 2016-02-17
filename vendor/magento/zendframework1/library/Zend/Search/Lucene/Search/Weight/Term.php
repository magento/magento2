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


/** Zend_Search_Lucene_Search_Weight */
#require_once 'Zend/Search/Lucene/Search/Weight.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Weight_Term extends Zend_Search_Lucene_Search_Weight
{
    /**
     * IndexReader.
     *
     * @var Zend_Search_Lucene_Interface
     */
    private $_reader;

    /**
     * Term
     *
     * @var Zend_Search_Lucene_Index_Term
     */
    private $_term;

    /**
     * The query that this concerns.
     *
     * @var Zend_Search_Lucene_Search_Query
     */
    private $_query;

    /**
     * Score factor
     *
     * @var float
     */
    private $_idf;

    /**
     * Query weight
     *
     * @var float
     */
    private $_queryWeight;


    /**
     * Zend_Search_Lucene_Search_Weight_Term constructor
     * reader - index reader
     *
     * @param Zend_Search_Lucene_Index_Term   $term
     * @param Zend_Search_Lucene_Search_Query $query
     * @param Zend_Search_Lucene_Interface    $reader
     */
    public function __construct(Zend_Search_Lucene_Index_Term   $term,
                                Zend_Search_Lucene_Search_Query $query,
                                Zend_Search_Lucene_Interface    $reader)
    {
        $this->_term   = $term;
        $this->_query  = $query;
        $this->_reader = $reader;
    }


    /**
     * The sum of squared weights of contained query clauses.
     *
     * @return float
     */
    public function sumOfSquaredWeights()
    {
        // compute idf
        $this->_idf = $this->_reader->getSimilarity()->idf($this->_term, $this->_reader);

        // compute query weight
        $this->_queryWeight = $this->_idf * $this->_query->getBoost();

        // square it
        return $this->_queryWeight * $this->_queryWeight;
    }


    /**
     * Assigns the query normalization factor to this.
     *
     * @param float $queryNorm
     */
    public function normalize($queryNorm)
    {
        $this->_queryNorm = $queryNorm;

        // normalize query weight
        $this->_queryWeight *= $queryNorm;

        // idf for documents
        $this->_value = $this->_queryWeight * $this->_idf;
    }
}

