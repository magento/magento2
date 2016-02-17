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

/** Zend_Pdf_Cmap_ByteEncoding */
#require_once 'Zend/Pdf/Cmap/ByteEncoding.php';


/**
 * Custom cmap type used for the Adobe Standard 14 PDF fonts.
 *
 * Just like {@link Zend_Pdf_Cmap_ByteEncoding} except that the constructor
 * takes a predefined array of glyph numbers and can cover any Unicode character.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Cmap_ByteEncoding_Static extends Zend_Pdf_Cmap_ByteEncoding
{
  /**** Public Interface ****/


  /* Object Lifecycle */

    /**
     * Object constructor
     *
     * @param array $cmapData Array whose keys are Unicode character codes and
     *   values are glyph numbers.
     * @throws Zend_Pdf_Exception
     */
    public function __construct($cmapData)
    {
        if (! is_array($cmapData)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Constructor parameter must be an array',
                                         Zend_Pdf_Exception::BAD_PARAMETER_TYPE);
        }
        $this->_glyphIndexArray = $cmapData;
    }

}
