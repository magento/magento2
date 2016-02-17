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
#require_once 'Zend/Pdf/Element/Name.php';


/** Zend_Pdf_Resource_Font_Simple */
#require_once 'Zend/Pdf/Resource/Font/Simple.php';

/**
 * Abstract class definition for the standard 14 Type 1 PDF fonts.
 *
 * The standard 14 PDF fonts are guaranteed to be availble in any PDF viewer
 * implementation. As such, they do not require much data for the font's
 * resource dictionary. The majority of the data provided by subclasses is for
 * the benefit of our own layout code.
 *
 * The standard fonts and the corresponding subclasses that manage them:
 * <ul>
 *  <li>Courier - {@link Zend_Pdf_Resource_Font_Simple_Standard_Courier}
 *  <li>Courier-Bold - {@link Zend_Pdf_Resource_Font_Simple_Standard_CourierBold}
 *  <li>Courier-Oblique - {@link Zend_Pdf_Resource_Font_Simple_Standard_CourierOblique}
 *  <li>Courier-BoldOblique - {@link Zend_Pdf_Resource_Font_Simple_Standard_CourierBoldOblique}
 *  <li>Helvetica - {@link Zend_Pdf_Resource_Font_Simple_Standard_Helvetica}
 *  <li>Helvetica-Bold - {@link Zend_Pdf_Resource_Font_Simple_Standard_HelveticaBold}
 *  <li>Helvetica-Oblique - {@link Zend_Pdf_Resource_Font_Simple_Standard_HelveticaOblique}
 *  <li>Helvetica-BoldOblique - {@link Zend_Pdf_Resource_Font_Simple_Standard_HelveticaBoldOblique}
 *  <li>Symbol - {@link Zend_Pdf_Resource_Font_Simple_Standard_Symbol}
 *  <li>Times - {@link Zend_Pdf_Resource_Font_Simple_Standard_Times}
 *  <li>Times-Bold - {@link Zend_Pdf_Resource_Font_Simple_Standard_TimesBold}
 *  <li>Times-Italic - {@link Zend_Pdf_Resource_Font_Simple_Standard_TimesItalic}
 *  <li>Times-BoldItalic - {@link Zend_Pdf_Resource_Font_Simple_Standard_TimesBoldItalic}
 *  <li>ZapfDingbats - {@link Zend_Pdf_Resource_Font_Simple_Standard_ZapfDingbats}
 * </ul>
 *
 * Font objects should be normally be obtained from the factory methods
 * {@link Zend_Pdf_Font::fontWithName} and {@link Zend_Pdf_Font::fontWithPath}.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Resource_Font_Simple_Standard extends Zend_Pdf_Resource_Font_Simple
{
  /**** Public Interface ****/


  /* Object Lifecycle */

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->_fontType = Zend_Pdf_Font::TYPE_STANDARD;

        parent::__construct();
        $this->_resource->Subtype  = new Zend_Pdf_Element_Name('Type1');
    }
}
