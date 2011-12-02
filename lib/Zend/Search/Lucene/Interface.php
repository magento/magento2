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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Search_Lucene_Index_TermsStream_Interface */
#require_once 'Zend/Search/Lucene/Index/TermsStream/Interface.php';


/** Classes used within Zend_Search_Lucene_Interface API */

/** Zend_Search_Lucene_Document */
#require_once 'Zend/Search/Lucene/Document.php';

/** Zend_Search_Lucene_Index_Term */
#require_once 'Zend/Search/Lucene/Index/Term.php';

/** Zend_Search_Lucene_Index_DocsFilter */
#require_once 'Zend/Search/Lucene/Index/DocsFilter.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Search_Lucene_Interface extends Zend_Search_Lucene_Index_TermsStream_Interface
{
    /**
     * Get current generation number
     *
     * Returns generation number
     * 0 means pre-2.1 index format
     * -1 means there are no segments files.
     *
     * @param Zend_Search_Lucene_Storage_Directory $directory
     * @return integer
     * @throws Zend_Search_Lucene_Exception
     */
    public static function getActualGeneration(Zend_Search_Lucene_Storage_Directory $directory);

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public static function getSegmentFileName($generation);

    /**
     * Get index format version
     *
     * @return integer
     */
    public function getFormatVersion();

    /**
     * Set index format version.
     * Index is converted to this format at the nearest upfdate time
     *
     * @param int $formatVersion
     * @throws Zend_Search_Lucene_Exception
     */
    public function setFormatVersion($formatVersion);

    /**
     * Returns the Zend_Search_Lucene_Storage_Directory instance for this index.
     *
     * @return Zend_Search_Lucene_Storage_Directory
     */
    public function getDirectory();

    /**
     * Returns the total number of documents in this index (including deleted documents).
     *
     * @return integer
     */
    public function count();

    /**
     * Returns one greater than the largest possible document number.
     * This may be used to, e.g., determine how big to allocate a structure which will have
     * an element for every document number in an index.
     *
     * @return integer
     */
    public function maxDoc();

    /**
     * Returns the total number of non-deleted documents in this index.
     *
     * @return integer
     */
    public function numDocs();

    /**
     * Checks, that document is deleted
     *
     * @param integer $id
     * @return boolean
     * @throws Zend_Search_Lucene_Exception    Exception is thrown if $id is out of the range
     */
    public function isDeleted($id);

    /**
     * Set default search field.
     *
     * Null means, that search is performed through all fields by default
     *
     * Default value is null
     *
     * @param string $fieldName
     */
    public static function setDefaultSearchField($fieldName);

    /**
     * Get default search field.
     *
     * Null means, that search is performed through all fields by default
     *
     * @return string
     */
    public static function getDefaultSearchField();

    /**
     * Set result set limit.
     *
     * 0 (default) means no limit
     *
     * @param integer $limit
     */
    public static function setResultSetLimit($limit);

    /**
     * Set result set limit.
     *
     * 0 means no limit
     *
     * @return integer
     */
    public static function getResultSetLimit();

    /**
     * Retrieve index maxBufferedDocs option
     *
     * maxBufferedDocs is a minimal number of documents required before
     * the buffered in-memory documents are written into a new Segment
     *
     * Default value is 10
     *
     * @return integer
     */
    public function getMaxBufferedDocs();

    /**
     * Set index maxBufferedDocs option
     *
     * maxBufferedDocs is a minimal number of documents required before
     * the buffered in-memory documents are written into a new Segment
     *
     * Default value is 10
     *
     * @param integer $maxBufferedDocs
     */
    public function setMaxBufferedDocs($maxBufferedDocs);

    /**
     * Retrieve index maxMergeDocs option
     *
     * maxMergeDocs is a largest number of documents ever merged by addDocument().
     * Small values (e.g., less than 10,000) are best for interactive indexing,
     * as this limits the length of pauses while indexing to a few seconds.
     * Larger values are best for batched indexing and speedier searches.
     *
     * Default value is PHP_INT_MAX
     *
     * @return integer
     */
    public function getMaxMergeDocs();

    /**
     * Set index maxMergeDocs option
     *
     * maxMergeDocs is a largest number of documents ever merged by addDocument().
     * Small values (e.g., less than 10,000) are best for interactive indexing,
     * as this limits the length of pauses while indexing to a few seconds.
     * Larger values are best for batched indexing and speedier searches.
     *
     * Default value is PHP_INT_MAX
     *
     * @param integer $maxMergeDocs
     */
    public function setMaxMergeDocs($maxMergeDocs);

    /**
     * Retrieve index mergeFactor option
     *
     * mergeFactor determines how often segment indices are merged by addDocument().
     * With smaller values, less RAM is used while indexing,
     * and searches on unoptimized indices are faster,
     * but indexing speed is slower.
     * With larger values, more RAM is used during indexing,
     * and while searches on unoptimized indices are slower,
     * indexing is faster.
     * Thus larger values (> 10) are best for batch index creation,
     * and smaller values (< 10) for indices that are interactively maintained.
     *
     * Default value is 10
     *
     * @return integer
     */
    public function getMergeFactor();

    /**
     * Set index mergeFactor option
     *
     * mergeFactor determines how often segment indices are merged by addDocument().
     * With smaller values, less RAM is used while indexing,
     * and searches on unoptimized indices are faster,
     * but indexing speed is slower.
     * With larger values, more RAM is used during indexing,
     * and while searches on unoptimized indices are slower,
     * indexing is faster.
     * Thus larger values (> 10) are best for batch index creation,
     * and smaller values (< 10) for indices that are interactively maintained.
     *
     * Default value is 10
     *
     * @param integer $maxMergeDocs
     */
    public function setMergeFactor($mergeFactor);

    /**
     * Performs a query against the index and returns an array
     * of Zend_Search_Lucene_Search_QueryHit objects.
     * Input is a string or Zend_Search_Lucene_Search_Query.
     *
     * @param mixed $query
     * @return array Zend_Search_Lucene_Search_QueryHit
     * @throws Zend_Search_Lucene_Exception
     */
    public function find($query);

    /**
     * Returns a list of all unique field names that exist in this index.
     *
     * @param boolean $indexed
     * @return array
     */
    public function getFieldNames($indexed = false);

    /**
     * Returns a Zend_Search_Lucene_Document object for the document
     * number $id in this index.
     *
     * @param integer|Zend_Search_Lucene_Search_QueryHit $id
     * @return Zend_Search_Lucene_Document
     */
    public function getDocument($id);

    /**
     * Returns true if index contain documents with specified term.
     *
     * Is used for query optimization.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @return boolean
     */
    public function hasTerm(Zend_Search_Lucene_Index_Term $term);

    /**
     * Returns IDs of all the documents containing term.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return array
     */
    public function termDocs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    /**
     * Returns documents filter for all documents containing term.
     *
     * It performs the same operation as termDocs, but return result as
     * Zend_Search_Lucene_Index_DocsFilter object
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return Zend_Search_Lucene_Index_DocsFilter
     */
    public function termDocsFilter(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    /**
     * Returns an array of all term freqs.
     * Return array structure: array( docId => freq, ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return integer
     */
    public function termFreqs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    /**
     * Returns an array of all term positions in the documents.
     * Return array structure: array( docId => array( pos1, pos2, ...), ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return array
     */
    public function termPositions(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    /**
     * Returns the number of documents in this index containing the $term.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @return integer
     */
    public function docFreq(Zend_Search_Lucene_Index_Term $term);

    /**
     * Retrive similarity used by index reader
     *
     * @return Zend_Search_Lucene_Search_Similarity
     */
    public function getSimilarity();

    /**
     * Returns a normalization factor for "field, document" pair.
     *
     * @param integer $id
     * @param string $fieldName
     * @return float
     */
    public function norm($id, $fieldName);

    /**
     * Returns true if any documents have been deleted from this index.
     *
     * @return boolean
     */
    public function hasDeletions();

    /**
     * Deletes a document from the index.
     * $id is an internal document id
     *
     * @param integer|Zend_Search_Lucene_Search_QueryHit $id
     * @throws Zend_Search_Lucene_Exception
     */
    public function delete($id);

    /**
     * Adds a document to this index.
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document);

    /**
     * Commit changes resulting from delete() or undeleteAll() operations.
     */
    public function commit();

    /**
     * Optimize index.
     *
     * Merges all segments into one
     */
    public function optimize();

    /**
     * Returns an array of all terms in this index.
     *
     * @return array
     */
    public function terms();

    /**
     * Undeletes all documents currently marked as deleted in this index.
     */
    public function undeleteAll();


    /**
     * Add reference to the index object
     *
     * @internal
     */
    public function addReference();

    /**
     * Remove reference from the index object
     *
     * When reference count becomes zero, index is closed and resources are cleaned up
     *
     * @internal
     */
    public function removeReference();
}
