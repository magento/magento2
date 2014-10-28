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
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SegmentToDelta.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Pdf_Cmap */
#require_once 'Zend/Pdf/Cmap.php';


/**
 * Implements the "segment mapping to delta values" character map (type 4).
 *
 * This is the Microsoft standard mapping table type for OpenType fonts. It
 * provides the ability to cover multiple contiguous ranges of the Unicode
 * character set, with the exception of Unicode Surrogates (U+D800 - U+DFFF).
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Cmap_SegmentToDelta extends Zend_Pdf_Cmap
{
  /**** Instance Variables ****/


    /**
     * The number of segments in the table.
     * @var integer
     */
    protected $_segmentCount = 0;

    /**
     * The size of the binary search range for segments.
     * @var integer
     */
    protected $_searchRange = 0;

    /**
     * The number of binary search steps required to cover the entire search
     * range.
     * @var integer
     */
    protected $_searchIterations = 0;

    /**
     * Array of ending character codes for each segment.
     * @var array
     */
    protected $_segmentTableEndCodes = array();

    /**
     * The ending character code for the segment at the end of the low search
     * range.
     * @var integer
     */
    protected $_searchRangeEndCode = 0;

    /**
     * Array of starting character codes for each segment.
     * @var array
     */
    protected $_segmentTableStartCodes = array();

    /**
     * Array of character code to glyph delta values for each segment.
     * @var array
     */
    protected $_segmentTableIdDeltas = array();

    /**
     * Array of offsets into the glyph index array for each segment.
     * @var array
     */
    protected $_segmentTableIdRangeOffsets = array();

    /**
     * Glyph index array. Stores glyph numbers, used with range offset.
     * @var array
     */
    protected $_glyphIndexArray = array();



  /**** Public Interface ****/


  /* Concrete Class Implementation */

    /**
     * Returns an array of glyph numbers corresponding to the Unicode characters.
     *
     * If a particular character doesn't exist in this font, the special 'missing
     * character glyph' will be substituted.
     *
     * See also {@link glyphNumberForCharacter()}.
     *
     * @param array $characterCodes Array of Unicode character codes (code points).
     * @return array Array of glyph numbers.
     */
    public function glyphNumbersForCharacters($characterCodes)
    {
        $glyphNumbers = array();
        foreach ($characterCodes as $key => $characterCode) {

            /* These tables only cover the 16-bit character range.
             */
            if ($characterCode > 0xffff) {
                $glyphNumbers[$key] = Zend_Pdf_Cmap::MISSING_CHARACTER_GLYPH;
                continue;
            }

            /* Determine where to start the binary search. The segments are
             * ordered from lowest-to-highest. We are looking for the first
             * segment whose end code is greater than or equal to our character
             * code.
             *
             * If the end code at the top of the search range is larger, then
             * our target is probably below it.
             *
             * If it is smaller, our target is probably above it, so move the
             * search range to the end of the segment list.
             */
            if ($this->_searchRangeEndCode >= $characterCode) {
                $searchIndex = $this->_searchRange;
            } else {
                $searchIndex = $this->_segmentCount;
            }

            /* Now do a binary search to find the first segment whose end code
             * is greater or equal to our character code. No matter the number
             * of segments (there may be hundreds in a large font), we will only
             * need to perform $this->_searchIterations.
             */
            for ($i = 1; $i <= $this->_searchIterations; $i++) {
                if ($this->_segmentTableEndCodes[$searchIndex] >= $characterCode) {
                    $subtableIndex = $searchIndex;
                    $searchIndex -= $this->_searchRange >> $i;
                } else {
                    $searchIndex += $this->_searchRange >> $i;
                }
            }

            /* If the segment's start code is greater than our character code,
             * that character is not represented in this font. Move on.
             */
            if ($this->_segmentTableStartCodes[$subtableIndex] > $characterCode) {
                $glyphNumbers[$key] = Zend_Pdf_Cmap::MISSING_CHARACTER_GLYPH;
                continue;
            }

            if ($this->_segmentTableIdRangeOffsets[$subtableIndex] == 0) {
                /* This segment uses a simple mapping from character code to
                 * glyph number.
                 */
                $glyphNumbers[$key] = ($characterCode + $this->_segmentTableIdDeltas[$subtableIndex]) % 65536;

            } else {
                /* This segment relies on the glyph index array to determine the
                 * glyph number. The calculation below determines the correct
                 * index into that array. It's a little odd because the range
                 * offset in the font file is designed to quickly provide an
                 * address of the index in the raw binary data instead of the
                 * index itself. Since we've parsed the data into arrays, we
                 * must process it a bit differently.
                 */
                $glyphIndex = ($characterCode - $this->_segmentTableStartCodes[$subtableIndex] +
                               $this->_segmentTableIdRangeOffsets[$subtableIndex] - $this->_segmentCount +
                               $subtableIndex - 1);
                $glyphNumbers[$key] = $this->_glyphIndexArray[$glyphIndex];

            }

        }
        return $glyphNumbers;
    }

    /**
     * Returns the glyph number corresponding to the Unicode character.
     *
     * If a particular character doesn't exist in this font, the special 'missing
     * character glyph' will be substituted.
     *
     * See also {@link glyphNumbersForCharacters()} which is optimized for bulk
     * operations.
     *
     * @param integer $characterCode Unicode character code (code point).
     * @return integer Glyph number.
     */
    public function glyphNumberForCharacter($characterCode)
    {
        /* This code is pretty much a copy of glyphNumbersForCharacters().
         * See that method for inline documentation.
         */

        if ($characterCode > 0xffff) {
            return Zend_Pdf_Cmap::MISSING_CHARACTER_GLYPH;
        }

        if ($this->_searchRangeEndCode >= $characterCode) {
            $searchIndex = $this->_searchRange;
        } else {
            $searchIndex = $this->_segmentCount;
        }

        for ($i = 1; $i <= $this->_searchIterations; $i++) {
            if ($this->_segmentTableEndCodes[$searchIndex] >= $characterCode) {
                $subtableIndex = $searchIndex;
                $searchIndex -= $this->_searchRange >> $i;
            } else {
                $searchIndex += $this->_searchRange >> $i;
            }
        }

        if ($this->_segmentTableStartCodes[$subtableIndex] > $characterCode) {
            return Zend_Pdf_Cmap::MISSING_CHARACTER_GLYPH;
        }

        if ($this->_segmentTableIdRangeOffsets[$subtableIndex] == 0) {
            $glyphNumber = ($characterCode + $this->_segmentTableIdDeltas[$subtableIndex]) % 65536;
        } else {
            $glyphIndex = ($characterCode - $this->_segmentTableStartCodes[$subtableIndex] +
                           $this->_segmentTableIdRangeOffsets[$subtableIndex] - $this->_segmentCount +
                           $subtableIndex - 1);
            $glyphNumber = $this->_glyphIndexArray[$glyphIndex];
        }
        return $glyphNumber;
    }

    /**
     * Returns an array containing the Unicode characters that have entries in
     * this character map.
     *
     * @return array Unicode character codes.
     */
    public function getCoveredCharacters()
    {
        $characterCodes = array();
        for ($i = 1; $i <= $this->_segmentCount; $i++) {
            for ($code = $this->_segmentTableStartCodes[$i]; $code <= $this->_segmentTableEndCodes[$i]; $code++) {
                $characterCodes[] = $code;
            }
        }
        return $characterCodes;
    }


    /**
     * Returns an array containing the glyphs numbers that have entries in this character map.
     * Keys are Unicode character codes (integers)
     *
     * This functionality is partially covered by glyphNumbersForCharacters(getCoveredCharacters())
     * call, but this method do it in more effective way (prepare complete list instead of searching
     * glyph for each character code).
     *
     * @internal
     * @return array Array representing <Unicode character code> => <glyph number> pairs.
     */
    public function getCoveredCharactersGlyphs()
    {
        $glyphNumbers = array();

        for ($segmentNum = 1; $segmentNum <= $this->_segmentCount; $segmentNum++) {
            if ($this->_segmentTableIdRangeOffsets[$segmentNum] == 0) {
                $delta = $this->_segmentTableIdDeltas[$segmentNum];

                for ($code =  $this->_segmentTableStartCodes[$segmentNum];
                     $code <= $this->_segmentTableEndCodes[$segmentNum];
                     $code++) {
                    $glyphNumbers[$code] = ($code + $delta) % 65536;
                }
            } else {
                $code       = $this->_segmentTableStartCodes[$segmentNum];
                $glyphIndex = $this->_segmentTableIdRangeOffsets[$segmentNum] - ($this->_segmentCount - $segmentNum) - 1;

                while ($code <= $this->_segmentTableEndCodes[$segmentNum]) {
                    $glyphNumbers[$code] = $this->_glyphIndexArray[$glyphIndex];

                    $code++;
                    $glyphIndex++;
                }
            }
        }

        return $glyphNumbers;
    }



  /* Object Lifecycle */

    /**
     * Object constructor
     *
     * Parses the raw binary table data. Throws an exception if the table is
     * malformed.
     *
     * @param string $cmapData Raw binary cmap table data.
     * @throws Zend_Pdf_Exception
     */
    public function __construct($cmapData)
    {
        /* Sanity check: The table should be at least 23 bytes in size.
         */
        $actualLength = strlen($cmapData);
        if ($actualLength < 23) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Insufficient table data',
                                         Zend_Pdf_Exception::CMAP_TABLE_DATA_TOO_SMALL);
        }

        /* Sanity check: Make sure this is right data for this table type.
         */
        $type = $this->_extractUInt2($cmapData, 0);
        if ($type != Zend_Pdf_Cmap::TYPE_SEGMENT_TO_DELTA) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Wrong cmap table type',
                                         Zend_Pdf_Exception::CMAP_WRONG_TABLE_TYPE);
        }

        $length = $this->_extractUInt2($cmapData, 2);
        if ($length != $actualLength) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Table length ($length) does not match actual length ($actualLength)",
                                         Zend_Pdf_Exception::CMAP_WRONG_TABLE_LENGTH);
        }

        /* Mapping tables should be language-independent. The font may not work
         * as expected if they are not. Unfortunately, many font files in the
         * wild incorrectly record a language ID in this field, so we can't
         * call this a failure.
         */
        $language = $this->_extractUInt2($cmapData, 4);
        if ($language != 0) {
            // Record a warning here somehow?
        }

        /* These two values are stored premultiplied by two which is convienent
         * when using the binary data directly, but we're parsing it out to
         * native PHP data types, so divide by two.
         */
        $this->_segmentCount = $this->_extractUInt2($cmapData, 6) >> 1;
        $this->_searchRange  = $this->_extractUInt2($cmapData, 8) >> 1;

        $this->_searchIterations = $this->_extractUInt2($cmapData, 10) + 1;

        $offset = 14;
        for ($i = 1; $i <= $this->_segmentCount; $i++, $offset += 2) {
            $this->_segmentTableEndCodes[$i] = $this->_extractUInt2($cmapData, $offset);
        }

        $this->_searchRangeEndCode = $this->_segmentTableEndCodes[$this->_searchRange];

        $offset += 2;    // reserved bytes

        for ($i = 1; $i <= $this->_segmentCount; $i++, $offset += 2) {
            $this->_segmentTableStartCodes[$i] = $this->_extractUInt2($cmapData, $offset);
        }

        for ($i = 1; $i <= $this->_segmentCount; $i++, $offset += 2) {
            $this->_segmentTableIdDeltas[$i] = $this->_extractInt2($cmapData, $offset);    // signed
        }

        /* The range offset helps determine the index into the glyph index array.
         * Like the segment count and search range above, it's stored as a byte
         * multiple in the font, so divide by two as we extract the values.
         */
        for ($i = 1; $i <= $this->_segmentCount; $i++, $offset += 2) {
            $this->_segmentTableIdRangeOffsets[$i] = $this->_extractUInt2($cmapData, $offset) >> 1;
        }

        /* The size of the glyph index array varies by font and depends on the
         * extent of the usage of range offsets versus deltas. Some fonts may
         * not have any entries in this array.
         */
        for (; $offset < $length; $offset += 2) {
            $this->_glyphIndexArray[] = $this->_extractUInt2($cmapData, $offset);
        }

        /* Sanity check: After reading all of the data, we should be at the end
         * of the table.
         */
        if ($offset != $length) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Ending offset ($offset) does not match length ($length)",
                                         Zend_Pdf_Exception::CMAP_FINAL_OFFSET_NOT_LENGTH);
        }
    }

}
