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
 * @version    $Id: Simple.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Name.php';


/** Zend_Pdf_Resource_Font */
#require_once 'Zend/Pdf/Resource/Font.php';

/**
 * Adobe PDF Simple fonts implementation
 *
 * PDF simple fonts functionality is presented by Adobe Type 1
 * (including standard PDF Type1 built-in fonts) and TrueType fonts support.
 *
 * Both fonts have the following properties:
 * - Glyphs in the font are selected by single-byte character codes obtained from a
 *   string that is shown by the text-showing operators. Logically, these codes index
 *   into a table of 256 glyphs; the mapping from codes to glyphs is called the font’s
 *   encoding.
 *   PDF specification provides a possibility to specify any user defined encoding in addition
 *   to the standard built-in encodings: Standard-Encoding, MacRomanEncoding, WinAnsiEncoding,
 *   and PDFDocEncoding, but Zend_Pdf simple fonts implementation operates only with
 *   Windows ANSI encoding (except Symbol and ZapfDingbats built-in fonts).
 *
 * - Each glyph has a single set of metrics, including a horizontal displacement or
 *   width. That is, simple fonts support only horizontal writing mode.
 *
 *
 * The code in this class is common to both types. However, you will only deal
 * directly with subclasses.
 *
 * Font objects should be normally be obtained from the factory methods
 * {@link Zend_Pdf_Font::fontWithName} and {@link Zend_Pdf_Font::fontWithPath}.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Resource_Font_Simple extends Zend_Pdf_Resource_Font
{
    /**
     * Object representing the font's cmap (character to glyph map).
     * @var Zend_Pdf_Cmap
     */
    protected $_cmap = null;

    /**
     * Array containing the widths of each of the glyphs contained in the font.
     *
     * Keys are integers starting from 0, which coresponds to Zend_Pdf_Cmap::MISSING_CHARACTER_GLYPH.
     *
     * Font character map may contain gaps for actually used glyphs, nevertheless glyphWidths array
     * contains widths for all glyphs even they are unused.
     *
     * @var array
     */
    protected $_glyphWidths = null;

    /**
     * Width for glyphs missed in the font
     *
     * Note: Adobe PDF specfication (V1.4 - V1.6) doesn't define behavior for rendering
     * characters missed in the standard PDF fonts (such us 0x7F (DEL) Windows ANSI code)
     * Adobe Font Metrics files doesn't also define metrics for "missed glyph".
     * We provide character width as "0" for this case, but actually it depends on PDF viewer
     * implementation.
     *
     * @var integer
     */
    protected $_missingGlyphWidth = 0;


    /**** Public Interface ****/


  /* Object Lifecycle */

    /**
     * Object constructor
     *
     */
    public function __construct()
    {
        parent::__construct();

        /**
         * @todo
         * It's easy to add other encodings support now (Standard-Encoding, MacRomanEncoding,
         * PDFDocEncoding, MacExpertEncoding, Symbol, and ZapfDingbats).
         * Steps for the implementation:
         * - completely describe all PDF single byte encodings in the documentation
         * - implement non-WinAnsi encodings processing into encodeString()/decodeString() methods
         *
         * These encodings will be automatically supported for standard builtin PDF fonts as well
         * as for external fonts.
         */
        $this->_resource->Encoding = new Zend_Pdf_Element_Name('WinAnsiEncoding');
    }

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
        return $this->_cmap->glyphNumbersForCharacters($characterCodes);
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
        return $this->_cmap->glyphNumberForCharacter($characterCode);
    }

    /**
     * Returns a number between 0 and 1 inclusive that indicates the percentage
     * of characters in the string which are covered by glyphs in this font.
     *
     * Since no one font will contain glyphs for the entire Unicode character
     * range, this method can be used to help locate a suitable font when the
     * actual contents of the string are not known.
     *
     * Note that some fonts lie about the characters they support. Additionally,
     * fonts don't usually contain glyphs for control characters such as tabs
     * and line breaks, so it is rare that you will get back a full 1.0 score.
     * The resulting value should be considered informational only.
     *
     * @param string $string
     * @param string $charEncoding (optional) Character encoding of source text.
     *   If omitted, uses 'current locale'.
     * @return float
     */
    public function getCoveredPercentage($string, $charEncoding = '')
    {
        /* Convert the string to UTF-16BE encoding so we can match the string's
         * character codes to those found in the cmap.
         */
        if ($charEncoding != 'UTF-16BE') {
            if (PHP_OS != 'AIX') { // AIX doesnt know what UTF-16BE is
                $string = iconv($charEncoding, 'UTF-16BE', $string);
            }
        }

        $charCount = (PHP_OS != 'AIX') ? iconv_strlen($string, 'UTF-16BE') : strlen($string);
        if ($charCount == 0) {
            return 0;
        }

        /* Fetch the covered character code list from the font's cmap.
         */
        $coveredCharacters = $this->_cmap->getCoveredCharacters();

        /* Calculate the score by doing a lookup for each character.
         */
        $score = 0;
        $maxIndex = strlen($string);
        for ($i = 0; $i < $maxIndex; $i++) {
            /**
             * @todo Properly handle characters encoded as surrogate pairs.
             */
            $charCode = (ord($string[$i]) << 8) | ord($string[++$i]);
            /* This could probably be optimized a bit with a binary search...
             */
            if (in_array($charCode, $coveredCharacters)) {
                $score++;
            }
        }
        return $score / $charCount;
    }

    /**
     * Returns the widths of the glyphs.
     *
     * The widths are expressed in the font's glyph space. You are responsible
     * for converting to user space as necessary. See {@link unitsPerEm()}.
     *
     * See also {@link widthForGlyph()}.
     *
     * @param array &$glyphNumbers Array of glyph numbers.
     * @return array Array of glyph widths (integers).
     */
    public function widthsForGlyphs($glyphNumbers)
    {
        $widths = array();
        foreach ($glyphNumbers as $key => $glyphNumber) {
            if (!isset($this->_glyphWidths[$glyphNumber])) {
                $widths[$key] = $this->_missingGlyphWidth;
            } else {
                $widths[$key] = $this->_glyphWidths[$glyphNumber];
            }
        }
        return $widths;
    }

    /**
     * Returns the width of the glyph.
     *
     * Like {@link widthsForGlyphs()} but used for one glyph at a time.
     *
     * @param integer $glyphNumber
     * @return integer
     */
    public function widthForGlyph($glyphNumber)
    {
        if (!isset($this->_glyphWidths[$glyphNumber])) {
            return $this->_missingGlyphWidth;
        }
        return $this->_glyphWidths[$glyphNumber];
    }

    /**
     * Convert string to the font encoding.
     *
     * The method is used to prepare string for text drawing operators
     *
     * @param string $string
     * @param string $charEncoding Character encoding of source text.
     * @return string
     */
    public function encodeString($string, $charEncoding)
    {
        if (PHP_OS == 'AIX') {
            return $string; // returning here b/c AIX doesnt know what CP1252 is
        }

        return iconv($charEncoding, 'CP1252//IGNORE', $string);
    }

    /**
     * Convert string from the font encoding.
     *
     * The method is used to convert strings retrieved from existing content streams
     *
     * @param string $string
     * @param string $charEncoding Character encoding of resulting text.
     * @return string
     */
    public function decodeString($string, $charEncoding)
    {
        return iconv('CP1252', $charEncoding, $string);
    }
}
