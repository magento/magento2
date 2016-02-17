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
#require_once 'Zend/Pdf/Element/Numeric.php';


/** Zend_Pdf_Resource_Font_Simple */
#require_once 'Zend/Pdf/Resource/Font/Simple.php';

/**
 * Parsed and (optionaly) embedded fonts implementation
 *
 * OpenType fonts can contain either TrueType or PostScript Type 1 outlines.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Resource_Font_Simple_Parsed extends Zend_Pdf_Resource_Font_Simple
{
    /**
     * Object constructor
     *
     * @param Zend_Pdf_FileParser_Font_OpenType $fontParser Font parser object containing OpenType file.
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

        $this->_glyphWidths       = $fontParser->glyphWidths;
        $this->_missingGlyphWidth = $this->_glyphWidths[0];


        $this->_cmap = $fontParser->cmap;


        /* Resource dictionary */

        $baseFont = $this->getFontName(Zend_Pdf_Font::NAME_POSTSCRIPT, 'en', 'UTF-8');
        $this->_resource->BaseFont = new Zend_Pdf_Element_Name($baseFont);

        $this->_resource->FirstChar = new Zend_Pdf_Element_Numeric(0);
        $this->_resource->LastChar  = new Zend_Pdf_Element_Numeric(count($this->_glyphWidths) - 1);

        /* Now convert the scalar glyph widths to Zend_Pdf_Element_Numeric objects.
         */
        $pdfWidths = array();
        foreach ($this->_glyphWidths as $width) {
            $pdfWidths[] = new Zend_Pdf_Element_Numeric($this->toEmSpace($width));
        }
        /* Create the Zend_Pdf_Element_Array object and add it to the font's
         * object factory and resource dictionary.
         */
        $widthsArrayElement = new Zend_Pdf_Element_Array($pdfWidths);
        $widthsObject = $this->_objectFactory->newObject($widthsArrayElement);
        $this->_resource->Widths = $widthsObject;
    }
}
