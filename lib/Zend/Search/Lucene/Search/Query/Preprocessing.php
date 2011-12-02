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
 * @version    $Id: Preprocessing.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Search_Lucene_Search_Query */
#require_once 'Zend/Search/Lucene/Search/Query.php';


/**
 * It's an internal abstract class intended to finalize ase a query processing after query parsing.
 * This type of query is not actually involved into query execution.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @internal
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_Search_Query_Preprocessing extends Zend_Search_Lucene_Search_Query
{
    /**
     * Matched terms.
     *
     * Matched terms list.
     * It's filled during rewrite operation and may be used for search result highlighting
     *
     * Array of Zend_Search_Lucene_Index_Term objects
     *
     * @var array
     */
    protected $_matches = null;

    /**
     * Optimize query in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('This query is not intended to be executed.');
    }

    /**
     * Constructs an appropriate Weight implementation for this query.
     *
     * @param Zend_Search_Lucene_Interface $reader
     * @return Zend_Search_Lucene_Search_Weight
     */
    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('This query is not intended to be executed.');
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
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('This query is not intended to be executed.');
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
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('This query is not intended to be executed.');
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
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('This query is not intended to be executed.');
    }

    /**
     * Return query terms
     *
     * @return array
     */
    public function getQueryTerms()
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Rewrite operation has to be done before retrieving query terms.');
    }
}

