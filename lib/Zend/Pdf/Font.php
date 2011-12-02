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
 * @version    $Id: Font.php 20211 2010-01-12 02:14:29Z yoshida@zend.co.jp $
 */


/**
 * Abstract factory class which vends {@link Zend_Pdf_Resource_Font} objects.
 *
 * Font objects themselves are normally instantiated through the factory methods
 * {@link fontWithName()} or {@link fontWithPath()}.
 *
 * This class is also the home for font-related constants because the name of
 * the true base class ({@link Zend_Pdf_Resource_Font}) is not intuitive for the
 * end user.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Font
{
  /**** Class Constants ****/


  /* Font Types */

    /**
     * Unknown font type.
     */
    const TYPE_UNKNOWN = 0;

    /**
     * One of the standard 14 PDF fonts.
     */
    const TYPE_STANDARD = 1;

    /**
     * A PostScript Type 1 font.
     */
    const TYPE_TYPE_1 = 2;

    /**
     * A TrueType font or an OpenType font containing TrueType outlines.
     */
    const TYPE_TRUETYPE = 3;

    /**
     * Type 0 composite font.
     */
    const TYPE_TYPE_0 = 4;

    /**
     * CID font containing a PostScript Type 1 font.
     * These fonts are used only to construct Type 0 composite fonts and can't be used directly
     */
    const TYPE_CIDFONT_TYPE_0 = 5;

    /**
     * CID font containing a TrueType font or an OpenType font containing TrueType outlines.
     * These fonts are used only to construct Type 0 composite fonts and can't be used directly
     */
    const TYPE_CIDFONT_TYPE_2 = 6;


  /* Names of the Standard 14 PDF Fonts */

    /**
     * Name of the standard PDF font Courier.
     */
    const FONT_COURIER = 'Courier';

    /**
     * Name of the bold style of the standard PDF font Courier.
     */
    const FONT_COURIER_BOLD = 'Courier-Bold';

    /**
     * Name of the italic style of the standard PDF font Courier.
     */
    const FONT_COURIER_OBLIQUE = 'Courier-Oblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_COURIER_OBLIQUE}.
     */
    const FONT_COURIER_ITALIC = 'Courier-Oblique';

    /**
     * Name of the bold and italic style of the standard PDF font Courier.
     */
    const FONT_COURIER_BOLD_OBLIQUE = 'Courier-BoldOblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_COURIER_BOLD_OBLIQUE}.
     */
    const FONT_COURIER_BOLD_ITALIC = 'Courier-BoldOblique';

    /**
     * Name of the standard PDF font Helvetica.
     */
    const FONT_HELVETICA = 'Helvetica';

    /**
     * Name of the bold style of the standard PDF font Helvetica.
     */
    const FONT_HELVETICA_BOLD = 'Helvetica-Bold';

    /**
     * Name of the italic style of the standard PDF font Helvetica.
     */
    const FONT_HELVETICA_OBLIQUE = 'Helvetica-Oblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_HELVETICA_OBLIQUE}.
     */
    const FONT_HELVETICA_ITALIC = 'Helvetica-Oblique';

    /**
     * Name of the bold and italic style of the standard PDF font Helvetica.
     */
    const FONT_HELVETICA_BOLD_OBLIQUE = 'Helvetica-BoldOblique';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_HELVETICA_BOLD_OBLIQUE}.
     */
    const FONT_HELVETICA_BOLD_ITALIC = 'Helvetica-BoldOblique';

    /**
     * Name of the standard PDF font Symbol.
     */
    const FONT_SYMBOL = 'Symbol';

    /**
     * Name of the standard PDF font Times.
     */
    const FONT_TIMES_ROMAN = 'Times-Roman';

    /**
     * Convenience constant for a common misspelling of
     * {@link FONT_TIMES_ROMAN}.
     */
    const FONT_TIMES = 'Times-Roman';

    /**
     * Name of the bold style of the standard PDF font Times.
     */
    const FONT_TIMES_BOLD = 'Times-Bold';

    /**
     * Name of the italic style of the standard PDF font Times.
     */
    const FONT_TIMES_ITALIC = 'Times-Italic';

    /**
     * Name of the bold and italic style of the standard PDF font Times.
     */
    const FONT_TIMES_BOLD_ITALIC = 'Times-BoldItalic';

    /**
     * Name of the standard PDF font Zapf Dingbats.
     */
    const FONT_ZAPFDINGBATS = 'ZapfDingbats';


  /* Font Name String Types */

    /**
     * Full copyright notice for the font.
     */
    const NAME_COPYRIGHT =  0;

    /**
     * Font family name. Used to group similar styles of fonts together.
     */
    const NAME_FAMILY =  1;

    /**
     * Font style within the font family. Examples: Regular, Italic, Bold, etc.
     */
    const NAME_STYLE =  2;

    /**
     * Unique font identifier.
     */
    const NAME_ID =  3;

    /**
     * Full font name. Usually a combination of the {@link NAME_FAMILY} and
     * {@link NAME_STYLE} strings.
     */
    const NAME_FULL =  4;

    /**
     * Version number of the font.
     */
    const NAME_VERSION =  5;

    /**
     * PostScript name for the font. This is the name used to identify fonts
     * internally and within the PDF file.
     */
    const NAME_POSTSCRIPT =  6;

    /**
     * Font trademark notice. This is distinct from the {@link NAME_COPYRIGHT}.
     */
    const NAME_TRADEMARK =  7;

    /**
     * Name of the font manufacturer.
     */
    const NAME_MANUFACTURER =  8;

    /**
     * Name of the designer of the font.
     */
    const NAME_DESIGNER =  9;

    /**
     * Description of the font. May contain revision information, usage
     * recommendations, features, etc.
     */
    const NAME_DESCRIPTION = 10;

    /**
     * URL of the font vendor. Some fonts may contain a unique serial number
     * embedded in this URL, which is used for licensing.
     */
    const NAME_VENDOR_URL = 11;

    /**
     * URL of the font designer ({@link NAME_DESIGNER}).
     */
    const NAME_DESIGNER_URL = 12;

    /**
     * Plain language licensing terms for the font.
     */
    const NAME_LICENSE = 13;

    /**
     * URL of more detailed licensing information for the font.
     */
    const NAME_LICENSE_URL = 14;

    /**
     * Preferred font family. Used by some fonts to work around a Microsoft
     * Windows limitation where only four fonts styles can share the same
     * {@link NAME_FAMILY} value.
     */
    const NAME_PREFERRED_FAMILY = 16;

    /**
     * Preferred font style. A more descriptive string than {@link NAME_STYLE}.
     */
    const NAME_PREFERRED_STYLE = 17;

    /**
     * Suggested text to use as a representative sample of the font.
     */
    const NAME_SAMPLE_TEXT = 19;

    /**
     * PostScript CID findfont name.
     */
    const NAME_CID_NAME = 20;


  /* Font Weights */

    /**
     * Thin font weight.
     */
    const WEIGHT_THIN = 100;

    /**
     * Extra-light (Ultra-light) font weight.
     */
    const WEIGHT_EXTRA_LIGHT = 200;

    /**
     * Light font weight.
     */
    const WEIGHT_LIGHT = 300;

    /**
     * Normal (Regular) font weight.
     */
    const WEIGHT_NORMAL = 400;

    /**
     * Medium font weight.
     */
    const WEIGHT_MEDIUM = 500;

    /**
     * Semi-bold (Demi-bold) font weight.
     */
    const WEIGHT_SEMI_BOLD = 600;

    /**
     * Bold font weight.
     */
    const WEIGHT_BOLD = 700;

    /**
     * Extra-bold (Ultra-bold) font weight.
     */
    const WEIGHT_EXTRA_BOLD = 800;

    /**
     * Black (Heavy) font weight.
     */
    const WEIGHT_BLACK = 900;


  /* Font Widths */

    /**
     * Ultra-condensed font width. Typically 50% of normal.
     */
    const WIDTH_ULTRA_CONDENSED = 1;

    /**
     * Extra-condensed font width. Typically 62.5% of normal.
     */
    const WIDTH_EXTRA_CONDENSED = 2;

    /**
     * Condensed font width. Typically 75% of normal.
     */
    const WIDTH_CONDENSED = 3;

    /**
     * Semi-condensed font width. Typically 87.5% of normal.
     */
    const WIDTH_SEMI_CONDENSED = 4;

    /**
     * Normal (Medium) font width.
     */
    const WIDTH_NORMAL = 5;

    /**
     * Semi-expanded font width. Typically 112.5% of normal.
     */
    const WIDTH_SEMI_EXPANDED = 6;

    /**
     * Expanded font width. Typically 125% of normal.
     */
    const WIDTH_EXPANDED = 7;

    /**
     * Extra-expanded font width. Typically 150% of normal.
     */
    const WIDTH_EXTRA_EXPANDED = 8;

    /**
     * Ultra-expanded font width. Typically 200% of normal.
     */
    const WIDTH_ULTRA_EXPANDED = 9;


  /* Font Embedding Options */

    /**
     * Do not embed the font in the PDF document.
     */
    const EMBED_DONT_EMBED = 0x01;

    /**
     * Embed, but do not subset the font in the PDF document.
     */
    const EMBED_DONT_SUBSET = 0x02;

    /**
     * Embed, but do not compress the font in the PDF document.
     */
    const EMBED_DONT_COMPRESS = 0x04;

    /**
     * Suppress the exception normally thrown if the font cannot be embedded
     * due to its copyright bits being set.
     */
    const EMBED_SUPPRESS_EMBED_EXCEPTION = 0x08;



  /**** Class Variables ****/


    /**
     * Array whose keys are the unique PostScript names of instantiated fonts.
     * The values are the font objects themselves.
     * @var array
     */
    private static $_fontNames = array();

    /**
     * Array whose keys are the md5 hash of the full paths on disk for parsed
     * fonts. The values are the font objects themselves.
     * @var array
     */
    private static $_fontFilePaths = array();



  /**** Public Interface ****/


  /* Factory Methods */

    /**
     * Returns a {@link Zend_Pdf_Resource_Font} object by full name.
     *
     * This is the preferred method to obtain one of the standard 14 PDF fonts.
     *
     * The result of this method is cached, preventing unnecessary duplication
     * of font objects. Repetitive calls for a font with the same name will
     * return the same object.
     *
     * The $embeddingOptions parameter allows you to set certain flags related
     * to font embedding. You may combine options by OR-ing them together. See
     * the EMBED_ constants defined in {@link Zend_Pdf_Font} for the list of
     * available options and their descriptions. Note that this value is only
     * used when creating a font for the first time. If a font with the same
     * name already exists, you will get that object and the options you specify
     * here will be ignored. This is because fonts are only embedded within the
     * PDF file once.
     *
     * If the font name supplied does not match the name of a previously
     * instantiated object and it is not one of the 14 standard PDF fonts, an
     * exception will be thrown.
     *
     * @param string $name Full PostScript name of font.
     * @param integer $embeddingOptions (optional) Options for font embedding.
     * @return Zend_Pdf_Resource_Font
     * @throws Zend_Pdf_Exception
     */
    public static function fontWithName($name, $embeddingOptions = 0)
        {
        /* First check the cache. Don't duplicate font objects.
         */
        if (isset(Zend_Pdf_Font::$_fontNames[$name])) {
            return Zend_Pdf_Font::$_fontNames[$name];
        }

        /**
         * @todo It would be cool to be able to have a mapping of font names to
         *   file paths in a configuration file for frequently used custom
         *   fonts. This would allow a user to use custom fonts without having
         *   to hard-code file paths all over the place. Table this idea until
         *   {@link Zend_Config} is ready.
         */

        /* Not an existing font and no mapping in the config file. Check to see
         * if this is one of the standard 14 PDF fonts.
         */
        switch ($name) {
            case Zend_Pdf_Font::FONT_COURIER:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/Courier.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_Courier();
                break;

            case Zend_Pdf_Font::FONT_COURIER_BOLD:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/CourierBold.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_CourierBold();
                break;

            case Zend_Pdf_Font::FONT_COURIER_OBLIQUE:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/CourierOblique.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_CourierOblique();
                break;

            case Zend_Pdf_Font::FONT_COURIER_BOLD_OBLIQUE:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/CourierBoldOblique.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_CourierBoldOblique();
                break;

            case Zend_Pdf_Font::FONT_HELVETICA:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/Helvetica.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_Helvetica();
                break;

            case Zend_Pdf_Font::FONT_HELVETICA_BOLD:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/HelveticaBold.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_HelveticaBold();
                break;

            case Zend_Pdf_Font::FONT_HELVETICA_OBLIQUE:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/HelveticaOblique.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_HelveticaOblique();
                break;

            case Zend_Pdf_Font::FONT_HELVETICA_BOLD_OBLIQUE:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/HelveticaBoldOblique.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_HelveticaBoldOblique();
                break;

            case Zend_Pdf_Font::FONT_SYMBOL:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/Symbol.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_Symbol();
                break;

            case Zend_Pdf_Font::FONT_TIMES_ROMAN:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/TimesRoman.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_TimesRoman();
                break;

            case Zend_Pdf_Font::FONT_TIMES_BOLD:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/TimesBold.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_TimesBold();
                break;

            case Zend_Pdf_Font::FONT_TIMES_ITALIC:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/TimesItalic.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_TimesItalic();
                break;

            case Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/TimesBoldItalic.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_TimesBoldItalic();
                break;

            case Zend_Pdf_Font::FONT_ZAPFDINGBATS:
                #require_once 'Zend/Pdf/Resource/Font/Simple/Standard/ZapfDingbats.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Standard_ZapfDingbats();
                break;

            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception("Unknown font name: $name",
                                             Zend_Pdf_Exception::BAD_FONT_NAME);
        }

        /* Add this new font to the cache array and return it for use.
         */
        Zend_Pdf_Font::$_fontNames[$name] = $font;
        return $font;
    }

    /**
     * Returns a {@link Zend_Pdf_Resource_Font} object by file path.
     *
     * The result of this method is cached, preventing unnecessary duplication
     * of font objects. Repetitive calls for the font with the same path will
     * return the same object.
     *
     * The $embeddingOptions parameter allows you to set certain flags related
     * to font embedding. You may combine options by OR-ing them together. See
     * the EMBED_ constants defined in {@link Zend_Pdf_Font} for the list of
     * available options and their descriptions. Note that this value is only
     * used when creating a font for the first time. If a font with the same
     * name already exists, you will get that object and the options you specify
     * here will be ignored. This is because fonts are only embedded within the
     * PDF file once.
     *
     * If the file path supplied does not match the path of a previously
     * instantiated object or the font type cannot be determined, an exception
     * will be thrown.
     *
     * @param string $filePath Full path to the font file.
     * @param integer $embeddingOptions (optional) Options for font embedding.
     * @return Zend_Pdf_Resource_Font
     * @throws Zend_Pdf_Exception
     */
    public static function fontWithPath($filePath, $embeddingOptions = 0)
    {
        /* First check the cache. Don't duplicate font objects.
         */
        $filePathKey = md5($filePath);
        if (isset(Zend_Pdf_Font::$_fontFilePaths[$filePathKey])) {
            return Zend_Pdf_Font::$_fontFilePaths[$filePathKey];
        }

        /* Create a file parser data source object for this file. File path and
         * access permission checks are handled here.
         */
        #require_once 'Zend/Pdf/FileParserDataSource/File.php';
        $dataSource = new Zend_Pdf_FileParserDataSource_File($filePath);

        /* Attempt to determine the type of font. We can't always trust file
         * extensions, but try that first since it's fastest.
         */
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        /* If it turns out that the file is named improperly and we guess the
         * wrong type, we'll get null instead of a font object.
         */
        switch ($fileExtension) {
            case 'ttf':
                $font = Zend_Pdf_Font::_extractTrueTypeFont($dataSource, $embeddingOptions);
                break;

            default:
                /* Unrecognized extension. Try to determine the type by actually
                 * parsing it below.
                 */
                $font = null;
                break;
        }


        if ($font === null) {
            /* There was no match for the file extension or the extension was
             * wrong. Attempt to detect the type of font by actually parsing it.
             * We'll do the checks in order of most likely format to try to
             * reduce the detection time.
             */

            // OpenType

            // TrueType
            if (($font === null) && ($fileExtension != 'ttf')) {
                $font = Zend_Pdf_Font::_extractTrueTypeFont($dataSource, $embeddingOptions);
            }

            // Type 1 PostScript

            // Mac OS X dfont

            // others?
        }


        /* Done with the data source object.
         */
        $dataSource = null;

        if ($font !== null) {
            /* Parsing was successful. Add this font instance to the cache arrays
             * and return it for use.
             */
            $fontName = $font->getFontName(Zend_Pdf_Font::NAME_POSTSCRIPT, '', '');
            Zend_Pdf_Font::$_fontNames[$fontName] = $font;
            $filePathKey = md5($filePath);
            Zend_Pdf_Font::$_fontFilePaths[$filePathKey] = $font;
            return $font;

        } else {
            /* The type of font could not be determined. Give up.
             */
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Cannot determine font type: $filePath",
                                         Zend_Pdf_Exception::CANT_DETERMINE_FONT_TYPE);
         }

    }



  /**** Internal Methods ****/


  /* Font Extraction Methods */

    /**
     * Attempts to extract a TrueType font from the data source.
     *
     * If the font parser throws an exception that suggests the data source
     * simply doesn't contain a TrueType font, catches it and returns null. If
     * an exception is thrown that suggests the TrueType font is corrupt or
     * otherwise unusable, throws that exception. If successful, returns the
     * font object.
     *
     * @param Zend_Pdf_FileParserDataSource $dataSource
     * @param integer $embeddingOptions Options for font embedding.
     * @return Zend_Pdf_Resource_Font_OpenType_TrueType May also return null if
     *   the data source does not appear to contain a TrueType font.
     * @throws Zend_Pdf_Exception
     */
    protected static function _extractTrueTypeFont($dataSource, $embeddingOptions)
    {
        try {
            #require_once 'Zend/Pdf/FileParser/Font/OpenType/TrueType.php';
            $fontParser = new Zend_Pdf_FileParser_Font_OpenType_TrueType($dataSource);

            $fontParser->parse();
            if ($fontParser->isAdobeLatinSubset) {
                #require_once 'Zend/Pdf/Resource/Font/Simple/Parsed/TrueType.php';
                $font = new Zend_Pdf_Resource_Font_Simple_Parsed_TrueType($fontParser, $embeddingOptions);
            } else {
                #require_once 'Zend/Pdf/Resource/Font/CidFont/TrueType.php';
                #require_once 'Zend/Pdf/Resource/Font/Type0.php';
                /* Use Composite Type 0 font which supports Unicode character mapping */
                $cidFont = new Zend_Pdf_Resource_Font_CidFont_TrueType($fontParser, $embeddingOptions);
                $font    = new Zend_Pdf_Resource_Font_Type0($cidFont);
            }
        } catch (Zend_Pdf_Exception $e) {
            /* The following exception codes suggest that this isn't really a
             * TrueType font. If we caught such an exception, simply return
             * null. For all other cases, it probably is a TrueType font but has
             * a problem; throw the exception again.
             */
            $fontParser = null;
            #require_once 'Zend/Pdf/Exception.php';
            switch ($e->getCode()) {
                case Zend_Pdf_Exception::WRONG_FONT_TYPE:    // break intentionally omitted
                case Zend_Pdf_Exception::BAD_TABLE_COUNT:    // break intentionally omitted
                case Zend_Pdf_Exception::BAD_MAGIC_NUMBER:
                    return null;

                default:
                    throw new Zend_Pdf_Exception($e->getMessage(), $e->getCode(), $e);
            }
        }
        return $font;
    }
}
