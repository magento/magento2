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
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Search_Lucene_LockManager */
#require_once 'Zend/Search/Lucene/LockManager.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_Writer
{
    /**
     * @todo Implement Analyzer substitution
     * @todo Implement Zend_Search_Lucene_Storage_DirectoryRAM and Zend_Search_Lucene_Storage_FileRAM to use it for
     *       temporary index files
     * @todo Directory lock processing
     */

    /**
     * Number of documents required before the buffered in-memory
     * documents are written into a new Segment
     *
     * Default value is 10
     *
     * @var integer
     */
    public $maxBufferedDocs = 10;

    /**
     * Largest number of documents ever merged by addDocument().
     * Small values (e.g., less than 10,000) are best for interactive indexing,
     * as this limits the length of pauses while indexing to a few seconds.
     * Larger values are best for batched indexing and speedier searches.
     *
     * Default value is PHP_INT_MAX
     *
     * @var integer
     */
    public $maxMergeDocs = PHP_INT_MAX;

    /**
     * Determines how often segment indices are merged by addDocument().
     *
     * With smaller values, less RAM is used while indexing,
     * and searches on unoptimized indices are faster,
     * but indexing speed is slower.
     *
     * With larger values, more RAM is used during indexing,
     * and while searches on unoptimized indices are slower,
     * indexing is faster.
     *
     * Thus larger values (> 10) are best for batch index creation,
     * and smaller values (< 10) for indices that are interactively maintained.
     *
     * Default value is 10
     *
     * @var integer
     */
    public $mergeFactor = 10;

    /**
     * File system adapter.
     *
     * @var Zend_Search_Lucene_Storage_Directory
     */
    private $_directory = null;


    /**
     * Changes counter.
     *
     * @var integer
     */
    private $_versionUpdate = 0;

    /**
     * List of the segments, created by index writer
     * Array of Zend_Search_Lucene_Index_SegmentInfo objects
     *
     * @var array
     */
    private $_newSegments = array();

    /**
     * List of segments to be deleted on commit
     *
     * @var array
     */
    private $_segmentsToDelete = array();

    /**
     * Current segment to add documents
     *
     * @var Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter
     */
    private $_currentSegment = null;

    /**
     * Array of Zend_Search_Lucene_Index_SegmentInfo objects for this index.
     *
     * It's a reference to the corresponding Zend_Search_Lucene::$_segmentInfos array
     *
     * @var array Zend_Search_Lucene_Index_SegmentInfo
     */
    private $_segmentInfos;

    /**
     * Index target format version
     *
     * @var integer
     */
    private $_targetFormatVersion;

    /**
     * List of indexfiles extensions
     *
     * @var array
     */
    private static $_indexExtensions = array('.cfs' => '.cfs',
                                             '.cfx' => '.cfx',
                                             '.fnm' => '.fnm',
                                             '.fdx' => '.fdx',
                                             '.fdt' => '.fdt',
                                             '.tis' => '.tis',
                                             '.tii' => '.tii',
                                             '.frq' => '.frq',
                                             '.prx' => '.prx',
                                             '.tvx' => '.tvx',
                                             '.tvd' => '.tvd',
                                             '.tvf' => '.tvf',
                                             '.del' => '.del',
                                             '.sti' => '.sti' );


    /**
     * Create empty index
     *
     * @param Zend_Search_Lucene_Storage_Directory $directory
     * @param integer $generation
     * @param integer $nameCount
     */
    public static function createIndex(Zend_Search_Lucene_Storage_Directory $directory, $generation, $nameCount)
    {
        if ($generation == 0) {
            // Create index in pre-2.1 mode
            foreach ($directory->fileList() as $file) {
                if ($file == 'deletable' ||
                    $file == 'segments'  ||
                    isset(self::$_indexExtensions[ substr($file, strlen($file)-4)]) ||
                    preg_match('/\.f\d+$/i', $file) /* matches <segment_name>.f<decimal_nmber> file names */) {
                        $directory->deleteFile($file);
                    }
            }

            $segmentsFile = $directory->createFile('segments');
            $segmentsFile->writeInt((int)0xFFFFFFFF);

            // write version (initialized by current time)
            $segmentsFile->writeLong(round(microtime(true)));

            // write name counter
            $segmentsFile->writeInt($nameCount);
            // write segment counter
            $segmentsFile->writeInt(0);

            $deletableFile = $directory->createFile('deletable');
            // write counter
            $deletableFile->writeInt(0);
        } else {
            $genFile = $directory->createFile('segments.gen');

            $genFile->writeInt((int)0xFFFFFFFE);
            // Write generation two times
            $genFile->writeLong($generation);
            $genFile->writeLong($generation);

            $segmentsFile = $directory->createFile(Zend_Search_Lucene::getSegmentFileName($generation));
            $segmentsFile->writeInt((int)0xFFFFFFFD);

            // write version (initialized by current time)
            $segmentsFile->writeLong(round(microtime(true)));

            // write name counter
            $segmentsFile->writeInt($nameCount);
            // write segment counter
            $segmentsFile->writeInt(0);
        }
    }

    /**
     * Open the index for writing
     *
     * @param Zend_Search_Lucene_Storage_Directory $directory
     * @param array $segmentInfos
     * @param integer $targetFormatVersion
     * @param Zend_Search_Lucene_Storage_File $cleanUpLock
     */
    public function __construct(Zend_Search_Lucene_Storage_Directory $directory, &$segmentInfos, $targetFormatVersion)
    {
        $this->_directory           = $directory;
        $this->_segmentInfos        = &$segmentInfos;
        $this->_targetFormatVersion = $targetFormatVersion;
    }

    /**
     * Adds a document to this index.
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        /** Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter */
        #require_once 'Zend/Search/Lucene/Index/SegmentWriter/DocumentWriter.php';

        if ($this->_currentSegment === null) {
            $this->_currentSegment =
                new Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter($this->_directory, $this->_newSegmentName());
        }
        $this->_currentSegment->addDocument($document);

        if ($this->_currentSegment->count() >= $this->maxBufferedDocs) {
            $this->commit();
        }

        $this->_maybeMergeSegments();

        $this->_versionUpdate++;
    }


    /**
     * Check if we have anything to merge
     *
     * @return boolean
     */
    private function _hasAnythingToMerge()
    {
        $segmentSizes = array();
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            $segmentSizes[$segName] = $segmentInfo->count();
        }

        $mergePool   = array();
        $poolSize    = 0;
        $sizeToMerge = $this->maxBufferedDocs;
        asort($segmentSizes, SORT_NUMERIC);
        foreach ($segmentSizes as $segName => $size) {
            // Check, if segment comes into a new merging block
            while ($size >= $sizeToMerge) {
                // Merge previous block if it's large enough
                if ($poolSize >= $sizeToMerge) {
                    return true;
                }
                $mergePool   = array();
                $poolSize    = 0;

                $sizeToMerge *= $this->mergeFactor;

                if ($sizeToMerge > $this->maxMergeDocs) {
                    return false;
                }
            }

            $mergePool[] = $this->_segmentInfos[$segName];
            $poolSize += $size;
        }

        if ($poolSize >= $sizeToMerge) {
            return true;
        }

        return false;
    }

    /**
     * Merge segments if necessary
     */
    private function _maybeMergeSegments()
    {
        if (Zend_Search_Lucene_LockManager::obtainOptimizationLock($this->_directory) === false) {
            return;
        }

        if (!$this->_hasAnythingToMerge()) {
            Zend_Search_Lucene_LockManager::releaseOptimizationLock($this->_directory);
            return;
        }

        // Update segments list to be sure all segments are not merged yet by another process
        //
        // Segment merging functionality is concentrated in this class and surrounded
        // by optimization lock obtaining/releasing.
        // _updateSegments() refreshes segments list from the latest index generation.
        // So only new segments can be added to the index while we are merging some already existing
        // segments.
        // Newly added segments will be also included into the index by the _updateSegments() call
        // either by another process or by the current process with the commit() call at the end of _mergeSegments() method.
        // That's guaranteed by the serialisation of _updateSegments() execution using exclusive locks.
        $this->_updateSegments();

        // Perform standard auto-optimization procedure
        $segmentSizes = array();
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            $segmentSizes[$segName] = $segmentInfo->count();
        }

        $mergePool   = array();
        $poolSize    = 0;
        $sizeToMerge = $this->maxBufferedDocs;
        asort($segmentSizes, SORT_NUMERIC);
        foreach ($segmentSizes as $segName => $size) {
            // Check, if segment comes into a new merging block
            while ($size >= $sizeToMerge) {
                // Merge previous block if it's large enough
                if ($poolSize >= $sizeToMerge) {
                    $this->_mergeSegments($mergePool);
                }
                $mergePool   = array();
                $poolSize    = 0;

                $sizeToMerge *= $this->mergeFactor;

                if ($sizeToMerge > $this->maxMergeDocs) {
                    Zend_Search_Lucene_LockManager::releaseOptimizationLock($this->_directory);
                    return;
                }
            }

            $mergePool[] = $this->_segmentInfos[$segName];
            $poolSize += $size;
        }

        if ($poolSize >= $sizeToMerge) {
            $this->_mergeSegments($mergePool);
        }

        Zend_Search_Lucene_LockManager::releaseOptimizationLock($this->_directory);
    }

    /**
     * Merge specified segments
     *
     * $segments is an array of SegmentInfo objects
     *
     * @param array $segments
     */
    private function _mergeSegments($segments)
    {
        $newName = $this->_newSegmentName();

        /** Zend_Search_Lucene_Index_SegmentMerger */
        #require_once 'Zend/Search/Lucene/Index/SegmentMerger.php';
        $merger = new Zend_Search_Lucene_Index_SegmentMerger($this->_directory,
                                                             $newName);
        foreach ($segments as $segmentInfo) {
            $merger->addSource($segmentInfo);
            $this->_segmentsToDelete[$segmentInfo->getName()] = $segmentInfo->getName();
        }

        $newSegment = $merger->merge();
        if ($newSegment !== null) {
            $this->_newSegments[$newSegment->getName()] = $newSegment;
        }

        $this->commit();
    }

    /**
     * Update segments file by adding current segment to a list
     *
     * @throws Zend_Search_Lucene_Exception
     */
    private function _updateSegments()
    {
        // Get an exclusive index lock
        Zend_Search_Lucene_LockManager::obtainWriteLock($this->_directory);

        // Write down changes for the segments
        foreach ($this->_segmentInfos as $segInfo) {
            $segInfo->writeChanges();
        }


        $generation = Zend_Search_Lucene::getActualGeneration($this->_directory);
        $segmentsFile   = $this->_directory->getFileObject(Zend_Search_Lucene::getSegmentFileName($generation), false);
        $newSegmentFile = $this->_directory->createFile(Zend_Search_Lucene::getSegmentFileName(++$generation), false);

        try {
            $genFile = $this->_directory->getFileObject('segments.gen', false);
        } catch (Zend_Search_Lucene_Exception $e) {
            if (strpos($e->getMessage(), 'is not readable') !== false) {
                $genFile = $this->_directory->createFile('segments.gen');
            } else {
                throw new Zend_Search_Lucene_Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        $genFile->writeInt((int)0xFFFFFFFE);
        // Write generation (first copy)
        $genFile->writeLong($generation);

        try {
            // Write format marker
            if ($this->_targetFormatVersion == Zend_Search_Lucene::FORMAT_2_1) {
                $newSegmentFile->writeInt((int)0xFFFFFFFD);
            } else if ($this->_targetFormatVersion == Zend_Search_Lucene::FORMAT_2_3) {
                $newSegmentFile->writeInt((int)0xFFFFFFFC);
            }

            // Read src file format identifier
            $format = $segmentsFile->readInt();
            if ($format == (int)0xFFFFFFFF) {
                $srcFormat = Zend_Search_Lucene::FORMAT_PRE_2_1;
            } else if ($format == (int)0xFFFFFFFD) {
                $srcFormat = Zend_Search_Lucene::FORMAT_2_1;
            } else if ($format == (int)0xFFFFFFFC) {
                $srcFormat = Zend_Search_Lucene::FORMAT_2_3;
            } else {
                throw new Zend_Search_Lucene_Exception('Unsupported segments file format');
            }

            $version = $segmentsFile->readLong() + $this->_versionUpdate;
            $this->_versionUpdate = 0;
            $newSegmentFile->writeLong($version);

            // Write segment name counter
            $newSegmentFile->writeInt($segmentsFile->readInt());

            // Get number of segments offset
            $numOfSegmentsOffset = $newSegmentFile->tell();
            // Write dummy data (segment counter)
            $newSegmentFile->writeInt(0);

            // Read number of segemnts
            $segmentsCount = $segmentsFile->readInt();

            $segments = array();
            for ($count = 0; $count < $segmentsCount; $count++) {
                $segName = $segmentsFile->readString();
                $segSize = $segmentsFile->readInt();

                if ($srcFormat == Zend_Search_Lucene::FORMAT_PRE_2_1) {
                    // pre-2.1 index format
                    $delGen            = 0;
                    $hasSingleNormFile = false;
                    $numField          = (int)0xFFFFFFFF;
                    $isCompoundByte    = 0;
                    $docStoreOptions   = null;
                } else {
                    $delGen = $segmentsFile->readLong();

                    if ($srcFormat == Zend_Search_Lucene::FORMAT_2_3) {
                        $docStoreOffset = $segmentsFile->readInt();

                        if ($docStoreOffset != (int)0xFFFFFFFF) {
                            $docStoreSegment        = $segmentsFile->readString();
                            $docStoreIsCompoundFile = $segmentsFile->readByte();

                            $docStoreOptions = array('offset'     => $docStoreOffset,
                                                     'segment'    => $docStoreSegment,
                                                     'isCompound' => ($docStoreIsCompoundFile == 1));
                        } else {
                            $docStoreOptions = null;
                        }
                    } else {
                        $docStoreOptions = null;
                    }

                    $hasSingleNormFile = $segmentsFile->readByte();
                    $numField          = $segmentsFile->readInt();

                    $normGens = array();
                    if ($numField != (int)0xFFFFFFFF) {
                        for ($count1 = 0; $count1 < $numField; $count1++) {
                            $normGens[] = $segmentsFile->readLong();
                        }
                    }
                    $isCompoundByte    = $segmentsFile->readByte();
                }

                if (!in_array($segName, $this->_segmentsToDelete)) {
                    // Load segment if necessary
                    if (!isset($this->_segmentInfos[$segName])) {
                        if ($isCompoundByte == 0xFF) {
                            // The segment is not a compound file
                            $isCompound = false;
                        } else if ($isCompoundByte == 0x00) {
                            // The status is unknown
                            $isCompound = null;
                        } else if ($isCompoundByte == 0x01) {
                            // The segment is a compound file
                            $isCompound = true;
                        }

                        /** Zend_Search_Lucene_Index_SegmentInfo */
                        #require_once 'Zend/Search/Lucene/Index/SegmentInfo.php';
                        $this->_segmentInfos[$segName] =
                                    new Zend_Search_Lucene_Index_SegmentInfo($this->_directory,
                                                                             $segName,
                                                                             $segSize,
                                                                             $delGen,
                                                                             $docStoreOptions,
                                                                             $hasSingleNormFile,
                                                                             $isCompound);
                    } else {
                        // Retrieve actual deletions file generation number
                        $delGen = $this->_segmentInfos[$segName]->getDelGen();
                    }

                    $newSegmentFile->writeString($segName);
                    $newSegmentFile->writeInt($segSize);
                    $newSegmentFile->writeLong($delGen);
                    if ($this->_targetFormatVersion == Zend_Search_Lucene::FORMAT_2_3) {
                        if ($docStoreOptions !== null) {
                            $newSegmentFile->writeInt($docStoreOffset);
                            $newSegmentFile->writeString($docStoreSegment);
                            $newSegmentFile->writeByte($docStoreIsCompoundFile);
                        } else {
                            // Set DocStoreOffset to -1
                            $newSegmentFile->writeInt((int)0xFFFFFFFF);
                        }
                    } else if ($docStoreOptions !== null) {
                        // Release index write lock
                        Zend_Search_Lucene_LockManager::releaseWriteLock($this->_directory);

                        throw new Zend_Search_Lucene_Exception('Index conversion to lower format version is not supported.');
                    }

                    $newSegmentFile->writeByte($hasSingleNormFile);
                    $newSegmentFile->writeInt($numField);
                    if ($numField != (int)0xFFFFFFFF) {
                        foreach ($normGens as $normGen) {
                            $newSegmentFile->writeLong($normGen);
                        }
                    }
                    $newSegmentFile->writeByte($isCompoundByte);

                    $segments[$segName] = $segSize;
                }
            }
            $segmentsFile->close();

            $segmentsCount = count($segments) + count($this->_newSegments);

            foreach ($this->_newSegments as $segName => $segmentInfo) {
                $newSegmentFile->writeString($segName);
                $newSegmentFile->writeInt($segmentInfo->count());

                // delete file generation: -1 (there is no delete file yet)
                $newSegmentFile->writeInt((int)0xFFFFFFFF);$newSegmentFile->writeInt((int)0xFFFFFFFF);
                if ($this->_targetFormatVersion == Zend_Search_Lucene::FORMAT_2_3) {
                    // docStoreOffset: -1 (segment doesn't use shared doc store)
                    $newSegmentFile->writeInt((int)0xFFFFFFFF);
                }
                // HasSingleNormFile
                $newSegmentFile->writeByte($segmentInfo->hasSingleNormFile());
                // NumField
                $newSegmentFile->writeInt((int)0xFFFFFFFF);
                // IsCompoundFile
                $newSegmentFile->writeByte($segmentInfo->isCompound() ? 1 : -1);

                $segments[$segmentInfo->getName()] = $segmentInfo->count();
                $this->_segmentInfos[$segName] = $segmentInfo;
            }
            $this->_newSegments = array();

            $newSegmentFile->seek($numOfSegmentsOffset);
            $newSegmentFile->writeInt($segmentsCount);  // Update segments count
            $newSegmentFile->close();
        } catch (Exception $e) {
            /** Restore previous index generation */
            $generation--;
            $genFile->seek(4, SEEK_SET);
            // Write generation number twice
            $genFile->writeLong($generation); $genFile->writeLong($generation);

            // Release index write lock
            Zend_Search_Lucene_LockManager::releaseWriteLock($this->_directory);

            // Throw the exception
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception($e->getMessage(), $e->getCode(), $e);
        }

        // Write generation (second copy)
        $genFile->writeLong($generation);


        // Check if another update or read process is not running now
        // If yes, skip clean-up procedure
        if (Zend_Search_Lucene_LockManager::escalateReadLock($this->_directory)) {
            /**
             * Clean-up directory
             */
            $filesToDelete = array();
            $filesTypes    = array();
            $filesNumbers  = array();

            // list of .del files of currently used segments
            // each segment can have several generations of .del files
            // only last should not be deleted
            $delFiles = array();

            foreach ($this->_directory->fileList() as $file) {
                if ($file == 'deletable') {
                    // 'deletable' file
                    $filesToDelete[] = $file;
                    $filesTypes[]    = 0; // delete this file first, since it's not used starting from Lucene v2.1
                    $filesNumbers[]  = 0;
                } else if ($file == 'segments') {
                    // 'segments' file
                    $filesToDelete[] = $file;
                    $filesTypes[]    = 1; // second file to be deleted "zero" version of segments file (Lucene pre-2.1)
                    $filesNumbers[]  = 0;
                } else if (preg_match('/^segments_[a-zA-Z0-9]+$/i', $file)) {
                    // 'segments_xxx' file
                    // Check if it's not a just created generation file
                    if ($file != Zend_Search_Lucene::getSegmentFileName($generation)) {
                        $filesToDelete[] = $file;
                        $filesTypes[]    = 2; // first group of files for deletions
                        $filesNumbers[]  = (int)base_convert(substr($file, 9), 36, 10); // ordered by segment generation numbers
                    }
                } else if (preg_match('/(^_([a-zA-Z0-9]+))\.f\d+$/i', $file, $matches)) {
                    // one of per segment files ('<segment_name>.f<decimal_number>')
                    // Check if it's not one of the segments in the current segments set
                    if (!isset($segments[$matches[1]])) {
                        $filesToDelete[] = $file;
                        $filesTypes[]    = 3; // second group of files for deletions
                        $filesNumbers[]  = (int)base_convert($matches[2], 36, 10); // order by segment number
                    }
                } else if (preg_match('/(^_([a-zA-Z0-9]+))(_([a-zA-Z0-9]+))\.del$/i', $file, $matches)) {
                    // one of per segment files ('<segment_name>_<del_generation>.del' where <segment_name> is '_<segment_number>')
                    // Check if it's not one of the segments in the current segments set
                    if (!isset($segments[$matches[1]])) {
                        $filesToDelete[] = $file;
                        $filesTypes[]    = 3; // second group of files for deletions
                        $filesNumbers[]  = (int)base_convert($matches[2], 36, 10); // order by segment number
                    } else {
                        $segmentNumber = (int)base_convert($matches[2], 36, 10);
                        $delGeneration = (int)base_convert($matches[4], 36, 10);
                        if (!isset($delFiles[$segmentNumber])) {
                            $delFiles[$segmentNumber] = array();
                        }
                        $delFiles[$segmentNumber][$delGeneration] = $file;
                    }
                } else if (isset(self::$_indexExtensions[substr($file, strlen($file)-4)])) {
                    // one of per segment files ('<segment_name>.<ext>')
                    $segmentName = substr($file, 0, strlen($file) - 4);
                    // Check if it's not one of the segments in the current segments set
                    if (!isset($segments[$segmentName])  &&
                        ($this->_currentSegment === null  ||  $this->_currentSegment->getName() != $segmentName)) {
                        $filesToDelete[] = $file;
                        $filesTypes[]    = 3; // second group of files for deletions
                        $filesNumbers[]  = (int)base_convert(substr($file, 1 /* skip '_' */, strlen($file)-5), 36, 10); // order by segment number
                    }
                }
            }

            $maxGenNumber = 0;
            // process .del files of currently used segments
            foreach ($delFiles as $segmentNumber => $segmentDelFiles) {
                ksort($delFiles[$segmentNumber], SORT_NUMERIC);
                array_pop($delFiles[$segmentNumber]); // remove last delete file generation from candidates for deleting

                end($delFiles[$segmentNumber]);
                $lastGenNumber = key($delFiles[$segmentNumber]);
                if ($lastGenNumber > $maxGenNumber) {
                    $maxGenNumber = $lastGenNumber;
                }
            }
            foreach ($delFiles as $segmentNumber => $segmentDelFiles) {
                foreach ($segmentDelFiles as $delGeneration => $file) {
                        $filesToDelete[] = $file;
                        $filesTypes[]    = 4; // third group of files for deletions
                        $filesNumbers[]  = $segmentNumber*$maxGenNumber + $delGeneration; // order by <segment_number>,<del_generation> pair
                }
            }

            // Reorder files for deleting
            array_multisort($filesTypes,    SORT_ASC, SORT_NUMERIC,
                            $filesNumbers,  SORT_ASC, SORT_NUMERIC,
                            $filesToDelete, SORT_ASC, SORT_STRING);

            foreach ($filesToDelete as $file) {
                try {
                    /** Skip shared docstore segments deleting */
                    /** @todo Process '.cfx' files to check if them are already unused */
                    if (substr($file, strlen($file)-4) != '.cfx') {
                        $this->_directory->deleteFile($file);
                    }
                } catch (Zend_Search_Lucene_Exception $e) {
                    if (strpos($e->getMessage(), 'Can\'t delete file') === false) {
                        // That's not "file is under processing or already deleted" exception
                        // Pass it through
                        throw new Zend_Search_Lucene_Exception($e->getMessage(), $e->getCode(), $e);
                    }
                }
            }

            // Return read lock into the previous state
            Zend_Search_Lucene_LockManager::deEscalateReadLock($this->_directory);
        } else {
            // Only release resources if another index reader is running now
            foreach ($this->_segmentsToDelete as $segName) {
                foreach (self::$_indexExtensions as $ext) {
                    $this->_directory->purgeFile($segName . $ext);
                }
            }
        }

        // Clean-up _segmentsToDelete container
        $this->_segmentsToDelete = array();


        // Release index write lock
        Zend_Search_Lucene_LockManager::releaseWriteLock($this->_directory);

        // Remove unused segments from segments list
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            if (!isset($segments[$segName])) {
                unset($this->_segmentInfos[$segName]);
            }
        }
    }

    /**
     * Commit current changes
     */
    public function commit()
    {
        if ($this->_currentSegment !== null) {
            $newSegment = $this->_currentSegment->close();
            if ($newSegment !== null) {
                $this->_newSegments[$newSegment->getName()] = $newSegment;
            }
            $this->_currentSegment = null;
        }

        $this->_updateSegments();
    }


    /**
     * Merges the provided indexes into this index.
     *
     * @param array $readers
     * @return void
     */
    public function addIndexes($readers)
    {
        /**
         * @todo implementation
         */
    }

    /**
     * Merges all segments together into new one
     *
     * Returns true on success and false if another optimization or auto-optimization process
     * is running now
     *
     * @return boolean
     */
    public function optimize()
    {
        if (Zend_Search_Lucene_LockManager::obtainOptimizationLock($this->_directory) === false) {
            return false;
        }

        // Update segments list to be sure all segments are not merged yet by another process
        //
        // Segment merging functionality is concentrated in this class and surrounded
        // by optimization lock obtaining/releasing.
        // _updateSegments() refreshes segments list from the latest index generation.
        // So only new segments can be added to the index while we are merging some already existing
        // segments.
        // Newly added segments will be also included into the index by the _updateSegments() call
        // either by another process or by the current process with the commit() call at the end of _mergeSegments() method.
        // That's guaranteed by the serialisation of _updateSegments() execution using exclusive locks.
        $this->_updateSegments();

        $this->_mergeSegments($this->_segmentInfos);

        Zend_Search_Lucene_LockManager::releaseOptimizationLock($this->_directory);

        return true;
    }

    /**
     * Get name for new segment
     *
     * @return string
     */
    private function _newSegmentName()
    {
        Zend_Search_Lucene_LockManager::obtainWriteLock($this->_directory);

        $generation = Zend_Search_Lucene::getActualGeneration($this->_directory);
        $segmentsFile = $this->_directory->getFileObject(Zend_Search_Lucene::getSegmentFileName($generation), false);

        $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)
        $segmentNameCounter = $segmentsFile->readInt();

        $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)
        $segmentsFile->writeInt($segmentNameCounter + 1);

        // Flash output to guarantee that wrong value will not be loaded between unlock and
        // return (which calls $segmentsFile destructor)
        $segmentsFile->flush();

        Zend_Search_Lucene_LockManager::releaseWriteLock($this->_directory);

        return '_' . base_convert($segmentNameCounter, 10, 36);
    }

}
