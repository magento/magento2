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
 * @version    $Id: TrueType.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Pdf_FileParser_Font_OpenType */
#require_once 'Zend/Pdf/FileParser/Font/OpenType.php';

/**
 * Parses an OpenType font file containing TrueType outlines.
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_FileParser_Font_OpenType_TrueType extends Zend_Pdf_FileParser_Font_OpenType
{
  /**** Public Interface ****/


  /* Concrete Class Implementation */

    /**
     * Verifies that the font file actually contains TrueType outlines.
     *
     * @throws Zend_Pdf_Exception
     */
    public function screen()
    {
        if ($this->_isScreened) {
            return;
        }

        parent::screen();

        switch ($this->_readScalerType()) {
            case 0x00010000:    // version 1.0 - Windows TrueType signature
                break;

            case 0x74727565:    // 'true' - Macintosh TrueType signature
                break;

            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Not a TrueType font file',
                                             Zend_Pdf_Exception::WRONG_FONT_TYPE);
        }

        $this->fontType = Zend_Pdf_Font::TYPE_TRUETYPE;
        $this->_isScreened = true;
    }

    /**
     * Reads and parses the TrueType font data from the file on disk.
     *
     * @throws Zend_Pdf_Exception
     */
    public function parse()
    {
        if ($this->_isParsed) {
            return;
        }

        parent::parse();

        /* There is nothing additional to parse for TrueType fonts at this time.
         */

        $this->_isParsed = true;
    }
}
