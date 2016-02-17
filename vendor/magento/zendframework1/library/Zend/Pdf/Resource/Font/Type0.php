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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/Name.php';


/** Zend_Pdf_Resource_Font */
#require_once 'Zend/Pdf/Resource/Font.php';

/**
 * Adobe PDF composite fonts implementation
 *
 * A composite font is one whose glyphs are obtained from other fonts or from fontlike
 * objects called CIDFonts ({@link Zend_Pdf_Resource_Font_CidFont}), organized hierarchically.
 * In PDF, a composite font is represented by a font dictionary whose Subtype value is Type0;
 * this is also called a Type 0 font (the Type 0 font at the top level of the hierarchy is the
 * root font).
 *
 * In PDF, a Type 0 font is a CID-keyed font.
 *
 * CID-keyed fonts provide effective method to operate with multi-byte character encodings.
 *
 * The CID-keyed font architecture specifies the external representation of certain font programs,
 * called CMap and CIDFont files, along with some conventions for combining and using those files.
 *
 * A CID-keyed font is the combination of a CMap with one or more CIDFonts, simple fonts,
 * or composite fonts containing glyph descriptions.
 *
 * The term 'CID-keyed font' reflects the fact that CID (character identifier) numbers
 * are used to index and access the glyph descriptions in the font.
 *
 *
 * Font objects should be normally be obtained from the factory methods
 * {@link Zend_Pdf_Font::fontWithName} and {@link Zend_Pdf_Font::fontWithPath}.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Font_Type0 extends Zend_Pdf_Resource_Font
{
    /**
     * Descendant CIDFont
     *
     * @var Zend_Pdf_Resource_Font_CidFont
     */
    private $_descendantFont;


    /**
     * Generate ToUnicode character map data
     *
     * @return string
     */
    static private function getToUnicodeCMapData()
    {
        return '/CIDInit /ProcSet findresource begin '              . "\n"
             . '12 dict begin '                                     . "\n"
             . 'begincmap '                                         . "\n"
             . '/CIDSystemInfo '                                    . "\n"
             . '<</Registry (Adobe) '                               . "\n"
             . '/Ordering (UCS) '                                   . "\n"
             . '/Supplement 0'                                      . "\n"
             . '>> def'                                             . "\n"
             . '/CMapName /Adobe-Identity-UCS def '                 . "\n"
             . '/CMapType 2 def '                                   . "\n"
             . '1 begincodespacerange'                              . "\n"
             . '<0000> <FFFF> '                                     . "\n"
             . 'endcodespacerange '                                 . "\n"
             . '1 beginbfrange '                                    . "\n"
             . '<0000> <FFFF> <0000> '                              . "\n"
             . 'endbfrange '                                        . "\n"
             . 'endcmap '                                           . "\n"
             . 'CMapName currentdict /CMap defineresource pop '     . "\n"
             . 'end '
             . 'end ';
            }

    /**
     * Object constructor
     *
     */
    public function __construct(Zend_Pdf_Resource_Font_CidFont $descendantFont)
    {
        parent::__construct();

        $this->_objectFactory->attach($descendantFont->getFactory());

        $this->_fontType       = Zend_Pdf_Font::TYPE_TYPE_0;
        $this->_descendantFont = $descendantFont;


        $this->_fontNames    = $descendantFont->getFontNames();

        $this->_isBold       = $descendantFont->isBold();
        $this->_isItalic     = $descendantFont->isItalic();
        $this->_isMonospaced = $descendantFont->isMonospace();

        $this->_underlinePosition  = $descendantFont->getUnderlinePosition();
        $this->_underlineThickness = $descendantFont->getUnderlineThickness();
        $this->_strikePosition     = $descendantFont->getStrikePosition();
        $this->_strikeThickness    = $descendantFont->getStrikeThickness();

        $this->_unitsPerEm = $descendantFont->getUnitsPerEm();

        $this->_ascent  = $descendantFont->getAscent();
        $this->_descent = $descendantFont->getDescent();
        $this->_lineGap = $descendantFont->getLineGap();


        $this->_resource->Subtype         = new Zend_Pdf_Element_Name('Type0');
        $this->_resource->BaseFont        = new Zend_Pdf_Element_Name($descendantFont->getResource()->BaseFont->value);
        $this->_resource->DescendantFonts = new Zend_Pdf_Element_Array(array( $descendantFont->getResource() ));
        $this->_resource->Encoding        = new Zend_Pdf_Element_Name('Identity-H');

        $toUnicode = $this->_objectFactory->newStreamObject(self::getToUnicodeCMapData());
        $this->_resource->ToUnicode = $toUnicode;

    }

    /**
     * Returns an array of glyph numbers corresponding to the Unicode characters.
     *
     * Zend_Pdf uses 'Identity-H' encoding for Type 0 fonts.
     * So we don't need to perform any conversion
     *
     * See also {@link glyphNumberForCharacter()}.
     *
     * @param array $characterCodes Array of Unicode character codes (code points).
     * @return array Array of glyph numbers.
     */
    public function glyphNumbersForCharacters($characterCodes)
    {
        return $characterCodes;
    }

    /**
     * Returns the glyph number corresponding to the Unicode character.
     *
     * Zend_Pdf uses 'Identity-H' encoding for Type 0 fonts.
     * So we don't need to perform any conversion
     *
     * @param integer $characterCode Unicode character code (code point).
     * @return integer Glyph number.
     */
    public function glyphNumberForCharacter($characterCode)
    {
        return $characterCode;
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
        return $this->_descendantFont->getCoveredPercentage($string, $charEncoding);
    }

    /**
     * Returns the widths of the glyphs.
     *
     * The widths are expressed in the font's glyph space. You are responsible
     * for converting to user space as necessary. See {@link unitsPerEm()}.
     *
     * Throws an exception if the glyph number is out of range.
     *
     * See also {@link widthForGlyph()}.
     *
     * @param array &$glyphNumbers Array of glyph numbers.
     * @return array Array of glyph widths (integers).
     * @throws Zend_Pdf_Exception
     */
    public function widthsForGlyphs($glyphNumbers)
    {
        return $this->_descendantFont->widthsForChars($glyphNumbers);
    }

    /**
     * Returns the width of the glyph.
     *
     * Like {@link widthsForGlyphs()} but used for one glyph at a time.
     *
     * @param integer $glyphNumber
     * @return integer
     * @throws Zend_Pdf_Exception
     */
    public function widthForGlyph($glyphNumber)
    {
        return $this->_descendantFont->widthForChar($glyphNumber);
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
        return iconv($charEncoding, 'UTF-16BE', $string);
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
        return iconv('UTF-16BE', $charEncoding, $string);
    }
}
