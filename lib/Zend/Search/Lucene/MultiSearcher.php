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
 * @version    $Id: MultiSearcher.php 22967 2010-09-18 18:53:58Z ramon $
 */


/** Zend_Search_Lucene_Interface */
#require_once 'Zend/Search/Lucene/Interface.php';

/**
 * Multisearcher allows to search through several independent indexes.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Interface_MultiSearcher implements Zend_Search_Lucene_Interface
{
    /**
     * List of indices for searching.
     * Array of Zend_Search_Lucene_Interface objects
     *
     * @var array
     */
    protected $_indices;

    /**
     * Object constructor.
     *
     * @param array $indices   Arrays of indices for search
     * @throws Zend_Search_Lucene_Exception
     */
    public function __construct($indices = array())
    {
        $this->_indices = $indices;

        foreach ($this->_indices as $index) {
            if (!$index instanceof Zend_Search_Lucene_Interface) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('sub-index objects have to implement Zend_Search_Lucene_Interface.');
            }
        }
    }

    /**
     * Add index for searching.
     *
     * @param Zend_Search_Lucene_Interface $index
     */
    public function addIndex(Zend_Search_Lucene_Interface $index)
    {
        $this->_indices[] = $index;
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
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception("Generation number can't be retrieved for multi-searcher");
    }

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public static function getSegmentFileName($generation)
    {
        return Zend_Search_Lucene::getSegmentFileName($generation);
    }

    /**
     * Get index format version
     *
     * @return integer
     * @throws Zend_Search_Lucene_Exception
     */
    public function getFormatVersion()
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception("Format version can't be retrieved for multi-searcher");
    }

    /**
     * Set index format version.
     * Index is converted to this format at the nearest upfdate time
     *
     * @param int $formatVersion
     */
    public function setFormatVersion($formatVersion)
    {
        foreach ($this->_indices as $index) {
            $index->setFormatVersion($formatVersion);
        }
    }

    /**
     * Returns the Zend_Search_Lucene_Storage_Directory instance for this index.
     *
     * @return Zend_Search_Lucene_Storage_Directory
     */
    public function getDirectory()
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception("Index directory can't be retrieved for multi-searcher");
    }

    /**
     * Returns the total number of documents in this index (including deleted documents).
     *
     * @return integer
     */
    public function count()
    {
        $count = 0;

        foreach ($this->_indices as $index) {
            $count += $index->count();
        }

        return $count;
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
        return $this->count();
    }

    /**
     * Returns the total number of non-deleted documents in this index.
     *
     * @return integer
     */
    public function numDocs()
    {
        $docs = 0;

        foreach ($this->_indices as $index) {
            $docs += $index->numDocs();
        }

        return $docs;
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
        foreach ($this->_indices as $index) {
            $indexCount = $index->count();

            if ($indexCount > $id) {
                return $index->isDeleted($id);
            }

            $id -= $indexCount;
        }

        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Document id is out of the range.');
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
        foreach ($this->_indices as $index) {
            $index->setDefaultSearchField($fieldName);
        }
    }


    /**
     * Get default search field.
     *
     * Null means, that search is performed through all fields by default
     *
     * @return string
     * @throws Zend_Search_Lucene_Exception
     */
    public static function getDefaultSearchField()
    {
        if (count($this->_indices) == 0) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Indices list is empty');
        }

        $defaultSearchField = reset($this->_indices)->getDefaultSearchField();

        foreach ($this->_indices as $index) {
            if ($index->getDefaultSearchField() !== $defaultSearchField) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Indices have different default search field.');
            }
        }

        return $defaultSearchField;
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
        foreach ($this->_indices as $index) {
            $index->setResultSetLimit($limit);
        }
    }

    /**
     * Set result set limit.
     *
     * 0 means no limit
     *
     * @return integer
     * @throws Zend_Search_Lucene_Exception
     */
    public static function getResultSetLimit()
    {
        if (count($this->_indices) == 0) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Indices list is empty');
        }

        $defaultResultSetLimit = reset($this->_indices)->getResultSetLimit();

        foreach ($this->_indices as $index) {
            if ($index->getResultSetLimit() !== $defaultResultSetLimit) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Indices have different default search field.');
            }
        }

        return $defaultResultSetLimit;
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
     * @throws Zend_Search_Lucene_Exception
     */
    public function getMaxBufferedDocs()
    {
        if (count($this->_indices) == 0) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Indices list is empty');
        }

        $maxBufferedDocs = reset($this->_indices)->getMaxBufferedDocs();

        foreach ($this->_indices as $index) {
            if ($index->getMaxBufferedDocs() !== $maxBufferedDocs) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Indices have different default search field.');
            }
        }

        return $maxBufferedDocs;
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
        foreach ($this->_indices as $index) {
            $index->setMaxBufferedDocs($maxBufferedDocs);
        }
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
     * @throws Zend_Search_Lucene_Exception
     */
    public function getMaxMergeDocs()
    {
        if (count($this->_indices) == 0) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Indices list is empty');
        }

        $maxMergeDocs = reset($this->_indices)->getMaxMergeDocs();

        foreach ($this->_indices as $index) {
            if ($index->getMaxMergeDocs() !== $maxMergeDocs) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Indices have different default search field.');
            }
        }

        return $maxMergeDocs;
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
        foreach ($this->_indices as $index) {
            $index->setMaxMergeDocs($maxMergeDocs);
        }
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
     * @throws Zend_Search_Lucene_Exception
     */
    public function getMergeFactor()
    {
        if (count($this->_indices) == 0) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Indices list is empty');
        }

        $mergeFactor = reset($this->_indices)->getMergeFactor();

        foreach ($this->_indices as $index) {
            if ($index->getMergeFactor() !== $mergeFactor) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Indices have different default search field.');
            }
        }

        return $mergeFactor;
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
        foreach ($this->_indices as $index) {
            $index->setMaxMergeDocs($mergeFactor);
        }
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
        if (count($this->_indices) == 0) {
            return array();
        }

        $hitsList = array();

        $indexShift = 0;
        foreach ($this->_indices as $index) {
            $hits = $index->find($query);

            if ($indexShift != 0) {
                foreach ($hits as $hit) {
                    $hit->id += $indexShift;
                }
            }

            $indexShift += $index->count();
            $hitsList[] = $hits;
        }

        /** @todo Implement advanced sorting */

        return call_user_func_array('array_merge', $hitsList);
    }

    /**
     * Returns a list of all unique field names that exist in this index.
     *
     * @param boolean $indexed
     * @return array
     */
    public function getFieldNames($indexed = false)
    {
        $fieldNamesList = array();

        foreach ($this->_indices as $index) {
            $fieldNamesList[] = $index->getFieldNames($indexed);
        }

        return array_unique(call_user_func_array('array_merge', $fieldNamesList));
    }

    /**
     * Returns a Zend_Search_Lucene_Document object for the document
     * number $id in this index.
     *
     * @param integer|Zend_Search_Lucene_Search_QueryHit $id
     * @return Zend_Search_Lucene_Document
     * @throws Zend_Search_Lucene_Exception    Exception is thrown if $id is out of the range
     */
    public function getDocument($id)
    {
        if ($id instanceof Zend_Search_Lucene_Search_QueryHit) {
            /* @var $id Zend_Search_Lucene_Search_QueryHit */
            $id = $id->id;
        }

        foreach ($this->_indices as $index) {
            $indexCount = $index->count();

            if ($indexCount > $id) {
                return $index->getDocument($id);
            }

            $id -= $indexCount;
        }

        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Document id is out of the range.');
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
        foreach ($this->_indices as $index) {
            if ($index->hasTerm($term)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns IDs of all the documents containing term.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return array
     * @throws Zend_Search_Lucene_Exception
     */
    public function termDocs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        if ($docsFilter != null) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Document filters could not used with multi-searcher');
        }

        $docsList = array();

        $indexShift = 0;
        foreach ($this->_indices as $index) {
            $docs = $index->termDocs($term);

            if ($indexShift != 0) {
                foreach ($docs as $id => $docId) {
                    $docs[$id] += $indexShift;
                }
            }

            $indexShift += $index->count();
            $docsList[] = $docs;
        }

        return call_user_func_array('array_merge', $docsList);
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
     * @throws Zend_Search_Lucene_Exception
     */
    public function termDocsFilter(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Document filters could not used with multi-searcher');
    }

    /**
     * Returns an array of all term freqs.
     * Return array structure: array( docId => freq, ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return integer
     * @throws Zend_Search_Lucene_Exception
     */
    public function termFreqs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        if ($docsFilter != null) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Document filters could not used with multi-searcher');
        }

        $freqsList = array();

        $indexShift = 0;
        foreach ($this->_indices as $index) {
            $freqs = $index->termFreqs($term);

            if ($indexShift != 0) {
                $freqsShifted = array();

                foreach ($freqs as $docId => $freq) {
                    $freqsShifted[$docId + $indexShift] = $freq;
                }
                $freqs = $freqsShifted;
            }

            $indexShift += $index->count();
            $freqsList[] = $freqs;
        }

        return call_user_func_array('array_merge', $freqsList);
    }

    /**
     * Returns an array of all term positions in the documents.
     * Return array structure: array( docId => array( pos1, pos2, ...), ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return array
     * @throws Zend_Search_Lucene_Exception
     */
    public function termPositions(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        if ($docsFilter != null) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Document filters could not used with multi-searcher');
        }

        $termPositionsList = array();

        $indexShift = 0;
        foreach ($this->_indices as $index) {
            $termPositions = $index->termPositions($term);

            if ($indexShift != 0) {
                $termPositionsShifted = array();

                foreach ($termPositions as $docId => $positions) {
                    $termPositions[$docId + $indexShift] = $positions;
                }
                $termPositions = $termPositionsShifted;
            }

            $indexShift += $index->count();
            $termPositionsList[] = $termPositions;
        }

        return call_user_func_array('array_merge', $termPositions);
    }

    /**
     * Returns the number of documents in this index containing the $term.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @return integer
     */
    public function docFreq(Zend_Search_Lucene_Index_Term $term)
    {
        $docFreq = 0;

        foreach ($this->_indices as $index) {
            $docFreq += $index->docFreq($term);
        }

        return $docFreq;
    }

    /**
     * Retrive similarity used by index reader
     *
     * @return Zend_Search_Lucene_Search_Similarity
     * @throws Zend_Search_Lucene_Exception
     */
    public function getSimilarity()
    {
        if (count($this->_indices) == 0) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Indices list is empty');
        }

        $similarity = reset($this->_indices)->getSimilarity();

        foreach ($this->_indices as $index) {
            if ($index->getSimilarity() !== $similarity) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Indices have different similarity.');
            }
        }

        return $similarity;
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
        foreach ($this->_indices as $index) {
            $indexCount = $index->count();

            if ($indexCount > $id) {
                return $index->norm($id, $fieldName);
            }

            $id -= $indexCount;
        }

        return null;
    }

    /**
     * Returns true if any documents have been deleted from this index.
     *
     * @return boolean
     */
    public function hasDeletions()
    {
        foreach ($this->_indices as $index) {
            if ($index->hasDeletions()) {
                return true;
            }
        }

        return false;
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
        foreach ($this->_indices as $index) {
            $indexCount = $index->count();

            if ($indexCount > $id) {
                $index->delete($id);
                return;
            }

            $id -= $indexCount;
        }

        #require_once 'Zend/Search/Lucene/Exception.php';
        throw new Zend_Search_Lucene_Exception('Document id is out of the range.');
    }


    /**
     * Callback used to choose target index for new documents
     *
     * Function/method signature:
     *    Zend_Search_Lucene_Interface  callbackFunction(Zend_Search_Lucene_Document $document, array $indices);
     *
     * null means "default documents distributing algorithm"
     *
     * @var callback
     */
    protected $_documentDistributorCallBack = null;

    /**
     * Set callback for choosing target index.
     *
     * @param callback $callback
     * @throws Zend_Search_Lucene_Exception
     */
    public function setDocumentDistributorCallback($callback)
    {
        if ($callback !== null  &&  !is_callable($callback)) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('$callback parameter must be a valid callback.');
        }

        $this->_documentDistributorCallBack = $callback;
    }

    /**
     * Get callback for choosing target index.
     *
     * @return callback
     */
    public function getDocumentDistributorCallback()
    {
        return $this->_documentDistributorCallBack;
    }

    /**
     * Adds a document to this index.
     *
     * @param Zend_Search_Lucene_Document $document
     * @throws Zend_Search_Lucene_Exception
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        if ($this->_documentDistributorCallBack !== null) {
            $index = call_user_func($this->_documentDistributorCallBack, $document, $this->_indices);
        } else {
            $index = $this->_indices[array_rand($this->_indices)];
        }

        $index->addDocument($document);
    }

    /**
     * Commit changes resulting from delete() or undeleteAll() operations.
     */
    public function commit()
    {
        foreach ($this->_indices as $index) {
            $index->commit();
        }
    }

    /**
     * Optimize index.
     *
     * Merges all segments into one
     */
    public function optimize()
    {
        foreach ($this->_indices as $index) {
            $index->optimise();
        }
    }

    /**
     * Returns an array of all terms in this index.
     *
     * @return array
     */
    public function terms()
    {
        $termsList = array();

        foreach ($this->_indices as $index) {
            $termsList[] = $index->terms();
        }

        return array_unique(call_user_func_array('array_merge', $termsList));
    }


    /**
     * Terms stream priority queue object
     *
     * @var Zend_Search_Lucene_TermStreamsPriorityQueue
     */
    private $_termsStream = null;

    /**
     * Reset terms stream.
     */
    public function resetTermsStream()
    {
        if ($this->_termsStream === null) {
            /** Zend_Search_Lucene_TermStreamsPriorityQueue */
            #require_once 'Zend/Search/Lucene/TermStreamsPriorityQueue.php';

            $this->_termsStream = new Zend_Search_Lucene_TermStreamsPriorityQueue($this->_indices);
        } else {
            $this->_termsStream->resetTermsStream();
        }
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
        $this->_termsStream->skipTo($prefix);
    }

    /**
     * Scans terms dictionary and returns next term
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function nextTerm()
    {
        return $this->_termsStream->nextTerm();
    }

    /**
     * Returns term in current position
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function currentTerm()
    {
        return $this->_termsStream->currentTerm();
    }

    /**
     * Close terms stream
     *
     * Should be used for resources clean up if stream is not read up to the end
     */
    public function closeTermsStream()
    {
        $this->_termsStream->closeTermsStream();
        $this->_termsStream = null;
    }


    /**
     * Undeletes all documents currently marked as deleted in this index.
     */
    public function undeleteAll()
    {
        foreach ($this->_indices as $index) {
            $index->undeleteAll();
        }
    }


    /**
     * Add reference to the index object
     *
     * @internal
     */
    public function addReference()
    {
        // Do nothing, since it's never referenced by indices
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
        // Do nothing, since it's never referenced by indices
    }
}
