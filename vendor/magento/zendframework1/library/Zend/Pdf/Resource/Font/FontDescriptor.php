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
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Numeric.php';

/** Zend_Pdf_Font */
#require_once 'Zend/Pdf/Font.php';


/**
 * FontDescriptor implementation
 *
 * A font descriptor specifies metrics and other attributes of a simple font or a
 * CIDFont as a whole, as distinct from the metrics of individual glyphs. These font
 * metrics provide information that enables a viewer application to synthesize a
 * substitute font or select a similar font when the font program is unavailable. The
 * font descriptor may also be used to embed the font program in the PDF file.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Font_FontDescriptor
{
    /**
     * Object constructor
     * @throws Zend_Pdf_Exception
     */
    public function __construct()
    {
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('Zend_Pdf_Resource_Font_FontDescriptor is not intended to be instantiated');
    }

    /**
     * Object constructor
     *
     * The $embeddingOptions parameter allows you to set certain flags related
     * to font embedding. You may combine options by OR-ing them together. See
     * the EMBED_ constants defined in {@link Zend_Pdf_Font} for the list of
     * available options and their descriptions.
     *
     * Note that it is not requried that fonts be embedded within the PDF file
     * to use them. If the recipient of the PDF has the font installed on their
     * computer, they will see the correct fonts in the document. If they don't,
     * the PDF viewer will substitute or synthesize a replacement.
     *
     *
     * @param Zend_Pdf_Resource_Font $font Font
     * @param Zend_Pdf_FileParser_Font_OpenType $fontParser Font parser object containing parsed TrueType file.
     * @param integer $embeddingOptions Options for font embedding.
     * @return Zend_Pdf_Element_Dictionary
     * @throws Zend_Pdf_Exception
     */
    static public function factory(Zend_Pdf_Resource_Font $font, Zend_Pdf_FileParser_Font_OpenType $fontParser, $embeddingOptions)
    {
        /* The font descriptor object contains the rest of the font metrics and
         * the information about the embedded font program (if applicible).
         */
        $fontDescriptor = new Zend_Pdf_Element_Dictionary();

        $fontDescriptor->Type     = new Zend_Pdf_Element_Name('FontDescriptor');
        $fontDescriptor->FontName = new Zend_Pdf_Element_Name($font->getResource()->BaseFont->value);

        /* The font flags value is a bitfield that describes the stylistic
         * attributes of the font. We will set as many of the bits as can be
         * determined from the font parser.
         */
        $flags = 0;
        if ($fontParser->isMonospaced) {    // bit 1: FixedPitch
            $flags |= 1 << 0;
        }
        if ($fontParser->isSerifFont) {    // bit 2: Serif
            $flags |= 1 << 1;
        }
        if (! $fontParser->isAdobeLatinSubset) {    // bit 3: Symbolic
            $flags |= 1 << 2;
        }
        if ($fontParser->isScriptFont) {    // bit 4: Script
            $flags |= 1 << 3;
        }
        if ($fontParser->isAdobeLatinSubset) {    // bit 6: Nonsymbolic
            $flags |= 1 << 5;
        }
        if ($fontParser->isItalic) {    // bit 7: Italic
            $flags |= 1 << 6;
        }
        // bits 17-19: AllCap, SmallCap, ForceBold; not available
        $fontDescriptor->Flags = new Zend_Pdf_Element_Numeric($flags);

        $fontBBox = array(new Zend_Pdf_Element_Numeric($font->toEmSpace($fontParser->xMin)),
                          new Zend_Pdf_Element_Numeric($font->toEmSpace($fontParser->yMin)),
                          new Zend_Pdf_Element_Numeric($font->toEmSpace($fontParser->xMax)),
                          new Zend_Pdf_Element_Numeric($font->toEmSpace($fontParser->yMax)));
        $fontDescriptor->FontBBox     = new Zend_Pdf_Element_Array($fontBBox);

        $fontDescriptor->ItalicAngle  = new Zend_Pdf_Element_Numeric($fontParser->italicAngle);

        $fontDescriptor->Ascent       = new Zend_Pdf_Element_Numeric($font->toEmSpace($fontParser->ascent));
        $fontDescriptor->Descent      = new Zend_Pdf_Element_Numeric($font->toEmSpace($fontParser->descent));

        $fontDescriptor->CapHeight    = new Zend_Pdf_Element_Numeric($fontParser->capitalHeight);
        /**
         * The vertical stem width is not yet extracted from the OpenType font
         * file. For now, record zero which is interpreted as 'unknown'.
         * @todo Calculate value for StemV.
         */
        $fontDescriptor->StemV        = new Zend_Pdf_Element_Numeric(0);

        $fontDescriptor->MissingWidth = new Zend_Pdf_Element_Numeric($fontParser->glyphWidths[0]);

        /* Set up font embedding. This is where the actual font program itself
         * is embedded within the PDF document.
         *
         * Note that it is not requried that fonts be embedded within the PDF
         * document to use them. If the recipient of the PDF has the font
         * installed on their computer, they will see the correct fonts in the
         * document. If they don't, the PDF viewer will substitute or synthesize
         * a replacement.
         *
         * There are several guidelines for font embedding:
         *
         * First, the developer might specifically request not to embed the font.
         */
        if (!($embeddingOptions & Zend_Pdf_Font::EMBED_DONT_EMBED)) {

            /* Second, the font author may have set copyright bits that prohibit
             * the font program from being embedded. Yes this is controversial,
             * but it's the rules:
             *   http://partners.adobe.com/public/developer/en/acrobat/sdk/FontPolicies.pdf
             *
             * To keep the developer in the loop, and to prevent surprising bug
             * reports of "your PDF doesn't have the right fonts," throw an
             * exception if the font cannot be embedded.
             */
            if (! $fontParser->isEmbeddable) {
                /* This exception may be suppressed if the developer decides that
                 * it's not a big deal that the font program can't be embedded.
                 */
                if (!($embeddingOptions & Zend_Pdf_Font::EMBED_SUPPRESS_EMBED_EXCEPTION)) {
                    $message = 'This font cannot be embedded in the PDF document. If you would like to use '
                             . 'it anyway, you must pass Zend_Pdf_Font::EMBED_SUPPRESS_EMBED_EXCEPTION '
                             . 'in the $options parameter of the font constructor.';
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception($message, Zend_Pdf_Exception::FONT_CANT_BE_EMBEDDED);
                }

            } else {
                /* Otherwise, the default behavior is to embed all custom fonts.
                 */
                /* This section will change soon to a stream object data
                 * provider model so that we don't have to keep a copy of the
                 * entire font in memory.
                 *
                 * We also cannot build font subsetting until the data provider
                 * model is in place.
                 */
                $fontFile = $fontParser->getDataSource()->readAllBytes();
                $fontFileObject = $font->getFactory()->newStreamObject($fontFile);
                $fontFileObject->dictionary->Length1 = new Zend_Pdf_Element_Numeric(strlen($fontFile));
                if (!($embeddingOptions & Zend_Pdf_Font::EMBED_DONT_COMPRESS)) {
                    /* Compress the font file using Flate. This generally cuts file
                     * sizes by about half!
                     */
                    $fontFileObject->dictionary->Filter = new Zend_Pdf_Element_Name('FlateDecode');
                }
                if ($fontParser instanceof Zend_Pdf_FileParser_Font_OpenType_Type1 /* not implemented now */) {
                    $fontDescriptor->FontFile  = $fontFileObject;
                } else if ($fontParser instanceof Zend_Pdf_FileParser_Font_OpenType_TrueType) {
                    $fontDescriptor->FontFile2 = $fontFileObject;
                } else {
                    $fontDescriptor->FontFile3 = $fontFileObject;
                }
            }
        }

        return $fontDescriptor;
    }
}
