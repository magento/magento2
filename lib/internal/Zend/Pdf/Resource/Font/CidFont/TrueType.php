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
 * @version    $Id: TrueType.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Internally used classes */

#require_once 'Zend/Pdf/Element/Name.php';

/** Zend_Pdf_Resource_Font_FontDescriptor */
#require_once 'Zend/Pdf/Resource/Font/FontDescriptor.php';


/** Zend_Pdf_Resource_Font_CidFont */
#require_once 'Zend/Pdf/Resource/Font/CidFont.php';

/**
 * Type 2 CIDFonts implementation
 *
 * For Type 2, the CIDFont program is actually a TrueType font program, which has
 * no native notion of CIDs. In a TrueType font program, glyph descriptions are
 * identified by glyph index values. Glyph indices are internal to the font and are not
 * defined consistently from one font to another. Instead, a TrueType font program
 * contains a 'cmap' table that provides mappings directly from character codes to
 * glyph indices for one or more predefined encodings.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Font_CidFont_TrueType extends Zend_Pdf_Resource_Font_CidFont
{
    /**
     * Object constructor
     *
     * @todo Joing this class with Zend_Pdf_Resource_Font_Simple_Parsed_TrueType
     *
     * @param Zend_Pdf_FileParser_Font_OpenType_TrueType $fontParser Font parser
     *   object containing parsed TrueType file.
     * @param integer $embeddingOptions Options for font embedding.
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_FileParser_Font_OpenType_TrueType $fontParser, $embeddingOptions)
    {
        parent::__construct($fontParser, $embeddingOptions);

        $this->_fontType = Zend_Pdf_Font::TYPE_CIDFONT_TYPE_2;

        $this->_resource->Subtype  = new Zend_Pdf_Element_Name('CIDFontType2');

        $fontDescriptor = Zend_Pdf_Resource_Font_FontDescriptor::factory($this, $fontParser, $embeddingOptions);
        $this->_resource->FontDescriptor = $this->_objectFactory->newObject($fontDescriptor);

        /* Prepare CIDToGIDMap */
        // Initialize 128K string of null characters (65536 2 byte integers)
        $cidToGidMapData = str_repeat("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", 8192);
        // Fill the index
        $charGlyphs = $this->_cmap->getCoveredCharactersGlyphs();
        foreach ($charGlyphs as $charCode => $glyph) {
            $cidToGidMapData[$charCode*2    ] = chr($glyph >> 8);
            $cidToGidMapData[$charCode*2 + 1] = chr($glyph & 0xFF);
        }
        // Store CIDToGIDMap within compressed stream object
        $cidToGidMap = $this->_objectFactory->newStreamObject($cidToGidMapData);
        $cidToGidMap->dictionary->Filter = new Zend_Pdf_Element_Name('FlateDecode');
        $this->_resource->CIDToGIDMap = $cidToGidMap;
    }

}
