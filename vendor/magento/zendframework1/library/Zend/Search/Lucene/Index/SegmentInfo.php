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

/** Zend_Search_Lucene_Index_TermsStream_Interface */
#require_once 'Zend/Search/Lucene/Index/TermsStream/Interface.php';


/** Zend_Search_Lucene_Search_Similarity */
#require_once 'Zend/Search/Lucene/Search/Similarity.php';

/** Zend_Search_Lucene_Index_FieldInfo */
#require_once 'Zend/Search/Lucene/Index/FieldInfo.php';

/** Zend_Search_Lucene_Index_Term */
#require_once 'Zend/Search/Lucene/Index/Term.php';

/** Zend_Search_Lucene_Index_TermInfo */
#require_once 'Zend/Search/Lucene/Index/TermInfo.php';

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_SegmentInfo implements Zend_Search_Lucene_Index_TermsStream_Interface
{
    /**
     * "Full scan vs fetch" boundary.
     *
     * If filter selectivity is less than this value, then full scan is performed
     * (since term entries fetching has some additional overhead).
     */
    const FULL_SCAN_VS_FETCH_BOUNDARY = 5;

    /**
     * Number of docs in a segment
     *
     * @var integer
     */
    private $_docCount;

    /**
     * Segment name
     *
     * @var string
     */
    private $_name;

    /**
     * Term Dictionary Index
     *
     * Array of arrays (Zend_Search_Lucene_Index_Term objects are represented as arrays because
     * of performance considerations)
     * [0] -> $termValue
     * [1] -> $termFieldNum
     *
     * Corresponding Zend_Search_Lucene_Index_TermInfo object stored in the $_termDictionaryInfos
     *
     * @var array
     */
    private $_termDictionary;

    /**
     * Term Dictionary Index TermInfos
     *
     * Array of arrays (Zend_Search_Lucene_Index_TermInfo objects are represented as arrays because
     * of performance considerations)
     * [0] -> $docFreq
     * [1] -> $freqPointer
     * [2] -> $proxPointer
     * [3] -> $skipOffset
     * [4] -> $indexPointer
     *
     * @var array
     */
    private $_termDictionaryInfos;

    /**
     * Segment fields. Array of Zend_Search_Lucene_Index_FieldInfo objects for this segment
     *
     * @var array
     */
    private $_fields;

    /**
     * Field positions in a dictionary.
     * (Term dictionary contains filelds ordered by names)
     *
     * @var array
     */
    private $_fieldsDicPositions;


    /**
     * Associative array where the key is the file name and the value is data offset
     * in a compound segment file (.csf).
     *
     * @var array
     */
    private $_segFiles;

    /**
     * Associative array where the key is the file name and the value is file size (.csf).
     *
     * @var array
     */
    private $_segFileSizes;

    /**
     * Delete file generation number
     *
     * -2 means autodetect latest delete generation
     * -1 means 'there is no delete file'
     *  0 means pre-2.1 format delete file
     *  X specifies used delete file
     *
     * @var integer
     */
    private $_delGen;

    /**
     * Segment has single norms file
     *
     * If true then one .nrm file is used for all fields
     * Otherwise .fN files are used
     *
     * @var boolean
     */
    private $_hasSingleNormFile;

    /**
     * Use compound segment file (*.cfs) to collect all other segment files
     * (excluding .del files)
     *
     * @var boolean
     */
    private $_isCompound;


    /**
     * File system adapter.
     *
     * @var Zend_Search_Lucene_Storage_Directory_Filesystem
     */
    private $_directory;

    /**
     * Normalization factors.
     * An array fieldName => normVector
     * normVector is a binary string.
     * Each byte corresponds to an indexed document in a segment and
     * encodes normalization factor (float value, encoded by
     * Zend_Search_Lucene_Search_Similarity::encodeNorm())
     *
     * @var array
     */
    private $_norms = array();

    /**
     * List of deleted documents.
     * bitset if bitset extension is loaded or array otherwise.
     *
     * @var mixed
     */
    private $_deleted = null;

    /**
     * $this->_deleted update flag
     *
     * @var boolean
     */
    private $_deletedDirty = false;

    /**
     * True if segment uses shared doc store
     *
     * @var boolean
     */
    private $_usesSharedDocStore;

    /*
     * Shared doc store options.
     * It's an assotiative array with the following items:
     * - 'offset'     => $docStoreOffset           The starting document in the shared doc store files where this segment's documents begin
     * - 'segment'    => $docStoreSegment          The name of the segment that has the shared doc store files.
     * - 'isCompound' => $docStoreIsCompoundFile   True, if compound file format is used for the shared doc store files (.cfx file).
     */
    private $_sharedDocStoreOptions;


    /**
     * Zend_Search_Lucene_Index_SegmentInfo constructor
     *
     * @param Zend_Search_Lucene_Storage_Directory $directory
     * @param string     $name
     * @param integer    $docCount
     * @param integer    $delGen
     * @param array|null $docStoreOptions
     * @param boolean    $hasSingleNormFile
     * @param boolean    $isCompound
     */
    public function __construct(Zend_Search_Lucene_Storage_Directory $directory, $name, $docCount, $delGen = 0, $docStoreOptions = null, $hasSingleNormFile = false, $isCompound = null)
    {
        $this->_directory = $directory;
        $this->_name      = $name;
        $this->_docCount  = $docCount;

        if ($docStoreOptions !== null) {
            $this->_usesSharedDocStore    = true;
            $this->_sharedDocStoreOptions = $docStoreOptions;

            if ($docStoreOptions['isCompound']) {
                $cfxFile       = $this->_directory->getFileObject($docStoreOptions['segment'] . '.cfx');
                $cfxFilesCount = $cfxFile->readVInt();

                $cfxFiles     = array();
                $cfxFileSizes = array();

                for ($count = 0; $count < $cfxFilesCount; $count++) {
                    $dataOffset = $cfxFile->readLong();
                    if ($count != 0) {
                        $cfxFileSizes[$fileName] = $dataOffset - end($cfxFiles);
                    }
                    $fileName            = $cfxFile->readString();
                    $cfxFiles[$fileName] = $dataOffset;
                }
                if ($count != 0) {
                    $cfxFileSizes[$fileName] = $this->_directory->fileLength($docStoreOptions['segment'] . '.cfx') - $dataOffset;
                }

                $this->_sharedDocStoreOptions['files']     = $cfxFiles;
                $this->_sharedDocStoreOptions['fileSizes'] = $cfxFileSizes;
            }
        }

        $this->_hasSingleNormFile = $hasSingleNormFile;
        $this->_delGen            = $delGen;
        $this->_termDictionary    = null;


        if ($isCompound !== null) {
            $this->_isCompound    = $isCompound;
        } else {
            // It's a pre-2.1 segment or isCompound is set to 'unknown'
            // Detect if segment uses compound file
            #require_once 'Zend/Search/Lucene/Exception.php';
            try {
                // Try to open compound file
                $this->_directory->getFileObject($name . '.cfs');

                // Compound file is found
                $this->_isCompound = true;
            } catch (Zend_Search_Lucene_Exception $e) {
                if (strpos($e->getMessage(), 'is not readable') !== false) {
                    // Compound file is not found or is not readable
                    $this->_isCompound = false;
                } else {
                    throw new Zend_Search_Lucene_Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        $this->_segFiles = array();
        if ($this->_isCompound) {
            $cfsFile = $this->_directory->getFileObject($name . '.cfs');
            $segFilesCount = $cfsFile->readVInt();

            for ($count = 0; $count < $segFilesCount; $count++) {
                $dataOffset = $cfsFile->readLong();
                if ($count != 0) {
                    $this->_segFileSizes[$fileName] = $dataOffset - end($this->_segFiles);
                }
                $fileName = $cfsFile->readString();
                $this->_segFiles[$fileName] = $dataOffset;
            }
            if ($count != 0) {
                $this->_segFileSizes[$fileName] = $this->_directory->fileLength($name . '.cfs') - $dataOffset;
            }
        }

        $fnmFile = $this->openCompoundFile('.fnm');
        $fieldsCount = $fnmFile->readVInt();
        $fieldNames = array();
        $fieldNums  = array();
        $this->_fields = array();

        for ($count=0; $count < $fieldsCount; $count++) {
            $fieldName = $fnmFile->readString();
            $fieldBits = $fnmFile->readByte();
            $this->_fields[$count] = new Zend_Search_Lucene_Index_FieldInfo($fieldName,
                                                                            $fieldBits & 0x01 /* field is indexed */,
                                                                            $count,
                                                                            $fieldBits & 0x02 /* termvectors are stored */,
                                                                            $fieldBits & 0x10 /* norms are omitted */,
                                                                            $fieldBits & 0x20 /* payloads are stored */);
            if ($fieldBits & 0x10) {
                // norms are omitted for the indexed field
                $this->_norms[$count] = str_repeat(chr(Zend_Search_Lucene_Search_Similarity::encodeNorm(1.0)), $docCount);
            }

            $fieldNums[$count]  = $count;
            $fieldNames[$count] = $fieldName;
        }
        array_multisort($fieldNames, SORT_ASC, SORT_REGULAR, $fieldNums);
        $this->_fieldsDicPositions = array_flip($fieldNums);

        if ($this->_delGen == -2) {
            // SegmentInfo constructor is invoked from index writer
            // Autodetect current delete file generation number
            $this->_delGen = $this->_detectLatestDelGen();
        }

        // Load deletions
        $this->_deleted = $this->_loadDelFile();
    }

    /**
     * Load detetions file
     *
     * Returns bitset or an array depending on bitset extension availability
     *
     * @return mixed
     * @throws Zend_Search_Lucene_Exception
     */
    private function _loadDelFile()
    {
        if ($this->_delGen == -1) {
            // There is no delete file for this segment
            return null;
        } else if ($this->_delGen == 0) {
            // It's a segment with pre-2.1 format delete file
            // Try to load deletions file
            return $this->_loadPre21DelFile();
        } else {
            // It's 2.1+ format deleteions file
            return $this->_load21DelFile();
        }
    }

    /**
     * Load pre-2.1 detetions file
     *
     * Returns bitset or an array depending on bitset extension availability
     *
     * @return mixed
     * @throws Zend_Search_Lucene_Exception
     */
    private function _loadPre21DelFile()
    {
        #require_once 'Zend/Search/Lucene/Exception.php';
        try {
            // '.del' files always stored in a separate file
            // Segment compound is not used
            $delFile = $this->_directory->getFileObject($this->_name . '.del');

            $byteCount = $delFile->readInt();
            $byteCount = ceil($byteCount/8);
            $bitCount  = $delFile->readInt();

            if ($bitCount == 0) {
                $delBytes = '';
            } else {
                $delBytes = $delFile->readBytes($byteCount);
            }

            if (extension_loaded('bitset')) {
                return $delBytes;
            } else {
                $deletions = array();
                for ($count = 0; $count < $byteCount; $count++) {
                    $byte = ord($delBytes[$count]);
                    for ($bit = 0; $bit < 8; $bit++) {
                        if ($byte & (1<<$bit)) {
                            $deletions[$count*8 + $bit] = 1;
                        }
                    }
                }

                return $deletions;
            }
        } catch(Zend_Search_Lucene_Exception $e) {
            if (strpos($e->getMessage(), 'is not readable') === false) {
                throw new Zend_Search_Lucene_Exception($e->getMessage(), $e->getCode(), $e);
            }
            // There is no deletion file
            $this->_delGen = -1;

            return null;
        }
    }

    /**
     * Load 2.1+ format detetions file
     *
     * Returns bitset or an array depending on bitset extension availability
     *
     * @return mixed
     */
    private function _load21DelFile()
    {
        $delFile = $this->_directory->getFileObject($this->_name . '_' . base_convert($this->_delGen, 10, 36) . '.del');

        $format = $delFile->readInt();

        if ($format == (int)0xFFFFFFFF) {
            if (extension_loaded('bitset')) {
                $deletions = bitset_empty();
            } else {
                $deletions = array();
            }

            $byteCount = $delFile->readInt();
            $bitCount  = $delFile->readInt();

            $delFileSize = $this->_directory->fileLength($this->_name . '_' . base_convert($this->_delGen, 10, 36) . '.del');
            $byteNum = 0;

            do {
                $dgap = $delFile->readVInt();
                $nonZeroByte = $delFile->readByte();

                $byteNum += $dgap;


                if (extension_loaded('bitset')) {
                    for ($bit = 0; $bit < 8; $bit++) {
                        if ($nonZeroByte & (1<<$bit)) {
                            bitset_incl($deletions, $byteNum*8 + $bit);
                        }
                    }
                    return $deletions;
                } else {
                    for ($bit = 0; $bit < 8; $bit++) {
                        if ($nonZeroByte & (1<<$bit)) {
                            $deletions[$byteNum*8 + $bit] = 1;
                        }
                    }
                    return (count($deletions) > 0) ? $deletions : null;
                }

            } while ($delFile->tell() < $delFileSize);
        } else {
            // $format is actually byte count
            $byteCount = ceil($format/8);
            $bitCount  = $delFile->readInt();

            if ($bitCount == 0) {
                $delBytes = '';
            } else {
                $delBytes = $delFile->readBytes($byteCount);
            }

            if (extension_loaded('bitset')) {
                return $delBytes;
            } else {
                $deletions = array();
                for ($count = 0; $count < $byteCount; $count++) {
                    $byte = ord($delBytes[$count]);
                    for ($bit = 0; $bit < 8; $bit++) {
                        if ($byte & (1<<$bit)) {
                            $deletions[$count*8 + $bit] = 1;
                        }
                    }
                }

                return (count($deletions) > 0) ? $deletions : null;
            }
        }
    }

    /**
     * Opens index file stoted within compound index file
     *
     * @param string $extension
     * @param boolean $shareHandler
     * @throws Zend_Search_Lucene_Exception
     * @return Zend_Search_Lucene_Storage_File
     */
    public function openCompoundFile($extension, $shareHandler = true)
    {
        if (($extension == '.fdx'  || $extension == '.fdt')  &&  $this->_usesSharedDocStore) {
            $fdxFName = $this->_sharedDocStoreOptions['segment'] . '.fdx';
            $fdtFName = $this->_sharedDocStoreOptions['segment'] . '.fdt';

            if (!$this->_sharedDocStoreOptions['isCompound']) {
                $fdxFile = $this->_directory->getFileObject($fdxFName, $shareHandler);
                $fdxFile->seek($this->_sharedDocStoreOptions['offset']*8, SEEK_CUR);

                if ($extension == '.fdx') {
                    // '.fdx' file is requested
                    return $fdxFile;
                } else {
                    // '.fdt' file is requested
                    $fdtStartOffset = $fdxFile->readLong();

                    $fdtFile = $this->_directory->getFileObject($fdtFName, $shareHandler);
                    $fdtFile->seek($fdtStartOffset, SEEK_CUR);

                    return $fdtFile;
                }
            }

            if( !isset($this->_sharedDocStoreOptions['files'][$fdxFName]) ) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Shared doc storage segment compound file doesn\'t contain '
                                       . $fdxFName . ' file.' );
            }
            if( !isset($this->_sharedDocStoreOptions['files'][$fdtFName]) ) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Shared doc storage segment compound file doesn\'t contain '
                                       . $fdtFName . ' file.' );
            }

            // Open shared docstore segment file
            $cfxFile = $this->_directory->getFileObject($this->_sharedDocStoreOptions['segment'] . '.cfx', $shareHandler);
            // Seek to the start of '.fdx' file within compound file
            $cfxFile->seek($this->_sharedDocStoreOptions['files'][$fdxFName]);
            // Seek to the start of current segment documents section
            $cfxFile->seek($this->_sharedDocStoreOptions['offset']*8, SEEK_CUR);

            if ($extension == '.fdx') {
                // '.fdx' file is requested
                return $cfxFile;
            } else {
                // '.fdt' file is requested
                $fdtStartOffset = $cfxFile->readLong();

                // Seek to the start of '.fdt' file within compound file
                $cfxFile->seek($this->_sharedDocStoreOptions['files'][$fdtFName]);
                // Seek to the start of current segment documents section
                $cfxFile->seek($fdtStartOffset, SEEK_CUR);

                return $fdtFile;
            }
        }

        $filename = $this->_name . $extension;

        if (!$this->_isCompound) {
            return $this->_directory->getFileObject($filename, $shareHandler);
        }

        if( !isset($this->_segFiles[$filename]) ) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Segment compound file doesn\'t contain '
                                       . $filename . ' file.' );
        }

        $file = $this->_directory->getFileObject($this->_name . '.cfs', $shareHandler);
        $file->seek($this->_segFiles[$filename]);
        return $file;
    }

    /**
     * Get compound file length
     *
     * @param string $extension
     * @return integer
     */
    public function compoundFileLength($extension)
    {
        if (($extension == '.fdx'  || $extension == '.fdt')  &&  $this->_usesSharedDocStore) {
            $filename = $this->_sharedDocStoreOptions['segment'] . $extension;

            if (!$this->_sharedDocStoreOptions['isCompound']) {
                return $this->_directory->fileLength($filename);
            }

            if( !isset($this->_sharedDocStoreOptions['fileSizes'][$filename]) ) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Shared doc store compound file doesn\'t contain '
                                           . $filename . ' file.' );
            }

            return $this->_sharedDocStoreOptions['fileSizes'][$filename];
        }


        $filename = $this->_name . $extension;

        // Try to get common file first
        if ($this->_directory->fileExists($filename)) {
            return $this->_directory->fileLength($filename);
        }

        if( !isset($this->_segFileSizes[$filename]) ) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Index compound file doesn\'t contain '
                                       . $filename . ' file.' );
        }

        return $this->_segFileSizes[$filename];
    }

    /**
     * Returns field index or -1 if field is not found
     *
     * @param string $fieldName
     * @return integer
     */
    public function getFieldNum($fieldName)
    {
        foreach( $this->_fields as $field ) {
            if( $field->name == $fieldName ) {
                return $field->number;
            }
        }

        return -1;
    }

    /**
     * Returns field info for specified field
     *
     * @param integer $fieldNum
     * @return Zend_Search_Lucene_Index_FieldInfo
     */
    public function getField($fieldNum)
    {
        return $this->_fields[$fieldNum];
    }

    /**
     * Returns array of fields.
     * if $indexed parameter is true, then returns only indexed fields.
     *
     * @param boolean $indexed
     * @return array
     */
    public function getFields($indexed = false)
    {
        $result = array();
        foreach( $this->_fields as $field ) {
            if( (!$indexed) || $field->isIndexed ) {
                $result[ $field->name ] = $field->name;
            }
        }
        return $result;
    }

    /**
     * Returns array of FieldInfo objects.
     *
     * @return array
     */
    public function getFieldInfos()
    {
        return $this->_fields;
    }

    /**
     * Returns actual deletions file generation number.
     *
     * @return integer
     */
    public function getDelGen()
    {
        return $this->_delGen;
    }

    /**
     * Returns the total number of documents in this segment (including deleted documents).
     *
     * @return integer
     */
    public function count()
    {
        return $this->_docCount;
    }

    /**
     * Returns number of deleted documents.
     *
     * @return integer
     */
    private function _deletedCount()
    {
        if ($this->_deleted === null) {
            return 0;
        }

        if (extension_loaded('bitset')) {
            return count(bitset_to_array($this->_deleted));
        } else {
            return count($this->_deleted);
        }
    }

    /**
     * Returns the total number of non-deleted documents in this segment.
     *
     * @return integer
     */
    public function numDocs()
    {
        if ($this->hasDeletions()) {
            return $this->_docCount - $this->_deletedCount();
        } else {
            return $this->_docCount;
        }
    }

    /**
     * Get field position in a fields dictionary
     *
     * @param integer $fieldNum
     * @return integer
     */
    private function _getFieldPosition($fieldNum) {
        // Treat values which are not in a translation table as a 'direct value'
        return isset($this->_fieldsDicPositions[$fieldNum]) ?
                           $this->_fieldsDicPositions[$fieldNum] : $fieldNum;
    }

    /**
     * Return segment name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }


    /**
     * TermInfo cache
     *
     * Size is 1024.
     * Numbers are used instead of class constants because of performance considerations
     *
     * @var array
     */
    private $_termInfoCache = array();

    private function _cleanUpTermInfoCache()
    {
        // Clean 256 term infos
        foreach ($this->_termInfoCache as $key => $termInfo) {
            unset($this->_termInfoCache[$key]);

            // leave 768 last used term infos
            if (count($this->_termInfoCache) == 768) {
                break;
            }
        }
    }

    /**
     * Load terms dictionary index
     *
     * @throws Zend_Search_Lucene_Exception
     */
    private function _loadDictionaryIndex()
    {
        // Check, if index is already serialized
        if ($this->_directory->fileExists($this->_name . '.sti')) {
            // Load serialized dictionary index data
            $stiFile = $this->_directory->getFileObject($this->_name . '.sti');
            $stiFileData = $stiFile->readBytes($this->_directory->fileLength($this->_name . '.sti'));

            // Load dictionary index data
            if (($unserializedData = @unserialize($stiFileData)) !== false) {
                list($this->_termDictionary, $this->_termDictionaryInfos) = $unserializedData;
                return;
            }
        }

        // Load data from .tii file and generate .sti file

        // Prefetch dictionary index data
        $tiiFile = $this->openCompoundFile('.tii');
        $tiiFileData = $tiiFile->readBytes($this->compoundFileLength('.tii'));

        /** Zend_Search_Lucene_Index_DictionaryLoader */
        #require_once 'Zend/Search/Lucene/Index/DictionaryLoader.php';

        // Load dictionary index data
        list($this->_termDictionary, $this->_termDictionaryInfos) =
                    Zend_Search_Lucene_Index_DictionaryLoader::load($tiiFileData);

        $stiFileData = serialize(array($this->_termDictionary, $this->_termDictionaryInfos));
        $stiFile = $this->_directory->createFile($this->_name . '.sti');
        $stiFile->writeBytes($stiFileData);
    }

    /**
     * Scans terms dictionary and returns term info
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @return Zend_Search_Lucene_Index_TermInfo
     */
    public function getTermInfo(Zend_Search_Lucene_Index_Term $term)
    {
        $termKey = $term->key();
        if (isset($this->_termInfoCache[$termKey])) {
            $termInfo = $this->_termInfoCache[$termKey];

            // Move termInfo to the end of cache
            unset($this->_termInfoCache[$termKey]);
            $this->_termInfoCache[$termKey] = $termInfo;

            return $termInfo;
        }


        if ($this->_termDictionary === null) {
            $this->_loadDictionaryIndex();
        }

        $searchField = $this->getFieldNum($term->field);

        if ($searchField == -1) {
            return null;
        }
        $searchDicField = $this->_getFieldPosition($searchField);

        // search for appropriate value in dictionary
        $lowIndex = 0;
        $highIndex = count($this->_termDictionary)-1;
        while ($highIndex >= $lowIndex) {
            // $mid = ($highIndex - $lowIndex)/2;
            $mid = ($highIndex + $lowIndex) >> 1;
            $midTerm = $this->_termDictionary[$mid];

            $fieldNum = $this->_getFieldPosition($midTerm[0] /* field */);
            $delta = $searchDicField - $fieldNum;
            if ($delta == 0) {
                $delta = strcmp($term->text, $midTerm[1] /* text */);
            }

            if ($delta < 0) {
                $highIndex = $mid-1;
            } elseif ($delta > 0) {
                $lowIndex  = $mid+1;
            } else {
                // return $this->_termDictionaryInfos[$mid]; // We got it!
                $a = $this->_termDictionaryInfos[$mid];
                $termInfo = new Zend_Search_Lucene_Index_TermInfo($a[0], $a[1], $a[2], $a[3], $a[4]);

                // Put loaded termInfo into cache
                $this->_termInfoCache[$termKey] = $termInfo;

                return $termInfo;
            }
        }

        if ($highIndex == -1) {
            // Term is out of the dictionary range
            return null;
        }

        $prevPosition = $highIndex;
        $prevTerm = $this->_termDictionary[$prevPosition];
        $prevTermInfo = $this->_termDictionaryInfos[$prevPosition];

        $tisFile = $this->openCompoundFile('.tis');
        $tiVersion = $tisFile->readInt();
        if ($tiVersion != (int)0xFFFFFFFE /* pre-2.1 format */  &&
            $tiVersion != (int)0xFFFFFFFD /* 2.1+ format    */) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Wrong TermInfoFile file format');
        }

        $termCount     = $tisFile->readLong();
        $indexInterval = $tisFile->readInt();
        $skipInterval  = $tisFile->readInt();
        if ($tiVersion == (int)0xFFFFFFFD /* 2.1+ format */) {
            $maxSkipLevels = $tisFile->readInt();
        }

        $tisFile->seek($prevTermInfo[4] /* indexPointer */ - (($tiVersion == (int)0xFFFFFFFD)? 24 : 20) /* header size*/, SEEK_CUR);

        $termValue    = $prevTerm[1] /* text */;
        $termFieldNum = $prevTerm[0] /* field */;
        $freqPointer = $prevTermInfo[1] /* freqPointer */;
        $proxPointer = $prevTermInfo[2] /* proxPointer */;
        for ($count = $prevPosition*$indexInterval + 1;
             $count <= $termCount &&
             ( $this->_getFieldPosition($termFieldNum) < $searchDicField ||
              ($this->_getFieldPosition($termFieldNum) == $searchDicField &&
               strcmp($termValue, $term->text) < 0) );
             $count++) {
            $termPrefixLength = $tisFile->readVInt();
            $termSuffix       = $tisFile->readString();
            $termFieldNum     = $tisFile->readVInt();
            $termValue        = Zend_Search_Lucene_Index_Term::getPrefix($termValue, $termPrefixLength) . $termSuffix;

            $docFreq      = $tisFile->readVInt();
            $freqPointer += $tisFile->readVInt();
            $proxPointer += $tisFile->readVInt();
            if( $docFreq >= $skipInterval ) {
                $skipOffset = $tisFile->readVInt();
            } else {
                $skipOffset = 0;
            }
        }

        if ($termFieldNum == $searchField && $termValue == $term->text) {
            $termInfo = new Zend_Search_Lucene_Index_TermInfo($docFreq, $freqPointer, $proxPointer, $skipOffset);
        } else {
            $termInfo = null;
        }

        // Put loaded termInfo into cache
        $this->_termInfoCache[$termKey] = $termInfo;

        if (count($this->_termInfoCache) == 1024) {
            $this->_cleanUpTermInfoCache();
        }

        return $termInfo;
    }

    /**
     * Returns IDs of all the documents containing term.
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param integer $shift
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return array
     */
    public function termDocs(Zend_Search_Lucene_Index_Term $term, $shift = 0, $docsFilter = null)
    {
        $termInfo = $this->getTermInfo($term);

        if (!$termInfo instanceof Zend_Search_Lucene_Index_TermInfo) {
            if ($docsFilter !== null  &&  $docsFilter instanceof Zend_Search_Lucene_Index_DocsFilter) {
                $docsFilter->segmentFilters[$this->_name] = array();
            }
            return array();
        }

        $frqFile = $this->openCompoundFile('.frq');
        $frqFile->seek($termInfo->freqPointer,SEEK_CUR);
        $docId  = 0;
        $result = array();

        if ($docsFilter !== null) {
            if (!$docsFilter instanceof Zend_Search_Lucene_Index_DocsFilter) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Documents filter must be an instance of Zend_Search_Lucene_Index_DocsFilter or null.');
            }

            if (isset($docsFilter->segmentFilters[$this->_name])) {
                // Filter already has some data for the current segment

                // Make short name for the filter (which doesn't need additional dereferencing)
                $filter = &$docsFilter->segmentFilters[$this->_name];

                // Check if filter is not empty
                if (count($filter) == 0) {
                    return array();
                }

                if ($this->_docCount/count($filter) < self::FULL_SCAN_VS_FETCH_BOUNDARY) {
                    // Perform fetching
// ---------------------------------------------------------------
                    $updatedFilterData = array();

                    for( $count=0; $count < $termInfo->docFreq; $count++ ) {
                        $docDelta = $frqFile->readVInt();
                        if( $docDelta % 2 == 1 ) {
                            $docId += ($docDelta-1)/2;
                        } else {
                            $docId += $docDelta/2;
                            // read freq
                            $frqFile->readVInt();
                        }

                        if (isset($filter[$docId])) {
                           $result[] = $shift + $docId;
                           $updatedFilterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                        }
                    }
                    $docsFilter->segmentFilters[$this->_name] = $updatedFilterData;
// ---------------------------------------------------------------
                } else {
                    // Perform full scan
                    $updatedFilterData = array();

                    for( $count=0; $count < $termInfo->docFreq; $count++ ) {
                        $docDelta = $frqFile->readVInt();
                        if( $docDelta % 2 == 1 ) {
                            $docId += ($docDelta-1)/2;
                        } else {
                            $docId += $docDelta/2;
                            // read freq
                            $frqFile->readVInt();
                        }

                        if (isset($filter[$docId])) {
                           $result[] = $shift + $docId;
                           $updatedFilterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                        }
                    }
                    $docsFilter->segmentFilters[$this->_name] = $updatedFilterData;
                }
            } else {
                // Filter is present, but doesn't has data for the current segment yet
                $filterData = array();
                for( $count=0; $count < $termInfo->docFreq; $count++ ) {
                    $docDelta = $frqFile->readVInt();
                    if( $docDelta % 2 == 1 ) {
                        $docId += ($docDelta-1)/2;
                    } else {
                        $docId += $docDelta/2;
                        // read freq
                        $frqFile->readVInt();
                    }

                    $result[] = $shift + $docId;
                    $filterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                }
                $docsFilter->segmentFilters[$this->_name] = $filterData;
            }
        } else {
            for( $count=0; $count < $termInfo->docFreq; $count++ ) {
                $docDelta = $frqFile->readVInt();
                if( $docDelta % 2 == 1 ) {
                    $docId += ($docDelta-1)/2;
                } else {
                    $docId += $docDelta/2;
                    // read freq
                    $frqFile->readVInt();
                }

                $result[] = $shift + $docId;
            }
        }

        return $result;
    }

    /**
     * Returns term freqs array.
     * Result array structure: array(docId => freq, ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param integer $shift
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return Zend_Search_Lucene_Index_TermInfo
     */
    public function termFreqs(Zend_Search_Lucene_Index_Term $term, $shift = 0, $docsFilter = null)
    {
        $termInfo = $this->getTermInfo($term);

        if (!$termInfo instanceof Zend_Search_Lucene_Index_TermInfo) {
            if ($docsFilter !== null  &&  $docsFilter instanceof Zend_Search_Lucene_Index_DocsFilter) {
                $docsFilter->segmentFilters[$this->_name] = array();
            }
            return array();
        }

        $frqFile = $this->openCompoundFile('.frq');
        $frqFile->seek($termInfo->freqPointer,SEEK_CUR);
        $result = array();
        $docId = 0;

        $result = array();

        if ($docsFilter !== null) {
            if (!$docsFilter instanceof Zend_Search_Lucene_Index_DocsFilter) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Documents filter must be an instance of Zend_Search_Lucene_Index_DocsFilter or null.');
            }

            if (isset($docsFilter->segmentFilters[$this->_name])) {
                // Filter already has some data for the current segment

                // Make short name for the filter (which doesn't need additional dereferencing)
                $filter = &$docsFilter->segmentFilters[$this->_name];

                // Check if filter is not empty
                if (count($filter) == 0) {
                    return array();
                }


                if ($this->_docCount/count($filter) < self::FULL_SCAN_VS_FETCH_BOUNDARY) {
                    // Perform fetching
// ---------------------------------------------------------------
                    $updatedFilterData = array();

                    for ($count = 0; $count < $termInfo->docFreq; $count++) {
                        $docDelta = $frqFile->readVInt();
                        if ($docDelta % 2 == 1) {
                            $docId += ($docDelta-1)/2;
                            if (isset($filter[$docId])) {
                                $result[$shift + $docId] = 1;
                                $updatedFilterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                            }
                        } else {
                            $docId += $docDelta/2;
                            $freq = $frqFile->readVInt();
                            if (isset($filter[$docId])) {
                                $result[$shift + $docId] = $freq;
                                $updatedFilterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                            }
                        }
                    }
                    $docsFilter->segmentFilters[$this->_name] = $updatedFilterData;
// ---------------------------------------------------------------
                } else {
                    // Perform full scan
                    $updatedFilterData = array();

                    for ($count = 0; $count < $termInfo->docFreq; $count++) {
                        $docDelta = $frqFile->readVInt();
                        if ($docDelta % 2 == 1) {
                            $docId += ($docDelta-1)/2;
                            if (isset($filter[$docId])) {
                                $result[$shift + $docId] = 1;
                                $updatedFilterData[$docId] = 1; // 1 is just some constant value, so we don't need additional var dereference here
                            }
                        } else {
                            $docId += $docDelta/2;
                            $freq = $frqFile->readVInt();
                            if (isset($filter[$docId])) {
                                $result[$shift + $docId] = $freq;
                                $updatedFilterData[$docId] = 1; // 1 is just some constant value, so we don't need additional var dereference here
                            }
                        }
                    }
                    $docsFilter->segmentFilters[$this->_name] = $updatedFilterData;
                }
            } else {
                // Filter doesn't has data for current segment
                $filterData = array();

                for ($count = 0; $count < $termInfo->docFreq; $count++) {
                    $docDelta = $frqFile->readVInt();
                    if ($docDelta % 2 == 1) {
                        $docId += ($docDelta-1)/2;
                        $result[$shift + $docId] = 1;
                        $filterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                    } else {
                        $docId += $docDelta/2;
                        $result[$shift + $docId] = $frqFile->readVInt();
                        $filterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                    }
                }

                $docsFilter->segmentFilters[$this->_name] = $filterData;
            }
        } else {
            for ($count = 0; $count < $termInfo->docFreq; $count++) {
                $docDelta = $frqFile->readVInt();
                if ($docDelta % 2 == 1) {
                    $docId += ($docDelta-1)/2;
                    $result[$shift + $docId] = 1;
                } else {
                    $docId += $docDelta/2;
                    $result[$shift + $docId] = $frqFile->readVInt();
                }
            }
        }

        return $result;
    }

    /**
     * Returns term positions array.
     * Result array structure: array(docId => array(pos1, pos2, ...), ...)
     *
     * @param Zend_Search_Lucene_Index_Term $term
     * @param integer $shift
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @return Zend_Search_Lucene_Index_TermInfo
     */
    public function termPositions(Zend_Search_Lucene_Index_Term $term, $shift = 0, $docsFilter = null)
    {
        $termInfo = $this->getTermInfo($term);

        if (!$termInfo instanceof Zend_Search_Lucene_Index_TermInfo) {
            if ($docsFilter !== null  &&  $docsFilter instanceof Zend_Search_Lucene_Index_DocsFilter) {
                $docsFilter->segmentFilters[$this->_name] = array();
            }
            return array();
        }

        $frqFile = $this->openCompoundFile('.frq');
        $frqFile->seek($termInfo->freqPointer,SEEK_CUR);

        $docId = 0;
        $freqs = array();


        if ($docsFilter !== null) {
            if (!$docsFilter instanceof Zend_Search_Lucene_Index_DocsFilter) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Documents filter must be an instance of Zend_Search_Lucene_Index_DocsFilter or null.');
            }

            if (isset($docsFilter->segmentFilters[$this->_name])) {
                // Filter already has some data for the current segment

                // Make short name for the filter (which doesn't need additional dereferencing)
                $filter = &$docsFilter->segmentFilters[$this->_name];

                // Check if filter is not empty
                if (count($filter) == 0) {
                    return array();
                }

                if ($this->_docCount/count($filter) < self::FULL_SCAN_VS_FETCH_BOUNDARY) {
                    // Perform fetching
// ---------------------------------------------------------------
                    for ($count = 0; $count < $termInfo->docFreq; $count++) {
                        $docDelta = $frqFile->readVInt();
                        if ($docDelta % 2 == 1) {
                            $docId += ($docDelta-1)/2;
                            $freqs[$docId] = 1;
                        } else {
                            $docId += $docDelta/2;
                            $freqs[$docId] = $frqFile->readVInt();
                        }
                    }

                    $updatedFilterData = array();
                    $result = array();
                    $prxFile = $this->openCompoundFile('.prx');
                    $prxFile->seek($termInfo->proxPointer, SEEK_CUR);
                    foreach ($freqs as $docId => $freq) {
                        $termPosition = 0;
                        $positions = array();

                        // we have to read .prx file to get right position for next doc
                        // even filter doesn't match current document
                        for ($count = 0; $count < $freq; $count++ ) {
                            $termPosition += $prxFile->readVInt();
                            $positions[] = $termPosition;
                        }

                        // Include into updated filter and into result only if doc is matched by filter
                        if (isset($filter[$docId])) {
                            $updatedFilterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                            $result[$shift + $docId] = $positions;
                        }
                    }

                    $docsFilter->segmentFilters[$this->_name] = $updatedFilterData;
// ---------------------------------------------------------------
                } else {
                    // Perform full scan
                    for ($count = 0; $count < $termInfo->docFreq; $count++) {
                        $docDelta = $frqFile->readVInt();
                        if ($docDelta % 2 == 1) {
                            $docId += ($docDelta-1)/2;
                            $freqs[$docId] = 1;
                        } else {
                            $docId += $docDelta/2;
                            $freqs[$docId] = $frqFile->readVInt();
                        }
                    }

                    $updatedFilterData = array();
                    $result = array();
                    $prxFile = $this->openCompoundFile('.prx');
                    $prxFile->seek($termInfo->proxPointer, SEEK_CUR);
                    foreach ($freqs as $docId => $freq) {
                        $termPosition = 0;
                        $positions = array();

                        // we have to read .prx file to get right position for next doc
                        // even filter doesn't match current document
                        for ($count = 0; $count < $freq; $count++ ) {
                            $termPosition += $prxFile->readVInt();
                            $positions[] = $termPosition;
                        }

                        // Include into updated filter and into result only if doc is matched by filter
                        if (isset($filter[$docId])) {
                            $updatedFilterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here
                            $result[$shift + $docId] = $positions;
                        }
                    }

                    $docsFilter->segmentFilters[$this->_name] = $updatedFilterData;
                }
            } else {
                // Filter doesn't has data for current segment
                for ($count = 0; $count < $termInfo->docFreq; $count++) {
                    $docDelta = $frqFile->readVInt();
                    if ($docDelta % 2 == 1) {
                        $docId += ($docDelta-1)/2;
                        $freqs[$docId] = 1;
                    } else {
                        $docId += $docDelta/2;
                        $freqs[$docId] = $frqFile->readVInt();
                    }
                }

                $filterData = array();
                $result = array();
                $prxFile = $this->openCompoundFile('.prx');
                $prxFile->seek($termInfo->proxPointer, SEEK_CUR);
                foreach ($freqs as $docId => $freq) {
                    $filterData[$docId] = 1; // 1 is just a some constant value, so we don't need additional var dereference here

                    $termPosition = 0;
                    $positions = array();

                    for ($count = 0; $count < $freq; $count++ ) {
                        $termPosition += $prxFile->readVInt();
                        $positions[] = $termPosition;
                    }

                    $result[$shift + $docId] = $positions;
                }

                $docsFilter->segmentFilters[$this->_name] = $filterData;
            }
        } else {
            for ($count = 0; $count < $termInfo->docFreq; $count++) {
                $docDelta = $frqFile->readVInt();
                if ($docDelta % 2 == 1) {
                    $docId += ($docDelta-1)/2;
                    $freqs[$docId] = 1;
                } else {
                    $docId += $docDelta/2;
                    $freqs[$docId] = $frqFile->readVInt();
                }
            }

            $result = array();
            $prxFile = $this->openCompoundFile('.prx');
            $prxFile->seek($termInfo->proxPointer, SEEK_CUR);
            foreach ($freqs as $docId => $freq) {
                $termPosition = 0;
                $positions = array();

                for ($count = 0; $count < $freq; $count++ ) {
                    $termPosition += $prxFile->readVInt();
                    $positions[] = $termPosition;
                }

                $result[$shift + $docId] = $positions;
            }
        }

        return $result;
    }

    /**
     * Load normalizatin factors from an index file
     *
     * @param integer $fieldNum
     * @throws Zend_Search_Lucene_Exception
     */
    private function _loadNorm($fieldNum)
    {
        if ($this->_hasSingleNormFile) {
            $normfFile = $this->openCompoundFile('.nrm');

            $header              = $normfFile->readBytes(3);
            $headerFormatVersion = $normfFile->readByte();

            if ($header != 'NRM'  ||  $headerFormatVersion != (int)0xFF) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new  Zend_Search_Lucene_Exception('Wrong norms file format.');
            }

            foreach ($this->_fields as $fNum => $fieldInfo) {
                if ($fieldInfo->isIndexed) {
                    $this->_norms[$fNum] = $normfFile->readBytes($this->_docCount);
                }
            }
        } else {
            $fFile = $this->openCompoundFile('.f' . $fieldNum);
            $this->_norms[$fieldNum] = $fFile->readBytes($this->_docCount);
        }
    }

    /**
     * Returns normalization factor for specified documents
     *
     * @param integer $id
     * @param string $fieldName
     * @return float
     */
    public function norm($id, $fieldName)
    {
        $fieldNum = $this->getFieldNum($fieldName);

        if ( !($this->_fields[$fieldNum]->isIndexed) ) {
            return null;
        }

        if (!isset($this->_norms[$fieldNum])) {
            $this->_loadNorm($fieldNum);
        }

        return Zend_Search_Lucene_Search_Similarity::decodeNorm( ord($this->_norms[$fieldNum][$id]) );
    }

    /**
     * Returns norm vector, encoded in a byte string
     *
     * @param string $fieldName
     * @return string
     */
    public function normVector($fieldName)
    {
        $fieldNum = $this->getFieldNum($fieldName);

        if ($fieldNum == -1  ||  !($this->_fields[$fieldNum]->isIndexed)) {
            $similarity = Zend_Search_Lucene_Search_Similarity::getDefault();

            return str_repeat(chr($similarity->encodeNorm( $similarity->lengthNorm($fieldName, 0) )),
                              $this->_docCount);
        }

        if (!isset($this->_norms[$fieldNum])) {
            $this->_loadNorm($fieldNum);
        }

        return $this->_norms[$fieldNum];
    }


    /**
     * Returns true if any documents have been deleted from this index segment.
     *
     * @return boolean
     */
    public function hasDeletions()
    {
        return $this->_deleted !== null;
    }


    /**
     * Returns true if segment has single norms file.
     *
     * @return boolean
     */
    public function hasSingleNormFile()
    {
        return $this->_hasSingleNormFile ? true : false;
    }

    /**
     * Returns true if segment is stored using compound segment file.
     *
     * @return boolean
     */
    public function isCompound()
    {
        return $this->_isCompound;
    }

    /**
     * Deletes a document from the index segment.
     * $id is an internal document id
     *
     * @param integer
     */
    public function delete($id)
    {
        $this->_deletedDirty = true;

        if (extension_loaded('bitset')) {
            if ($this->_deleted === null) {
                $this->_deleted = bitset_empty($id);
            }
            bitset_incl($this->_deleted, $id);
        } else {
            if ($this->_deleted === null) {
                $this->_deleted = array();
            }

            $this->_deleted[$id] = 1;
        }
    }

    /**
     * Checks, that document is deleted
     *
     * @param integer
     * @return boolean
     */
    public function isDeleted($id)
    {
        if ($this->_deleted === null) {
            return false;
        }

        if (extension_loaded('bitset')) {
            return bitset_in($this->_deleted, $id);
        } else {
            return isset($this->_deleted[$id]);
        }
    }

    /**
     * Detect latest delete generation
     *
     * Is actualy used from writeChanges() method or from the constructor if it's invoked from
     * Index writer. In both cases index write lock is already obtained, so we shouldn't care
     * about it
     *
     * @return integer
     */
    private function _detectLatestDelGen()
    {
        $delFileList = array();
        foreach ($this->_directory->fileList() as $file) {
            if ($file == $this->_name . '.del') {
                // Matches <segment_name>.del file name
                $delFileList[] = 0;
            } else if (preg_match('/^' . $this->_name . '_([a-zA-Z0-9]+)\.del$/i', $file, $matches)) {
                // Matches <segment_name>_NNN.del file names
                $delFileList[] = (int)base_convert($matches[1], 36, 10);
            }
        }

        if (count($delFileList) == 0) {
            // There is no deletions file for current segment in the directory
            // Set deletions file generation number to 1
            return -1;
        } else {
            // There are some deletions files for current segment in the directory
            // Set deletions file generation number to the highest nuber
            return max($delFileList);
        }
    }

    /**
     * Write changes if it's necessary.
     *
     * This method must be invoked only from the Writer _updateSegments() method,
     * so index Write lock has to be already obtained.
     *
     * @internal
     * @throws Zend_Search_Lucene_Exceptions
     */
    public function writeChanges()
    {
        // Get new generation number
        $latestDelGen = $this->_detectLatestDelGen();

        if (!$this->_deletedDirty) {
            // There was no deletions by current process

            if ($latestDelGen == $this->_delGen) {
                // Delete file hasn't been updated by any concurrent process
                return;
            } else if ($latestDelGen > $this->_delGen) {
                // Delete file has been updated by some concurrent process
                // Reload deletions file
                $this->_delGen  = $latestDelGen;
                $this->_deleted = $this->_loadDelFile();

                return;
            } else {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Delete file processing workflow is corrupted for the segment \'' . $this->_name . '\'.');
            }
        }

        if ($latestDelGen > $this->_delGen) {
            // Merge current deletions with latest deletions file
            $this->_delGen = $latestDelGen;

            $latestDelete = $this->_loadDelFile();

            if (extension_loaded('bitset')) {
                $this->_deleted = bitset_union($this->_deleted, $latestDelete);
            } else {
                $this->_deleted += $latestDelete;
            }
        }

        if (extension_loaded('bitset')) {
            $delBytes = $this->_deleted;
            $bitCount = count(bitset_to_array($delBytes));
        } else {
            $byteCount = floor($this->_docCount/8)+1;
            $delBytes = str_repeat(chr(0), $byteCount);
            for ($count = 0; $count < $byteCount; $count++) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    if (isset($this->_deleted[$count*8 + $bit])) {
                        $byte |= (1<<$bit);
                    }
                }
                $delBytes[$count] = chr($byte);
            }
            $bitCount = count($this->_deleted);
        }

        if ($this->_delGen == -1) {
            // Set delete file generation number to 1
            $this->_delGen = 1;
        } else {
            // Increase delete file generation number by 1
            $this->_delGen++;
        }

        $delFile = $this->_directory->createFile($this->_name . '_' . base_convert($this->_delGen, 10, 36) . '.del');
        $delFile->writeInt($this->_docCount);
        $delFile->writeInt($bitCount);
        $delFile->writeBytes($delBytes);

        $this->_deletedDirty = false;
    }


    /**
     * Term Dictionary File object for stream like terms reading
     *
     * @var Zend_Search_Lucene_Storage_File
     */
    private $_tisFile = null;

    /**
     * Actual offset of the .tis file data
     *
     * @var integer
     */
    private $_tisFileOffset;

    /**
     * Frequencies File object for stream like terms reading
     *
     * @var Zend_Search_Lucene_Storage_File
     */
    private $_frqFile = null;

    /**
     * Actual offset of the .frq file data
     *
     * @var integer
     */
    private $_frqFileOffset;

    /**
     * Positions File object for stream like terms reading
     *
     * @var Zend_Search_Lucene_Storage_File
     */
    private $_prxFile = null;

    /**
     * Actual offset of the .prx file in the compound file
     *
     * @var integer
     */
    private $_prxFileOffset;


    /**
     * Actual number of terms in term stream
     *
     * @var integer
     */
    private $_termCount = 0;

    /**
     * Overall number of terms in term stream
     *
     * @var integer
     */
    private $_termNum = 0;

    /**
     * Segment index interval
     *
     * @var integer
     */
    private $_indexInterval;

    /**
     * Segment skip interval
     *
     * @var integer
     */
    private $_skipInterval;

    /**
     * Last TermInfo in a terms stream
     *
     * @var Zend_Search_Lucene_Index_TermInfo
     */
    private $_lastTermInfo = null;

    /**
     * Last Term in a terms stream
     *
     * @var Zend_Search_Lucene_Index_Term
     */
    private $_lastTerm = null;

    /**
     * Map of the document IDs
     * Used to get new docID after removing deleted documents.
     * It's not very effective from memory usage point of view,
     * but much more faster, then other methods
     *
     * @var array|null
     */
    private $_docMap = null;

    /**
     * An array of all term positions in the documents.
     * Array structure: array( docId => array( pos1, pos2, ...), ...)
     *
     * Is set to null if term positions loading has to be skipped
     *
     * @var array|null
     */
    private $_lastTermPositions;


    /**
     * Terms scan mode
     *
     * Values:
     *
     * self::SM_TERMS_ONLY - terms are scanned, no additional info is retrieved
     * self::SM_FULL_INFO  - terms are scanned, frequency and position info is retrieved
     * self::SM_MERGE_INFO - terms are scanned, frequency and position info is retrieved
     *                       document numbers are compacted (shifted if segment has deleted documents)
     *
     * @var integer
     */
    private $_termsScanMode;

    /** Scan modes */
    const SM_TERMS_ONLY = 0;    // terms are scanned, no additional info is retrieved
    const SM_FULL_INFO  = 1;    // terms are scanned, frequency and position info is retrieved
    const SM_MERGE_INFO = 2;    // terms are scanned, frequency and position info is retrieved
                                // document numbers are compacted (shifted if segment contains deleted documents)

    /**
     * Reset terms stream
     *
     * $startId - id for the fist document
     * $compact - remove deleted documents
     *
     * Returns start document id for the next segment
     *
     * @param integer $startId
     * @param integer $mode
     * @throws Zend_Search_Lucene_Exception
     * @return integer
     */
    public function resetTermsStream(/** $startId = 0, $mode = self::SM_TERMS_ONLY */)
    {
        /**
         * SegmentInfo->resetTermsStream() method actually takes two optional parameters:
         *   $startId (default value is 0)
         *   $mode (default value is self::SM_TERMS_ONLY)
         */
        $argList = func_get_args();
        if (count($argList) > 2) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Wrong number of arguments');
        } else if (count($argList) == 2) {
            $startId = $argList[0];
            $mode    = $argList[1];
        } else if (count($argList) == 1) {
            $startId = $argList[0];
            $mode    = self::SM_TERMS_ONLY;
        } else {
            $startId = 0;
            $mode    = self::SM_TERMS_ONLY;
        }

        if ($this->_tisFile !== null) {
            $this->_tisFile = null;
        }

        $this->_tisFile = $this->openCompoundFile('.tis', false);
        $this->_tisFileOffset = $this->_tisFile->tell();

        $tiVersion = $this->_tisFile->readInt();
        if ($tiVersion != (int)0xFFFFFFFE /* pre-2.1 format */  &&
            $tiVersion != (int)0xFFFFFFFD /* 2.1+ format    */) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Wrong TermInfoFile file format');
        }

        $this->_termCount     =
              $this->_termNum = $this->_tisFile->readLong(); // Read terms count
        $this->_indexInterval = $this->_tisFile->readInt();  // Read Index interval
        $this->_skipInterval  = $this->_tisFile->readInt();  // Read skip interval
        if ($tiVersion == (int)0xFFFFFFFD /* 2.1+ format */) {
            $maxSkipLevels = $this->_tisFile->readInt();
        }

        if ($this->_frqFile !== null) {
            $this->_frqFile = null;
        }
        if ($this->_prxFile !== null) {
            $this->_prxFile = null;
        }
        $this->_docMap = array();

        $this->_lastTerm          = new Zend_Search_Lucene_Index_Term('', -1);
        $this->_lastTermInfo      = new Zend_Search_Lucene_Index_TermInfo(0, 0, 0, 0);
        $this->_lastTermPositions = null;

        $this->_termsScanMode = $mode;

        switch ($mode) {
            case self::SM_TERMS_ONLY:
                // Do nothing
                break;

            case self::SM_FULL_INFO:
                // break intentionally omitted
            case self::SM_MERGE_INFO:
                $this->_frqFile = $this->openCompoundFile('.frq', false);
                $this->_frqFileOffset = $this->_frqFile->tell();

                $this->_prxFile = $this->openCompoundFile('.prx', false);
                $this->_prxFileOffset = $this->_prxFile->tell();

                for ($count = 0; $count < $this->_docCount; $count++) {
                    if (!$this->isDeleted($count)) {
                        $this->_docMap[$count] = $startId + (($mode == self::SM_MERGE_INFO) ? count($this->_docMap) : $count);
                    }
                }
                break;

            default:
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Wrong terms scaning mode specified.');
                break;
        }

        // Calculate next segment start id (since $this->_docMap structure may be cleaned by $this->nextTerm() call)
        $nextSegmentStartId = $startId + (($mode == self::SM_MERGE_INFO) ? count($this->_docMap) : $this->_docCount);
        $this->nextTerm();

        return $nextSegmentStartId;
    }


    /**
     * Skip terms stream up to the specified term preffix.
     *
     * Prefix contains fully specified field info and portion of searched term
     *
     * @param Zend_Search_Lucene_Index_Term $prefix
     * @throws Zend_Search_Lucene_Exception
     */
    public function skipTo(Zend_Search_Lucene_Index_Term $prefix)
    {
        if ($this->_termDictionary === null) {
            $this->_loadDictionaryIndex();
        }

        $searchField = $this->getFieldNum($prefix->field);

        if ($searchField == -1) {
            /**
             * Field is not presented in this segment
             * Go to the end of dictionary
             */
            $this->_tisFile = null;
            $this->_frqFile = null;
            $this->_prxFile = null;

            $this->_lastTerm          = null;
            $this->_lastTermInfo      = null;
            $this->_lastTermPositions = null;

            return;
        }
        $searchDicField = $this->_getFieldPosition($searchField);

        // search for appropriate value in dictionary
        $lowIndex = 0;
        $highIndex = count($this->_termDictionary)-1;
        while ($highIndex >= $lowIndex) {
            // $mid = ($highIndex - $lowIndex)/2;
            $mid = ($highIndex + $lowIndex) >> 1;
            $midTerm = $this->_termDictionary[$mid];

            $fieldNum = $this->_getFieldPosition($midTerm[0] /* field */);
            $delta = $searchDicField - $fieldNum;
            if ($delta == 0) {
                $delta = strcmp($prefix->text, $midTerm[1] /* text */);
            }

            if ($delta < 0) {
                $highIndex = $mid-1;
            } elseif ($delta > 0) {
                $lowIndex  = $mid+1;
            } else {
                // We have reached term we are looking for
                break;
            }
        }

        if ($highIndex == -1) {
            // Term is out of the dictionary range
            $this->_tisFile = null;
            $this->_frqFile = null;
            $this->_prxFile = null;

            $this->_lastTerm          = null;
            $this->_lastTermInfo      = null;
            $this->_lastTermPositions = null;

            return;
        }

        $prevPosition = $highIndex;
        $prevTerm = $this->_termDictionary[$prevPosition];
        $prevTermInfo = $this->_termDictionaryInfos[$prevPosition];

        if ($this->_tisFile === null) {
            // The end of terms stream is reached and terms dictionary file is closed
            // Perform mini-reset operation
            $this->_tisFile = $this->openCompoundFile('.tis', false);

            if ($this->_termsScanMode == self::SM_FULL_INFO  ||  $this->_termsScanMode == self::SM_MERGE_INFO) {
                $this->_frqFile = $this->openCompoundFile('.frq', false);
                $this->_prxFile = $this->openCompoundFile('.prx', false);
            }
        }
        $this->_tisFile->seek($this->_tisFileOffset + $prevTermInfo[4], SEEK_SET);

        $this->_lastTerm     = new Zend_Search_Lucene_Index_Term($prevTerm[1] /* text */,
                                                                 ($prevTerm[0] == -1) ? '' : $this->_fields[$prevTerm[0] /* field */]->name);
        $this->_lastTermInfo = new Zend_Search_Lucene_Index_TermInfo($prevTermInfo[0] /* docFreq */,
                                                                     $prevTermInfo[1] /* freqPointer */,
                                                                     $prevTermInfo[2] /* proxPointer */,
                                                                     $prevTermInfo[3] /* skipOffset */);
        $this->_termCount  =  $this->_termNum - $prevPosition*$this->_indexInterval;

        if ($highIndex == 0) {
            // skip start entry
            $this->nextTerm();
        } else if ($prefix->field == $this->_lastTerm->field  &&  $prefix->text  == $this->_lastTerm->text) {
            // We got exact match in the dictionary index

            if ($this->_termsScanMode == self::SM_FULL_INFO  ||  $this->_termsScanMode == self::SM_MERGE_INFO) {
                $this->_lastTermPositions = array();

                $this->_frqFile->seek($this->_lastTermInfo->freqPointer + $this->_frqFileOffset, SEEK_SET);
                $freqs = array();   $docId = 0;
                for( $count = 0; $count < $this->_lastTermInfo->docFreq; $count++ ) {
                    $docDelta = $this->_frqFile->readVInt();
                    if( $docDelta % 2 == 1 ) {
                        $docId += ($docDelta-1)/2;
                        $freqs[ $docId ] = 1;
                    } else {
                        $docId += $docDelta/2;
                        $freqs[ $docId ] = $this->_frqFile->readVInt();
                    }
                }

                $this->_prxFile->seek($this->_lastTermInfo->proxPointer + $this->_prxFileOffset, SEEK_SET);
                foreach ($freqs as $docId => $freq) {
                    $termPosition = 0;  $positions = array();

                    for ($count = 0; $count < $freq; $count++ ) {
                        $termPosition += $this->_prxFile->readVInt();
                        $positions[] = $termPosition;
                    }

                    if (isset($this->_docMap[$docId])) {
                        $this->_lastTermPositions[$this->_docMap[$docId]] = $positions;
                    }
                }
            }

            return;
        }

        // Search term matching specified prefix
        while ($this->_lastTerm !== null) {
            if ( strcmp($this->_lastTerm->field, $prefix->field) > 0  ||
                 ($prefix->field == $this->_lastTerm->field  &&  strcmp($this->_lastTerm->text, $prefix->text) >= 0) ) {
                    // Current term matches or greate than the pattern
                    return;
            }

            $this->nextTerm();
        }
    }


    /**
     * Scans terms dictionary and returns next term
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function nextTerm()
    {
        if ($this->_tisFile === null  ||  $this->_termCount == 0) {
            $this->_lastTerm          = null;
            $this->_lastTermInfo      = null;
            $this->_lastTermPositions = null;
            $this->_docMap            = null;

            // may be necessary for "empty" segment
            $this->_tisFile = null;
            $this->_frqFile = null;
            $this->_prxFile = null;

            return null;
        }

        $termPrefixLength = $this->_tisFile->readVInt();
        $termSuffix       = $this->_tisFile->readString();
        $termFieldNum     = $this->_tisFile->readVInt();
        $termValue        = Zend_Search_Lucene_Index_Term::getPrefix($this->_lastTerm->text, $termPrefixLength) . $termSuffix;

        $this->_lastTerm = new Zend_Search_Lucene_Index_Term($termValue, $this->_fields[$termFieldNum]->name);

        $docFreq     = $this->_tisFile->readVInt();
        $freqPointer = $this->_lastTermInfo->freqPointer + $this->_tisFile->readVInt();
        $proxPointer = $this->_lastTermInfo->proxPointer + $this->_tisFile->readVInt();
        if ($docFreq >= $this->_skipInterval) {
            $skipOffset = $this->_tisFile->readVInt();
        } else {
            $skipOffset = 0;
        }

        $this->_lastTermInfo = new Zend_Search_Lucene_Index_TermInfo($docFreq, $freqPointer, $proxPointer, $skipOffset);


        if ($this->_termsScanMode == self::SM_FULL_INFO  ||  $this->_termsScanMode == self::SM_MERGE_INFO) {
            $this->_lastTermPositions = array();

            $this->_frqFile->seek($this->_lastTermInfo->freqPointer + $this->_frqFileOffset, SEEK_SET);
            $freqs = array();   $docId = 0;
            for( $count = 0; $count < $this->_lastTermInfo->docFreq; $count++ ) {
                $docDelta = $this->_frqFile->readVInt();
                if( $docDelta % 2 == 1 ) {
                    $docId += ($docDelta-1)/2;
                    $freqs[ $docId ] = 1;
                } else {
                    $docId += $docDelta/2;
                    $freqs[ $docId ] = $this->_frqFile->readVInt();
                }
            }

            $this->_prxFile->seek($this->_lastTermInfo->proxPointer + $this->_prxFileOffset, SEEK_SET);
            foreach ($freqs as $docId => $freq) {
                $termPosition = 0;  $positions = array();

                for ($count = 0; $count < $freq; $count++ ) {
                    $termPosition += $this->_prxFile->readVInt();
                    $positions[] = $termPosition;
                }

                if (isset($this->_docMap[$docId])) {
                    $this->_lastTermPositions[$this->_docMap[$docId]] = $positions;
                }
            }
        }

        $this->_termCount--;
        if ($this->_termCount == 0) {
            $this->_tisFile = null;
            $this->_frqFile = null;
            $this->_prxFile = null;
        }

        return $this->_lastTerm;
    }

    /**
     * Close terms stream
     *
     * Should be used for resources clean up if stream is not read up to the end
     */
    public function closeTermsStream()
    {
        $this->_tisFile = null;
        $this->_frqFile = null;
        $this->_prxFile = null;

        $this->_lastTerm          = null;
        $this->_lastTermInfo      = null;
        $this->_lastTermPositions = null;

        $this->_docMap            = null;
    }


    /**
     * Returns term in current position
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function currentTerm()
    {
        return $this->_lastTerm;
    }


    /**
     * Returns an array of all term positions in the documents.
     * Return array structure: array( docId => array( pos1, pos2, ...), ...)
     *
     * @return array
     */
    public function currentTermPositions()
    {
        return $this->_lastTermPositions;
    }
}

