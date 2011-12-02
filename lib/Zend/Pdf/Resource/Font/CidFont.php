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
 * @version    $Id: CidFont.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Internally used classes */
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String.php';


/** Zend_Pdf_Resource_Font */
#require_once 'Zend/Pdf/Resource/Font.php';

/**
 * Adobe PDF CIDFont font object implementation
 *
 * A CIDFont program contains glyph descriptions that are accessed using a CID as
 * the character selector. There are two types of CIDFont. A Type 0 CIDFont contains
 * glyph descriptions based on Adobe’s Type 1 font format, whereas those in a
 * Type 2 CIDFont are based on the TrueType font format.
 *
 * A CIDFont dictionary is a PDF object that contains information about a CIDFont program.
 * Although its Type value is Font, a CIDFont is not actually a font. It does not have an Encoding
 * entry, it cannot be listed in the Font subdictionary of a resource dictionary, and it cannot be
 * used as the operand of the Tf operator. It is used only as a descendant of a Type 0 font.
 * The CMap in the Type 0 font is what defines the encoding that maps character codes to CIDs
 * in the CIDFont.
 *
 * Font objects should be normally be obtained from the factory methods
 * {@link Zend_Pdf_Font::fontWithName} and {@link Zend_Pdf_Font::fontWithPath}.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Resource_Font_CidFont extends Zend_Pdf_Resource_Font
{
    /**
     * Object representing the font's cmap (character to glyph map).
     * @var Zend_Pdf_Cmap
     */
    protected $_cmap = null;

    /**
     * Array containing the widths of each character that have entries in used character map.
     *
     * @var array
     */
    protected $_charWidths = null;

    /**
     * Width for characters missed in the font
     *
     * @var integer
     */
    protected $_missingCharWidth = 0;


    /**
     * Object constructor
     *
     * @param Zend_Pdf_FileParser_Font_OpenType $fontParser Font parser object
     *   containing OpenType file.
     * @param integer $embeddingOptions Options for font embedding.
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_FileParser_Font_OpenType $fontParser)
    {
        parent::__construct();

        $fontParser->parse();


        /* Object properties */

        $this->_fontNames = $fontParser->names;

        $this->_isBold       = $fontParser->isBold;
        $this->_isItalic     = $fontParser->isItalic;
        $this->_isMonospaced = $fontParser->isMonospaced;

        $this->_underlinePosition  = $fontParser->underlinePosition;
        $this->_underlineThickness = $fontParser->underlineThickness;
        $this->_strikePosition     = $fontParser->strikePosition;
        $this->_strikeThickness    = $fontParser->strikeThickness;

        $this->_unitsPerEm = $fontParser->unitsPerEm;

        $this->_ascent  = $fontParser->ascent;
        $this->_descent = $fontParser->descent;
        $this->_lineGap = $fontParser->lineGap;


        $this->_cmap = $fontParser->cmap;


        /* Resource dictionary */

        $baseFont = $this->getFontName(Zend_Pdf_Font::NAME_POSTSCRIPT, 'en', 'UTF-8');
        $this->_resource->BaseFont = new Zend_Pdf_Element_Name($baseFont);


        /**
         * Prepare widths array.
         */
        /* Constract characters widths array using font CMap and glyphs widths array */
        $glyphWidths = $fontParser->glyphWidths;
        $charGlyphs  = $this->_cmap->getCoveredCharactersGlyphs();
        $charWidths  = array();
        foreach ($charGlyphs as $charCode => $glyph) {
            $charWidths[$charCode] = $glyphWidths[$glyph];
        }
        $this->_charWidths       = $charWidths;
        $this->_missingCharWidth = $glyphWidths[0];

        /* Width array optimization. Step1: extract default value */
        $widthFrequencies = array_count_values($charWidths);
        $defaultWidth          = null;
        $defaultWidthFrequency = -1;
        foreach ($widthFrequencies as $width => $frequency) {
            if ($frequency > $defaultWidthFrequency) {
                $defaultWidth          = $width;
                $defaultWidthFrequency = $frequency;
            }
        }

        // Store default value in the font dictionary
        $this->_resource->DW = new Zend_Pdf_Element_Numeric($this->toEmSpace($defaultWidth));

        // Remove characters which corresponds to default width from the widths array
        $defWidthChars = array_keys($charWidths, $defaultWidth);
        foreach ($defWidthChars as $charCode) {
            unset($charWidths[$charCode]);
        }

        // Order cheracter widths aray by character codes
        ksort($charWidths, SORT_NUMERIC);

        /* Width array optimization. Step2: Compact character codes sequences */
        $lastCharCode = -1;
        $widthsSequences = array();
        foreach ($charWidths as $charCode => $width) {
            if ($lastCharCode == -1) {
                $charCodesSequense = array();
                $sequenceStartCode = $charCode;
            } else if ($charCode != $lastCharCode + 1) {
                // New chracters sequence detected
                $widthsSequences[$sequenceStartCode] = $charCodesSequense;
                $charCodesSequense = array();
                $sequenceStartCode = $charCode;
            }
            $charCodesSequense[] = $width;
            $lastCharCode = $charCode;
        }
        // Save last sequence, if widths array is not empty (it may happens for monospaced fonts)
        if (count($charWidths) != 0) {
            $widthsSequences[$sequenceStartCode] = $charCodesSequense;
        }

        $pdfCharsWidths = array();
        foreach ($widthsSequences as $startCode => $widthsSequence) {
            /* Width array optimization. Step3: Compact widths sequences */
            $pdfWidths        = array();
            $lastWidth        = -1;
            $widthsInSequence = 0;
            foreach ($widthsSequence as $width) {
                if ($lastWidth != $width) {
                    // New width is detected
                    if ($widthsInSequence != 0) {
                        // Previous width value was a part of the widths sequence. Save it as 'c_1st c_last w'.
                        $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($startCode);                         // First character code
                        $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($startCode + $widthsInSequence - 1); // Last character code
                        $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($this->toEmSpace($lastWidth));       // Width

                        // Reset widths sequence
                        $startCode = $startCode + $widthsInSequence;
                        $widthsInSequence = 0;
                    }

                    // Collect new width
                    $pdfWidths[] = new Zend_Pdf_Element_Numeric($this->toEmSpace($width));

                    $lastWidth = $width;
                } else {
                    // Width is equal to previous
                    if (count($pdfWidths) != 0) {
                        // We already have some widths collected
                        // So, we've just detected new widths sequence

                        // Remove last element from widths list, since it's a part of widths sequence
                        array_pop($pdfWidths);

                        // and write the rest if it's not empty
                        if (count($pdfWidths) != 0) {
                            // Save it as 'c_1st [w1 w2 ... wn]'.
                            $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($startCode); // First character code
                            $pdfCharsWidths[] = new Zend_Pdf_Element_Array($pdfWidths);   // Widths array

                            // Reset widths collection
                            $startCode += count($pdfWidths);
                            $pdfWidths = array();
                        }

                        $widthsInSequence = 2;
                    } else {
                        // Continue widths sequence
                        $widthsInSequence++;
                    }
                }
            }

            // Check if we have widths collection or widths sequence to wite it down
            if (count($pdfWidths) != 0) {
                // We have some widths collected
                // Save it as 'c_1st [w1 w2 ... wn]'.
                $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($startCode); // First character code
                $pdfCharsWidths[] = new Zend_Pdf_Element_Array($pdfWidths);   // Widths array
            } else if ($widthsInSequence != 0){
                // We have widths sequence
                // Save it as 'c_1st c_last w'.
                $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($startCode);                         // First character code
                $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($startCode + $widthsInSequence - 1); // Last character code
                $pdfCharsWidths[] = new Zend_Pdf_Element_Numeric($this->toEmSpace($lastWidth));       // Width
            }
        }

        /* Create the Zend_Pdf_Element_Array object and add it to the font's
         * object factory and resource dictionary.
         */
        $widthsArrayElement = new Zend_Pdf_Element_Array($pdfCharsWidths);
        $widthsObject = $this->_objectFactory->newObject($widthsArrayElement);
        $this->_resource->W = $widthsObject;


        /* CIDSystemInfo dictionary */
        $cidSystemInfo = new Zend_Pdf_Element_Dictionary();
        $cidSystemInfo->Registry   = new Zend_Pdf_Element_String('Adobe');
        $cidSystemInfo->Ordering   = new Zend_Pdf_Element_String('UCS');
        $cidSystemInfo->Supplement = new Zend_Pdf_Element_Numeric(0);
        $cidSystemInfoObject            = $this->_objectFactory->newObject($cidSystemInfo);
        $this->_resource->CIDSystemInfo = $cidSystemInfoObject;
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
        /**
         * CIDFont object is not actually a font. It does not have an Encoding entry,
         * it cannot be listed in the Font subdictionary of a resource dictionary, and
         * it cannot be used as the operand of the Tf operator.
         *
         * Throw an exception.
         */
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('CIDFont PDF objects could not be used as the operand of the text drawing operators');
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
        /**
         * CIDFont object is not actually a font. It does not have an Encoding entry,
         * it cannot be listed in the Font subdictionary of a resource dictionary, and
         * it cannot be used as the operand of the Tf operator.
         *
         * Throw an exception.
         */
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('CIDFont PDF objects could not be used as the operand of the text drawing operators');
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
            $string = iconv($charEncoding, 'UTF-16BE', $string);
        }

        $charCount = iconv_strlen($string, 'UTF-16BE');
        if ($charCount == 0) {
            return 0;
        }

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
            if (isset($this->_charWidths[$charCode])) {
                $score++;
            }
        }
        return $score / $charCount;
    }

    /**
     * Returns the widths of the Chars.
     *
     * The widths are expressed in the font's glyph space. You are responsible
     * for converting to user space as necessary. See {@link unitsPerEm()}.
     *
     * See also {@link widthForChar()}.
     *
     * @param array &$glyphNumbers Array of glyph numbers.
     * @return array Array of glyph widths (integers).
     */
    public function widthsForChars($charCodes)
    {
        $widths = array();
        foreach ($charCodes as $key => $charCode) {
            if (!isset($this->_charWidths[$charCode])) {
                $widths[$key] = $this->_missingCharWidth;
            } else {
                $widths[$key] = $this->_charWidths[$charCode];
            }
        }
        return $widths;
    }

    /**
     * Returns the width of the character.
     *
     * Like {@link widthsForChars()} but used for one char at a time.
     *
     * @param integer $charCode
     * @return integer
     */
    public function widthForChar($charCode)
    {
        if (!isset($this->_charWidths[$charCode])) {
            return $this->_missingCharWidth;
        }
        return $this->_charWidths[$charCode];
    }

    /**
     * Returns the widths of the glyphs.
     *
     * @param array &$glyphNumbers Array of glyph numbers.
     * @return array Array of glyph widths (integers).
     * @throws Zend_Pdf_Exception
     */
    public function widthsForGlyphs($glyphNumbers)
    {
        /**
         * CIDFont object is not actually a font. It does not have an Encoding entry,
         * it cannot be listed in the Font subdictionary of a resource dictionary, and
         * it cannot be used as the operand of the Tf operator.
         *
         * Throw an exception.
         */
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('CIDFont PDF objects could not be used as the operand of the text drawing operators');
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
        /**
         * CIDFont object is not actually a font. It does not have an Encoding entry,
         * it cannot be listed in the Font subdictionary of a resource dictionary, and
         * it cannot be used as the operand of the Tf operator.
         *
         * Throw an exception.
         */
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('CIDFont PDF objects could not be used as the operand of the text drawing operators');
    }

    /**
     * Convert string to the font encoding.
     *
     * @param string $string
     * @param string $charEncoding Character encoding of source text.
     * @return string
     * @throws Zend_Pdf_Exception
     *      */
    public function encodeString($string, $charEncoding)
    {
        /**
         * CIDFont object is not actually a font. It does not have an Encoding entry,
         * it cannot be listed in the Font subdictionary of a resource dictionary, and
         * it cannot be used as the operand of the Tf operator.
         *
         * Throw an exception.
         */
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('CIDFont PDF objects could not be used as the operand of the text drawing operators');
    }

    /**
     * Convert string from the font encoding.
     *
     * @param string $string
     * @param string $charEncoding Character encoding of resulting text.
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function decodeString($string, $charEncoding)
    {
        /**
         * CIDFont object is not actually a font. It does not have an Encoding entry,
         * it cannot be listed in the Font subdictionary of a resource dictionary, and
         * it cannot be used as the operand of the Tf operator.
         *
         * Throw an exception.
         */
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('CIDFont PDF objects could not be used as the operand of the text drawing operators');
    }
}
