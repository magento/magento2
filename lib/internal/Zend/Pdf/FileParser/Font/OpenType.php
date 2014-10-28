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
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: OpenType.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Pdf_FileParser_Font */
#require_once 'Zend/Pdf/FileParser/Font.php';

/**
 * Abstract base class for OpenType font file parsers.
 *
 * TrueType was originally developed by Apple and was adopted as the default
 * font format for the Microsoft Windows platform. OpenType is an extension of
 * TrueType, developed jointly by Microsoft and Adobe, which adds support for
 * PostScript font data.
 *
 * This abstract parser class forms the foundation for concrete subclasses which
 * extract either TrueType or PostScript font data from the file.
 *
 * All OpenType files use big-endian byte ordering.
 *
 * The full TrueType and OpenType specifications can be found at:
 * <ul>
 *  <li>{@link http://developer.apple.com/textfonts/TTRefMan/}
 *  <li>{@link http://www.microsoft.com/typography/OTSPEC/}
 *  <li>{@link http://partners.adobe.com/public/developer/opentype/index_spec.html}
 * </ul>
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_FileParser_Font_OpenType extends Zend_Pdf_FileParser_Font
{
  /**** Instance Variables ****/


    /**
     * Stores the scaler type (font type) for the font file. See
     * {@link _readScalerType()}.
     * @var integer
     */
    protected $_scalerType = 0;

    /**
     * Stores the byte offsets to the various information tables.
     * @var array
     */
    protected $_tableDirectory = array();



  /**** Public Interface ****/


  /* Semi-Concrete Class Implementation */

    /**
     * Verifies that the font file is in the expected format.
     *
     * NOTE: This method should be overridden in subclasses to check the
     * specific format and set $this->_isScreened!
     *
     * @throws Zend_Pdf_Exception
     */
    public function screen()
    {
        if ($this->_isScreened) {
            return;
        }
        $this->_readScalerType();
    }

    /**
     * Reads and parses the font data from the file on disk.
     *
     * NOTE: This method should be overridden in subclasses to add type-
     * specific parsing and set $this->isParsed.
     *
     * @throws Zend_Pdf_Exception
     */
    public function parse()
    {
        if ($this->_isParsed) {
            return;
        }

        /* Screen the font file first, if it hasn't been done yet.
        */
        $this->screen();

        /* Start by reading the table directory.
         */
        $this->_parseTableDirectory();

        /* Then parse all of the required tables.
         */
        $this->_parseHeadTable();
        $this->_parseNameTable();
        $this->_parsePostTable();
        $this->_parseHheaTable();
        $this->_parseMaxpTable();
        $this->_parseOs2Table();
        $this->_parseHmtxTable();
        $this->_parseCmapTable();

        /* If present, parse the optional tables.
         */
        /**
         * @todo Add parser for kerning pairs.
         * @todo Add parser for ligatures.
         * @todo Add parser for other useful hinting tables.
         */
    }



  /**** Internal Methods ****/


  /* Parser Methods */

    /**
     * Parses the OpenType table directory.
     *
     * The table directory contains the identifier, checksum, byte offset, and
     * length of each of the information tables housed in the font file.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseTableDirectory()
    {
        $this->moveToOffset(4);

        $tableCount = $this->readUInt(2);
        $this->_debugLog('%d tables', $tableCount);

        /* Sanity check, in case we're not actually reading a OpenType file and
         * the first four bytes coincidentally matched an OpenType signature in
         * screen() above.
         *
         * There are at minimum 7 required tables: cmap, head, hhea, hmtx, maxp,
         * name, and post. In the current OpenType standard, only 32 table types
         * are defined, so use 50 as a practical limit.
         */
        if (($tableCount < 7) || ($tableCount > 50)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Table count not within expected range',
                                         Zend_Pdf_Exception::BAD_TABLE_COUNT);
        }

        /* Skip the next 6 bytes, which contain values to aid a binary search.
         */
        $this->skipBytes(6);

        /* The directory contains four values: the name of the table, checksum,
         * offset to the table from the beginning of the font, and actual data
         * length of the table.
         */
        for ($tableIndex = 0; $tableIndex < $tableCount; $tableIndex++) {
            $tableName = $this->readBytes(4);

            /* We ignore the checksum here for two reasons: First, the PDF viewer
             * will do this later anyway; Second, calculating the checksum would
             * require unsigned integers, which PHP does not currently provide.
             * We may revisit this in the future.
             */
            $this->skipBytes(4);

            $tableOffset = $this->readUInt(4);
            $tableLength = $this->readUInt(4);
            $this->_debugLog('%s offset: 0x%x; length: %d', $tableName, $tableOffset, $tableLength);

            /* Sanity checks for offset and length values.
             */
            $fileSize = $this->_dataSource->getSize();
            if (($tableOffset < 0) || ($tableOffset > $fileSize)) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception("Table offset ($tableOffset) not within expected range",
                                             Zend_Pdf_Exception::INDEX_OUT_OF_RANGE);
            }
            if (($tableLength < 0) || (($tableOffset + $tableLength) > $fileSize)) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception("Table length ($tableLength) not within expected range",
                                             Zend_Pdf_Exception::INDEX_OUT_OF_RANGE);
            }

            $this->_tableDirectory[$tableName]['offset'] = $tableOffset;
            $this->_tableDirectory[$tableName]['length'] = $tableLength;
        }
    }


    /**
     * Parses the OpenType head (Font Header) table.
     *
     * The head table contains global information about the font such as the
     * revision number and global metrics.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseHeadTable()
    {
        $this->_jumpToTable('head');

        /* We can read any version 1 table.
         */
        $tableVersion = $this->_readTableVersion(1, 1);

        /* Skip the font revision number and checksum adjustment.
         */
        $this->skipBytes(8);

        $magicNumber = $this->readUInt(4);
        if ($magicNumber != 0x5f0f3cf5) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Wrong magic number. Expected: 0x5f0f3cf5; actual: '
                                       . sprintf('%x', $magicNumber),
                                         Zend_Pdf_Exception::BAD_MAGIC_NUMBER);
        }

        /* Most of the flags we ignore, but there are a few values that are
         * useful for our layout routines.
         */
        $flags = $this->readUInt(2);
        $this->baselineAtZero    = $this->isBitSet(0, $flags);
        $this->useIntegerScaling = $this->isBitSet(3, $flags);

        $this->unitsPerEm = $this->readUInt(2);
        $this->_debugLog('Units per em: %d', $this->unitsPerEm);

        /* Skip creation and modification date/time.
         */
        $this->skipBytes(16);

        $this->xMin = $this->readInt(2);
        $this->yMin = $this->readInt(2);
        $this->xMax = $this->readInt(2);
        $this->yMax = $this->readInt(2);
        $this->_debugLog('Font bounding box: %d %d %d %d',
                         $this->xMin, $this->yMin, $this->xMax, $this->yMax);

        /* The style bits here must match the fsSelection bits in the OS/2
         * table, if present.
         */
        $macStyleBits = $this->readUInt(2);
        $this->isBold   = $this->isBitSet(0, $macStyleBits);
        $this->isItalic = $this->isBitSet(1, $macStyleBits);

        /* We don't need the remainder of data in this table: smallest readable
         * size, font direction hint, indexToLocFormat, and glyphDataFormat.
         */
    }


    /**
     * Parses the OpenType name (Naming) table.
     *
     * The name table contains all of the identifying strings associated with
     * the font such as its name, copyright, trademark, license, etc.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseNameTable()
    {
        $this->_jumpToTable('name');
        $baseOffset = $this->_tableDirectory['name']['offset'];

        /* The name table begins with a short header, followed by each of the
         * fixed-length name records, followed by the variable-length strings.
         */

        /* We only understand version 0 tables.
         */
        $tableFormat = $this->readUInt(2);
        if ($tableFormat != 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Unable to read format $tableFormat table",
                                         Zend_Pdf_Exception::DONT_UNDERSTAND_TABLE_VERSION);
        }
        $this->_debugLog('Format %d table', $tableFormat);

        $nameCount = $this->readUInt(2);
        $this->_debugLog('%d name strings', $nameCount);

        $storageOffset = $this->readUInt(2) + $baseOffset;
        $this->_debugLog('Storage offset: 0x%x', $storageOffset);

        /* Scan the name records for those we're interested in. We'll skip over
         * encodings and languages we don't understand or support. Prefer the
         * Microsoft Unicode encoding for a given name/language combination, but
         * use Mac Roman if nothing else is available. We will extract the
         * actual strings later.
         */
        $nameRecords = array();
        for ($nameIndex = 0; $nameIndex < $nameCount; $nameIndex++) {

            $platformID = $this->readUInt(2);
            $encodingID = $this->readUInt(2);

            if (! ( (($platformID == 3) && ($encodingID == 1)) ||    // Microsoft Unicode
                    (($platformID == 1) && ($encodingID == 0))       // Mac Roman
                   ) ) {
                $this->skipBytes(8);    // Not a supported encoding. Move on.
                continue;
            }

            $languageID = $this->readUInt(2);
            $nameID     = $this->readUInt(2);
            $nameLength = $this->readUInt(2);
            $nameOffset = $this->readUInt(2);

            $languageCode = $this->_languageCodeForPlatform($platformID, $languageID);
            if ($languageCode === null) {
                $this->_debugLog('Skipping languageID: 0x%x; platformID %d', $languageID, $platformID);
                continue;    // Not a supported language. Move on.
            }

            $this->_debugLog('Adding nameID: %d; languageID: 0x%x; platformID: %d; offset: 0x%x (0x%x); length: %d',
                             $nameID, $languageID, $platformID, $baseOffset + $nameOffset, $nameOffset, $nameLength);

            /* Entries in the name table are sorted by platform ID. If an entry
             * exists for both Mac Roman and Microsoft Unicode, the Unicode entry
             * will prevail since it is processed last.
             */
            $nameRecords[$nameID][$languageCode] = array('platform' => $platformID,
                                                         'offset'   => $nameOffset,
                                                         'length'   => $nameLength );
        }

        /* Now go back and extract the interesting strings.
         */
        $fontNames = array();
        foreach ($nameRecords as $name => $languages) {
            foreach ($languages as $language => $attributes) {
                $stringOffset = $storageOffset + $attributes['offset'];
                $this->moveToOffset($stringOffset);
                if ($attributes['platform'] == 3) {
                    $string = $this->readStringUTF16($attributes['length']);
                } else {
                    $string = $this->readStringMacRoman($attributes['length']);
                }
                $fontNames[$name][$language] = $string;
            }
        }

        $this->names = $fontNames;
    }


    /**
     * Parses the OpenType post (PostScript Information) table.
     *
     * The post table contains additional information required for using the font
     * on PostScript printers. It also contains the preferred location and
     * thickness for an underline, which is used by our layout code.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parsePostTable()
    {
        $this->_jumpToTable('post');

        /* We can read versions 1-4 tables.
         */
        $tableVersion = $this->_readTableVersion(1, 4);

        $this->italicAngle = $this->readFixed(16, 16);

        $this->underlinePosition = $this->readInt(2);
        $this->underlineThickness = $this->readInt(2);

        $fixedPitch = $this->readUInt(4);
        $this->isMonospaced = ($fixedPitch !== 0);

        /* Skip over PostScript virtual memory usage.
         */
        $this->skipBytes(16);

        /* The format of the remainder of this table is dependent on the table
         * version. However, since it contains glyph ordering information and
         * PostScript names which we don't use, move on. (This may change at
         * some point in the future though...)
         */
    }


    /**
     * Parses the OpenType hhea (Horizontal Header) table.
     *
     * The hhea table contains information used for horizontal layout. It also
     * contains some vertical layout information for Apple systems. The vertical
     * layout information for the PDF file is usually taken from the OS/2 table.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseHheaTable()
    {
        $this->_jumpToTable('hhea');

        /* We can read any version 1 table.
         */
        $tableVersion = $this->_readTableVersion(1, 1);

        /* The typographic ascent, descent, and line gap values are Apple-
         * specific. Similar values exist in the OS/2 table. We'll use these
         * values unless better values are found in OS/2.
         */
        $this->ascent = $this->readInt(2);
        $this->descent = $this->readInt(2);
        $this->lineGap = $this->readInt(2);

        /* The descent value is supposed to be negative--it's the distance
         * relative to the baseline. However, some fonts improperly store a
         * positive value in this field. If a positive value is found, flip the
         * sign and record a warning in the debug log that we did this.
         */
        if ($this->descent > 0) {
            $this->_debugLog('Warning: Font should specify negative descent. Actual: %d; Using %d',
                             $this->descent, -$this->descent);
            $this->descent = -$this->descent;
        }

        /* Skip over advance width, left and right sidebearing, max x extent,
         * caret slope rise, run, and offset, and the four reserved fields.
         */
        $this->skipBytes(22);

        /* These values are needed to read the hmtx table.
         */
        $this->metricDataFormat = $this->readInt(2);
        $this->numberHMetrics = $this->readUInt(2);
        $this->_debugLog('hmtx data format: %d; number of metrics: %d',
                         $this->metricDataFormat, $this->numberHMetrics);
    }


    /**
     * Parses the OpenType hhea (Horizontal Header) table.
     *
     * The hhea table contains information used for horizontal layout. It also
     * contains some vertical layout information for Apple systems. The vertical
     * layout information for the PDF file is usually taken from the OS/2 table.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseMaxpTable()
    {
        $this->_jumpToTable('maxp');

        /* We don't care about table version.
         */
        $this->_readTableVersion(0, 1);

        /* The number of glyphs in the font.
         */
        $this->numGlyphs = $this->readUInt(2);
        $this->_debugLog('number of glyphs: %d', $this->numGlyphs);

        // Skip other maxp table entries (if presented with table version 1.0)...
    }


    /**
     * Parses the OpenType OS/2 (OS/2 and Windows Metrics) table.
     *
     * The OS/2 table contains additional metrics data that is required to use
     * the font on the OS/2 or Microsoft Windows platforms. It is not required
     * for Macintosh fonts, so may not always be present. When available, we use
     * this table to determine most of the vertical layout and stylistic
     * information and for the font.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseOs2Table()
    {
        if (! $this->numberHMetrics) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("hhea table must be parsed prior to calling this method",
                                         Zend_Pdf_Exception::PARSED_OUT_OF_ORDER);
        }

        try {
            $this->_jumpToTable('OS/2');
        } catch (Zend_Pdf_Exception $e) {
            /* This table is not always present. If missing, use default values.
             */
            #require_once 'Zend/Pdf/Exception.php';
            if ($e->getCode() == Zend_Pdf_Exception::REQUIRED_TABLE_NOT_FOUND) {
                $this->_debugLog('No OS/2 table found. Using default values');
                $this->fontWeight = Zend_Pdf_Font::WEIGHT_NORMAL;
                $this->fontWidth = Zend_Pdf_Font::WIDTH_NORMAL;
                $this->isEmbeddable = true;
                $this->isSubsettable = true;
                $this->strikeThickness = $this->unitsPerEm * 0.05;
                $this->strikePosition  = $this->unitsPerEm * 0.225;
                $this->isSerifFont      = false;    // the style of the font is unknown
                $this->isSansSerifFont  = false;
                $this->isOrnamentalFont = false;
                $this->isScriptFont     = false;
                $this->isSymbolicFont   = false;
                $this->isAdobeLatinSubset = false;
                $this->vendorID = '';
                $this->xHeight = 0;
                $this->capitalHeight = 0;
                return;
            } else {
                /* Something else went wrong. Throw this exception higher up the chain.
                 */
                throw $e;
                throw new Zend_Pdf_Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        /* Version 0 tables are becoming rarer these days. They are only found
         * in older fonts.
         *
         * Version 1 formally defines the Unicode character range bits and adds
         * two new fields to the end of the table.
         *
         * Version 2 defines several additional flags to the embedding bits
         * (fsType field) and five new fields to the end of the table.
         *
         * Versions 2 and 3 are structurally identical. There are only two
         * significant differences between the two: First, in version 3, the
         * average character width (xAvgCharWidth field) is calculated using all
         * non-zero width glyphs in the font instead of just the Latin lower-
         * case alphabetic characters; this doesn't affect us. Second, in
         * version 3, the embedding bits (fsType field) have been made mutually
         * exclusive; see additional discusson on this below.
         *
         * We can understand all four of these table versions.
         */
        $tableVersion = $this->readUInt(2);
        if (($tableVersion < 0) || ($tableVersion > 3)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Unable to read version $tableVersion table",
                                         Zend_Pdf_Exception::DONT_UNDERSTAND_TABLE_VERSION);
        }
        $this->_debugLog('Version %d table', $tableVersion);

        $this->averageCharWidth = $this->readInt(2);

        /* Indicates the visual weight and aspect ratio of the characters. Used
         * primarily to logically sort fonts in lists. Also used to help choose
         * a more appropriate substitute font when necessary. See the WEIGHT_
         * and WIDTH_ constants defined in Zend_Pdf_Font.
         */
        $this->fontWeight = $this->readUInt(2);
        $this->fontWidth  = $this->readUInt(2);

        /* Describes the font embedding licensing rights. We can only embed and
         * subset a font when given explicit permission.
         *
         * NOTE: We always interpret these bits according to the rules defined
         * in version 3 of this table, regardless of the actual version. This
         * means we will perform our checks in order from the most-restrictive
         * to the least.
         */
        $embeddingFlags = $this->readUInt(2);
        $this->_debugLog('Embedding flags: %d', $embeddingFlags);
        if ($this->isBitSet(9, $embeddingFlags)) {
            /* Only bitmaps may be embedded. We don't have the ability to strip
             * outlines from fonts yet, so this means no embed.
             */
            $this->isEmbeddable = false;
        } else if ($this->isBitSet(1, $embeddingFlags)) {
            /* Restricted license embedding. We currently don't have any way to
             * enforce this, so interpret this as no embed. This may be revised
             * in the future...
             */
            $this->isEmbeddable = false;
        } else {
            /* The remainder of the bit settings grant us permission to embed
             * the font. There may be additional usage rights granted or denied
             * but those only affect the PDF viewer application, not our code.
             */
            $this->isEmbeddable = true;
        }
        $this->_debugLog('Font ' . ($this->isEmbeddable ? 'may' : 'may not') . ' be embedded');
        $isSubsettable = $this->isBitSet($embeddingFlags, 8);

        /* Recommended size and offset for synthesized subscript characters.
         */
        $this->subscriptXSize = $this->readInt(2);
        $this->subscriptYSize = $this->readInt(2);
        $this->subscriptXOffset = $this->readInt(2);
        $this->subscriptYOffset = $this->readInt(2);

        /* Recommended size and offset for synthesized superscript characters.
         */
        $this->superscriptXSize = $this->readInt(2);
        $this->superscriptYSize = $this->readInt(2);
        $this->superscriptXOffset = $this->readInt(2);
        $this->superscriptYOffset = $this->readInt(2);

        /* Size and vertical offset for the strikethrough.
         */
        $this->strikeThickness = $this->readInt(2);
        $this->strikePosition  = $this->readInt(2);

        /* Describes the class of font: serif, sans serif, script. etc. These
         * values are defined here:
         *   http://www.microsoft.com/OpenType/OTSpec/ibmfc.htm
         */
        $familyClass = ($this->readUInt(2) >> 8);    // don't care about subclass
        $this->_debugLog('Font family class: %d', $familyClass);
        $this->isSerifFont      = ((($familyClass >= 1) && ($familyClass <= 5)) ||
                                   ($familyClass == 7));
        $this->isSansSerifFont  = ($familyClass == 8);
        $this->isOrnamentalFont = ($familyClass == 9);
        $this->isScriptFont     = ($familyClass == 10);
        $this->isSymbolicFont   = ($familyClass == 12);

        /* Skip over the PANOSE number. The interesting values for us overlap
         * with the font family class defined above.
         */
        $this->skipBytes(10);

        /* The Unicode range is made up of four 4-byte unsigned long integers
         * which are used as bitfields covering a 128-bit range. Each bit
         * represents a Unicode code block. If the bit is set, this font at
         * least partially covers the characters in that block.
         */
        $unicodeRange1 = $this->readUInt(4);
        $unicodeRange2 = $this->readUInt(4);
        $unicodeRange3 = $this->readUInt(4);
        $unicodeRange4 = $this->readUInt(4);
        $this->_debugLog('Unicode ranges: 0x%x 0x%x 0x%x 0x%x',
                        $unicodeRange1, $unicodeRange2, $unicodeRange3, $unicodeRange4);

        /* The Unicode range is currently only used to decide if the character
         * set covered by the font is a subset of the Adobe Latin set, meaning
         * it only has the basic latin set. If it covers any other characters,
         * even any of the extended latin characters, it is considered symbolic
         * to PDF and must be described differently in the Font Descriptor.
         */
        /**
         * @todo Font is recognized as Adobe Latin subset font if it only contains
         * Basic Latin characters (only bit 0 of Unicode range bits is set).
         * Actually, other Unicode subranges like General Punctuation (bit 31) also
         * fall into Adobe Latin characters. So this code has to be modified.
         */
        $this->isAdobeLatinSubset = (($unicodeRange1 == 1) && ($unicodeRange2 == 0) &&
                                      ($unicodeRange3 == 0) && ($unicodeRange4 == 0));
        $this->_debugLog(($this->isAdobeLatinSubset ? 'Is' : 'Is not') . ' a subset of Adobe Latin');

        $this->vendorID = $this->readBytes(4);

        /* Skip the font style bits. We use the values found in the 'head' table.
         * Also skip the first Unicode and last Unicode character indicies. Our
         * cmap implementation does not need these values.
         */
        $this->skipBytes(6);

        /* Typographic ascender, descender, and line gap. These values are
         * preferred to those in the 'hhea' table.
         */
        $this->ascent = $this->readInt(2);
        $this->descent = $this->readInt(2);
        $this->lineGap = $this->readInt(2);

        /* The descent value is supposed to be negative--it's the distance
         * relative to the baseline. However, some fonts improperly store a
         * positive value in this field. If a positive value is found, flip the
         * sign and record a warning in the debug log that we did this.
         */
        if ($this->descent > 0) {
            $this->_debugLog('Warning: Font should specify negative descent. Actual: %d; Using %d',
                             $this->descent, -$this->descent);
            $this->descent = -$this->descent;
        }

        /* Skip over Windows-specific ascent and descent.
         */
        $this->skipBytes(4);

        /* Versions 0 and 1 tables do not contain the x or capital height
         * fields. Record zero for unknown.
         */
        if ($tableVersion < 2) {
            $this->xHeight = 0;
            $this->capitalHeight = 0;
        } else {

            /* Skip over the Windows code page coverages. We are only concerned
             * with Unicode coverage.
             */
            $this->skipBytes(8);

            $this->xHeight = $this->readInt(2);
            $this->capitalHeight = $this->readInt(2);

            /* Ignore the remaining fields in this table. They are Windows-specific.
             */
        }
        /**
         * @todo Obtain the x and capital heights from the 'glyf' table if they
         *   haven't been supplied here instead of storing zero.
         */
    }


    /**
     * Parses the OpenType hmtx (Horizontal Metrics) table.
     *
     * The hmtx table contains the horizontal metrics for every glyph contained
     * within the font. These are the critical values for horizontal layout of
     * text.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseHmtxTable()
    {
        $this->_jumpToTable('hmtx');

        if (! $this->numberHMetrics) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("hhea table must be parsed prior to calling this method",
                                         Zend_Pdf_Exception::PARSED_OUT_OF_ORDER);
        }

        /* We only understand version 0 tables.
         */
        if ($this->metricDataFormat != 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Unable to read format $this->metricDataFormat table.",
                                         Zend_Pdf_Exception::DONT_UNDERSTAND_TABLE_VERSION);
        }

        /* The hmtx table has no header. For each glpyh in the font, it contains
         * the glyph's advance width and its left side bearing. We don't use the
         * left side bearing.
         */
        $glyphWidths = array();
        for ($i = 0; $i < $this->numberHMetrics; $i++) {
            $glyphWidths[$i] = $this->readUInt(2);
            $this->skipBytes(2);
        }
        /* Populate last value for the rest of array
         */
        while (count($glyphWidths) < $this->numGlyphs) {
            $glyphWidths[] = end($glyphWidths);
        }
        $this->glyphWidths = $glyphWidths;

        /* There is an optional table of left side bearings which is sometimes
         * used for monospaced fonts. We don't use the left side bearing, so
         * we can safely ignore it.
         */
    }


    /**
     * Parses the OpenType cmap (Character to Glyph Mapping) table.
     *
     * The cmap table provides the maps from character codes to font glyphs.
     * There are usually at least two character maps in a font: Microsoft Unicode
     * and Macintosh Roman. For very complex fonts, there may also be mappings
     * for the characters in the Unicode Surrogates Area, which are UCS-4
     * characters.
     *
     * @todo Need to rework the selection logic for picking a subtable. We should
     *   have an explicit list of preferences, followed by a list of those that
     *   are tolerable. Most specifically, since everything above this layer deals
     *   in Unicode, we need to be sure to only accept format 0 MacRoman tables.
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _parseCmapTable()
    {
        $this->_jumpToTable('cmap');
        $baseOffset = $this->_tableDirectory['cmap']['offset'];

        /* We only understand version 0 tables.
         */
        $tableVersion = $this->readUInt(2);
        if ($tableVersion != 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Unable to read version $tableVersion table",
                                         Zend_Pdf_Exception::DONT_UNDERSTAND_TABLE_VERSION);
        }
        $this->_debugLog('Version %d table', $tableVersion);

        $subtableCount = $this->readUInt(2);
        $this->_debugLog('%d subtables', $subtableCount);

        /* Like the name table, there may be many different encoding subtables
         * present. Ideally, we are looking for an acceptable Unicode table.
         */
        $subtables = array();
        for ($subtableIndex = 0; $subtableIndex < $subtableCount; $subtableIndex++) {

            $platformID = $this->readUInt(2);
            $encodingID = $this->readUInt(2);

            if (! ( (($platformID == 0) && ($encodingID == 3)) ||    // Unicode 2.0 or later
                    (($platformID == 0) && ($encodingID == 0)) ||    // Unicode
                    (($platformID == 3) && ($encodingID == 1)) ||    // Microsoft Unicode
                    (($platformID == 1) && ($encodingID == 0))       // Mac Roman
                   ) ) {
                $this->_debugLog('Unsupported encoding: platformID: %d; encodingID: %d; skipping',
                                 $platformID, $encodingID);
                $this->skipBytes(4);
                continue;
            }

            $subtableOffset = $this->readUInt(4);
            if ($subtableOffset < 0) {    // Sanity check for 4-byte unsigned on 32-bit platform
                $this->_debugLog('Offset 0x%x out of range for platformID: %d; skipping',
                                 $subtableOffset, $platformID);
                continue;
            }

            $this->_debugLog('Found subtable; platformID: %d; encodingID: %d; offset: 0x%x (0x%x)',
                             $platformID, $encodingID, $baseOffset + $subtableOffset, $subtableOffset);

            $subtables[$platformID][$encodingID][] = $subtableOffset;
        }

        /* In preferred order, find a subtable to use.
         */
        $offsets = array();

        /* Unicode 2.0 or later semantics
         */
        if (isset($subtables[0][3])) {
            foreach ($subtables[0][3] as $offset) {
                $offsets[] = $offset;
            }
        }

        /* Unicode default semantics
         */
        if (isset($subtables[0][0])) {
            foreach ($subtables[0][0] as $offset) {
                $offsets[] = $offset;
            }
        }

        /* Microsoft Unicode
         */
        if (isset($subtables[3][1])) {
            foreach ($subtables[3][1] as $offset) {
                $offsets[] = $offset;
            }
        }

        /* Mac Roman.
         */
        if (isset($subtables[1][0])) {
            foreach ($subtables[1][0] as $offset) {
                $offsets[] = $offset;
            }
        }

        $cmapType = -1;

        foreach ($offsets as $offset) {
            $cmapOffset = $baseOffset + $offset;
            $this->moveToOffset($cmapOffset);
            $format = $this->readUInt(2);
            $language = -1;
            switch ($format) {
                case 0x0:
                    $cmapLength = $this->readUInt(2);
                    $language = $this->readUInt(2);
                    if ($language != 0) {
                        $this->_debugLog('Type 0 cmap tables must be language-independent;'
                                         . ' language: %d; skipping', $language);
                        continue;
                    }
                    break;

                case 0x4:    // break intentionally omitted
                case 0x6:
                    $cmapLength = $this->readUInt(2);
                    $language = $this->readUInt(2);
                    if ($language != 0) {
                        $this->_debugLog('Warning: cmap tables must be language-independent - this font'
                                         . ' may not work properly; language: %d', $language);
                    }
                    break;

                case 0x2:    // break intentionally omitted
                case 0x8:    // break intentionally omitted
                case 0xa:    // break intentionally omitted
                case 0xc:
                    $this->_debugLog('Format: 0x%x currently unsupported; skipping', $format);
                    continue;
                    //$this->skipBytes(2);
                    //$cmapLength = $this->readUInt(4);
                    //$language = $this->readUInt(4);
                    //if ($language != 0) {
                    //    $this->_debugLog('Warning: cmap tables must be language-independent - this font'
                    //                     . ' may not work properly; language: %d', $language);
                    //}
                    //break;

                default:
                    $this->_debugLog('Unknown subtable format: 0x%x; skipping', $format);
                    continue;
            }
            $cmapType = $format;
            break;
        }
        if ($cmapType == -1) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Unable to find usable cmap table',
                                         Zend_Pdf_Exception::CANT_FIND_GOOD_CMAP);
        }

        /* Now extract the subtable data and create a Zend_Pdf_FontCmap object.
         */
        $this->_debugLog('Using cmap type %d; offset: 0x%x; length: %d',
                         $cmapType, $cmapOffset, $cmapLength);
        $this->moveToOffset($cmapOffset);
        $cmapData = $this->readBytes($cmapLength);

        #require_once 'Zend/Pdf/Cmap.php';
        $this->cmap = Zend_Pdf_Cmap::cmapWithTypeData($cmapType, $cmapData);
    }


    /**
     * Reads the scaler type from the header of the OpenType font file and
     * returns it as an unsigned long integer.
     *
     * The scaler type defines the type of font: OpenType font files may contain
     * TrueType or PostScript outlines. Throws an exception if the scaler type
     * is not recognized.
     *
     * @return integer
     * @throws Zend_Pdf_Exception
     */
    protected function _readScalerType()
    {
        if ($this->_scalerType != 0) {
            return $this->_scalerType;
        }

        $this->moveToOffset(0);

        $this->_scalerType = $this->readUInt(4);

        switch ($this->_scalerType) {
            case 0x00010000:    // version 1.0 - Windows TrueType signature
                $this->_debugLog('Windows TrueType signature');
                break;

            case 0x74727565:    // 'true' - Macintosh TrueType signature
                $this->_debugLog('Macintosh TrueType signature');
                break;

            case 0x4f54544f:    // 'OTTO' - the CFF signature
                $this->_debugLog('PostScript CFF signature');
                break;

            case 0x74797031:    // 'typ1'
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Unsupported font type: PostScript in sfnt wrapper',
                                             Zend_Pdf_Exception::WRONG_FONT_TYPE);

            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Not an OpenType font file',
                                             Zend_Pdf_Exception::WRONG_FONT_TYPE);
        }
        return $this->_scalerType;
    }

    /**
     * Validates a given table's existence, then sets the file pointer to the
     * start of that table.
     *
     * @param string $tableName
     * @throws Zend_Pdf_Exception
     */
    protected function _jumpToTable($tableName)
    {
        if (empty($this->_tableDirectory[$tableName])) {    // do not allow NULL or zero
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Required table '$tableName' not found!",
                                         Zend_Pdf_Exception::REQUIRED_TABLE_NOT_FOUND);
        }
        $this->_debugLog("Parsing $tableName table...");
        $this->moveToOffset($this->_tableDirectory[$tableName]['offset']);
    }

    /**
     * Reads the fixed 16.16 table version number and checks for compatibility.
     * If the version is incompatible, throws an exception. If it is compatible,
     * returns the version number.
     *
     * @param float $minVersion Minimum compatible version number.
     * @param float $maxVertion Maximum compatible version number.
     * @return float Table version number.
     * @throws Zend_Pdf_Exception
     */
    protected function _readTableVersion($minVersion, $maxVersion)
    {
        $tableVersion = $this->readFixed(16, 16);
        if (($tableVersion < $minVersion) || ($tableVersion > $maxVersion)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Unable to read version $tableVersion table",
                                         Zend_Pdf_Exception::DONT_UNDERSTAND_TABLE_VERSION);
        }
        $this->_debugLog('Version %.2f table', $tableVersion);
        return $tableVersion;
    }

    /**
     * Utility method that returns ISO 639 two-letter language codes from the
     * TrueType platform and language ID. Returns NULL for languages that are
     * not supported.
     *
     * @param integer $platformID
     * @param integer $encodingID
     * @return string | null
     */
    protected function _languageCodeForPlatform($platformID, $languageID)
    {
        if ($platformID == 3) {    // Microsoft encoding.
            /* The low-order bytes specify the language, the high-order bytes
             * specify the dialect. We just care about the language. For the
             * complete list, see:
             *   http://www.microsoft.com/globaldev/reference/lcid-all.mspx
             */
            $languageID &= 0xff;
            switch ($languageID) {
                case 0x09:
                    return 'en';
                case 0x0c:
                    return 'fr';
                case 0x07:
                    return 'de';
                case 0x10:
                    return 'it';
                case 0x13:
                    return 'nl';
                case 0x1d:
                    return 'sv';
                case 0x0a:
                    return 'es';
                case 0x06:
                    return 'da';
                case 0x16:
                    return 'pt';
                case 0x14:
                    return 'no';
                case 0x0d:
                    return 'he';
                case 0x11:
                    return 'ja';
                case 0x01:
                    return 'ar';
                case 0x0b:
                    return 'fi';
                case 0x08:
                    return 'el';

                default:
                    return null;
            }

        } else if ($platformID == 1) {    // Macintosh encoding.
            switch ($languageID) {
                case 0:
                    return 'en';
                case 1:
                    return 'fr';
                case 2:
                    return 'de';
                case 3:
                    return 'it';
                case 4:
                    return 'nl';
                case 5:
                    return 'sv';
                case 6:
                    return 'es';
                case 7:
                    return 'da';
                case 8:
                    return 'pt';
                case 9:
                    return 'no';
                case 10:
                    return 'he';
                case 11:
                    return 'ja';
                case 12:
                    return 'ar';
                case 13:
                    return 'fi';
                case 14:
                    return 'el';

                default:
                    return null;
            }

        } else {    // Unknown encoding.
            return null;
        }
    }

}
