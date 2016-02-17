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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Search_Lucene_Interface */
#require_once 'Zend/Search/Lucene/Interface.php';


/**
 * Proxy class intended to be used in userland.
 *
 * It tracks, when index object goes out of scope and forces ndex closing
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Proxy implements Zend_Search_Lucene_Interface
{
    /**
     * Index object
     *
     * @var Zend_Search_Lucene_Interface
     */
    private $_index;

    /**
     * Object constructor
     *
     * @param Zend_Search_Lucene_Interface $index
     */
    public function __construct(Zend_Search_Lucene_Interface $index)
    {
        $this->_index = $index;
        $this->_index->addReference();
    }

    /**
     * Object destructor
     */
    public function __destruct()
    {
        if ($this->_index !== null) {
            // This code is invoked if Zend_Search_Lucene_Interface object constructor throws an exception
            $this->_index->removeReference();
        }
        $this->_index = null;
    }

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
    public static function getActualGeneration(Zend_Search_Lucene_Storage_Directory $directory)
    {
        Zend_Search_Lucene::getActualGeneration($directory);
    }

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public static function getSegmentFileName($generation)
    {
        Zend_Search_Lucene::getSegmentFileName($generation);
    }

    /**
     * Get index format version
     *
     * @return integer
     */
    public function getFormatVersion()
    {
        return $this->_index->getFormatVersion();
    }

    /**
     * Set index format version.
     * Index is converted to this format at the nearest upfdate time
     *
     * @param int $formatVersion
     * @throws Zend_Search_Lucene_Exception
     */
    public function setFormatVersion($formatVersion)
    {
        $this->_index->setFormatVersion($formatVersion);
    }

    /**
     * Returns the Zend_Search_Lucene_Storage_Directory instance for this index.
     *
     * @return Zend_Search_Lucene_Storage_Directory
     */
    public function getDirectory()
    {
        return $this->_index->getDirectory();
    }

    /**
     * Returns the total number of documents in this index (including deleted documents).
     *
     * @return integer
     */
    public function count()
    {
        return $this->_index->count();
    }

    /**
     * Returns one greater than the largest possible document number.
     * This may be used to, e.g., determine how big to allocate a structure which will have
     * an element for every document number in an index.
     *
     * @return integer
     */
    public function maxDoc()
    {
        return $this->_index->maxDoc();
    }

    /**
     * Returns the total number of non-deleted documents in this index.
     *
     * @return integer
     */
    public function numDocs()
    {
        return $this->_index->numDocs();
    }

    /**
     * Checks, that document is deleted
     *
     * @param integer $id
     * @return boolean
     * @throws Zend_Search_Lucene_Exception    Exception is thrown if $id is out of the range
     */
    public function isDeleted($id)
    {
        return $this->_index->isDeleted($id);
    }

    /**
     * Set default search field.
     *
     * Null means, that search is performed through all fields by default
     *
     * Default value is null
     *
     * @param string $fieldName
     */
    public static function setDefaultSearchField($fieldName)
    {
        Zend_Search_Lucene::setDefaultSearchField($fieldName);
    }

    /**
     * Get default search field.
     *
     * Null means, that search is performed through all fields by default
     *
     * @return string
     */
    public static function getDefaultSearchField()
    {
        return Zend_Search_Lucene::getDefaultSearchField();
    }

    /**
     * Set result set limit.
     *
     * 0 (default) means no limit
     *
     * @param integer $limit
     */
    public static function setResultSetLimit($limit)
    {
        Zend_Search_Lucene::setResultSetLimit($limit);
    }

    /**
     * Set result set limit.
     *
     * 0 means no limit
     *
     * @return integer
     */
    public static function getResultSetLimit()
    {
        return Zend_Search_Lucene::getResultSetLimit();
    }

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
    public function getMaxBufferedDocs()
    {
        return $this->_index->getMaxBufferedDocs();
    }

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
    public function setMaxBufferedDocs($maxBufferedDocs)
    {
        $this->_index->setMaxBufferedDocs($maxBufferedDocs);
    }


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
    public function getMaxMergeDocs()
    {
        return $this->_index->getMaxMergeDocs();
    }

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
    public function setMaxMergeDocs($maxMergeDocs)
    {
        $this->_index->setMaxMergeDocs($maxMergeDocs);
    }


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
    public function getMergeFactor()
    {
        return $this->_index->getMergeFactor();
    }

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
    public function setMergeFactor($mergeFactor)
    {
        $this->_index->setMergeFactor($mergeFactor);
    }

    /**
     * Performs a query against the index and returns an array
     * of Zend_Search_Lucene_Search_QueryHit objects.
     * Input is a string or Zend_Search_Lucene_Search_Query.
     *
     * @param mixed $query
     * @return array Zend_Search_Lucene_Search_QueryHit
     * @throws Zend_Search_Lucene_Exception
     */
    public function find($query)
    {
        // actual parameter list
        $parameters = func_get_args();

        // invoke $this->_index->find() method with specified parameters
        return call_user_func_array(array(&$this->_index, 'find'), $parameters);
    }

    /**
     * Returns a list of all unique field names that exist in this index.
     *
     * @param boolean $indexed
     * @return array
     */
    public function getFieldNames($indexed = false)
    {
        return $this->_index->getFieldNames($indexed);
    }

    /**
     * Returns a Zend_Search_Lucene_Document object for the document
     * number $id in this index.
     *
     * @param integer|Zend_Search_Lucene_Search_QueryHit $id
     * @return Zend_Search_Lucene_Document
     */
    public function getDocument($id)
    {
        return $this->_index->getDocument($id);
    }

    /**
     * Returns true if index contain documents with specified term.
     *
     * Is used for query optimization.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @return boolean
     */
    public function hasTerm(Zend_Search_Lucene_Index_Term $term)
    {
        return $this->_index->hasTerm($term);
    }

    /**
     * Returns IDs of all the documents containing term.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return array
     */
    public function termDocs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termDocs($term, $docsFilter);
    }

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
    public function termDocsFilter(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termDocsFilter($term, $docsFilter);
    }

    /**
     * Returns an array of all term freqs.
     * Return array structure: array( docId => freq, ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return integer
     */
    public function termFreqs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termFreqs($term, $docsFilter);
    }

    /**
     * Returns an array of all term positions in the documents.
     * Return array structure: array( docId => array( pos1, pos2, ...), ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return array
     */
    public function termPositions(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termPositions($term, $docsFilter);
    }

    /**
     * Returns the number of documents in this index containing the $term.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @return integer
     */
    public function docFreq(Zend_Search_Lucene_Index_Term $term)
    {
        return $this->_index->docFreq($term);
    }

    /**
     * Retrive similarity used by index reader
     *
     * @return Zend_Search_Lucene_Search_Similarity
     */
    public function getSimilarity()
    {
        return $this->_index->getSimilarity();
    }

    /**
     * Returns a normalization factor for "field, document" pair.
     *
     * @param integer $id
     * @param string $fieldName
     * @return float
     */
    public function norm($id, $fieldName)
    {
        return $this->_index->norm($id, $fieldName);
    }

    /**
     * Returns true if any documents have been deleted from this index.
     *
     * @return boolean
     */
    public function hasDeletions()
    {
        return $this->_index->hasDeletions();
    }

    /**
     * Deletes a document from the index.
     * $id is an internal document id
     *
     * @param integer|Zend_Search_Lucene_Search_QueryHit $id
     * @throws Zend_Search_Lucene_Exception
     */
    public function delete($id)
    {
        return $this->_index->delete($id);
    }

    /**
     * Adds a document to this index.
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        $this->_index->addDocument($document);
    }

    /**
     * Commit changes resulting from delete() or undeleteAll() operations.
     */
    public function commit()
    {
        $this->_index->commit();
    }

    /**
     * Optimize index.
     *
     * Merges all segments into one
     */
    public function optimize()
    {
        $this->_index->optimize();
    }

    /**
     * Returns an array of all terms in this index.
     *
     * @return array
     */
    public function terms()
    {
        return $this->_index->terms();
    }


    /**
     * Reset terms stream.
     */
    public function resetTermsStream()
    {
        $this->_index->resetTermsStream();
    }

    /**
     * Skip terms stream up to specified term preffix.
     *
     * Prefix contains fully specified field info and portion of searched term
     *
     * @param Zend_Search_Lucene_Index_Term $prefix
     */
    public function skipTo(Zend_Search_Lucene_Index_Term $prefix)
    {
        return $this->_index->skipTo($prefix);
    }

    /**
     * Scans terms dictionary and returns next term
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function nextTerm()
    {
        return $this->_index->nextTerm();
    }

    /**
     * Returns term in current position
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function currentTerm()
    {
        return $this->_index->currentTerm();
    }

    /**
     * Close terms stream
     *
     * Should be used for resources clean up if stream is not read up to the end
     */
    public function closeTermsStream()
    {
        $this->_index->closeTermsStream();
    }


    /**
     * Undeletes all documents currently marked as deleted in this index.
     */
    public function undeleteAll()
    {
        return $this->_index->undeleteAll();
    }

    /**
     * Add reference to the index object
     *
     * @internal
     */
    public function addReference()
    {
        return $this->_index->addReference();
    }

    /**
     * Remove reference from the index object
     *
     * When reference count becomes zero, index is closed and resources are cleaned up
     *
     * @internal
     */
    public function removeReference()
    {
        return $this->_index->removeReference();
    }
}
