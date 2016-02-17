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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Style object.
 * Style object doesn't directly correspond to any PDF file object.
 * It's utility class, used as a container for style information.
 * It's used by Zend_Pdf_Page class in draw operations.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Style
{
    /**
     * Fill color.
     * Used to fill geometric shapes or text.
     *
     * @var Zend_Pdf_Color|null
     */
    private $_fillColor = null;

    /**
     * Line color.
     * Current color, used for lines and font outlines.
     *
     * @var Zend_Pdf_Color|null
     */

    private $_color;

    /**
     * Line width.
     *
     * @var Zend_Pdf_Element_Numeric
     */
    private $_lineWidth;

    /**
     * Array which describes line dashing pattern.
     * It's array of numeric:
     * array($on_length, $off_length, $on_length, $off_length, ...)
     *
     * @var array
     */
    private $_lineDashingPattern;

    /**
     * Line dashing phase
     *
     * @var float
     */
    private $_lineDashingPhase;

    /**
     * Current font
     *
     * @var Zend_Pdf_Resource_Font
     */
    private $_font;

    /**
     * Font size
     *
     * @var float
     */
    private $_fontSize;



    /**
     * Create style.
     *
     * @param Zend_Pdf_Style $anotherStyle
     */
    public function __construct($anotherStyle = null)
    {
        if ($anotherStyle !== null) {
            $this->_fillColor          = $anotherStyle->_fillColor;
            $this->_color              = $anotherStyle->_color;
            $this->_lineWidth          = $anotherStyle->_lineWidth;
            $this->_lineDashingPattern = $anotherStyle->_lineDashingPattern;
            $this->_lineDashingPhase   = $anotherStyle->_lineDashingPhase;
            $this->_font               = $anotherStyle->_font;
            $this->_fontSize           = $anotherStyle->_fontSize;
        }
    }


    /**
     * Set fill color.
     *
     * @param Zend_Pdf_Color $color
     */
    public function setFillColor(Zend_Pdf_Color $color)
    {
        $this->_fillColor = $color;
    }

    /**
     * Set line color.
     *
     * @param Zend_Pdf_Color $color
     */
    public function setLineColor(Zend_Pdf_Color $color)
    {
        $this->_color = $color;
    }

    /**
     * Set line width.
     *
     * @param float $width
     */
    public function setLineWidth($width)
    {
        #require_once 'Zend/Pdf/Element/Numeric.php';
        $this->_lineWidth = new Zend_Pdf_Element_Numeric($width);
    }


    /**
     * Set line dashing pattern
     *
     * @param array $pattern
     * @param float $phase
     */
    public function setLineDashingPattern($pattern, $phase = 0)
    {
        #require_once 'Zend/Pdf/Page.php';
        if ($pattern === Zend_Pdf_Page::LINE_DASHING_SOLID) {
            $pattern = array();
            $phase   = 0;
        }

        #require_once 'Zend/Pdf/Element/Numeric.php';
        $this->_lineDashingPattern = $pattern;
        $this->_lineDashingPhase   = new Zend_Pdf_Element_Numeric($phase);
    }


    /**
     * Set current font.
     *
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize
     */
    public function setFont(Zend_Pdf_Resource_Font $font, $fontSize)
    {
        $this->_font = $font;
        $this->_fontSize = $fontSize;
    }

    /**
     * Modify current font size
     *
     * @param float $fontSize
     */
    public function setFontSize($fontSize)
    {
        $this->_fontSize = $fontSize;
    }

    /**
     * Get fill color.
     *
     * @return Zend_Pdf_Color|null
     */
    public function getFillColor()
    {
        return $this->_fillColor;
    }

    /**
     * Get line color.
     *
     * @return Zend_Pdf_Color|null
     */
    public function getLineColor()
    {
        return $this->_color;
    }

    /**
     * Get line width.
     *
     * @return float
     */
    public function getLineWidth()
    {
        return $this->_lineWidth->value;
    }

    /**
     * Get line dashing pattern
     *
     * @return array
     */
    public function getLineDashingPattern()
    {
        return $this->_lineDashingPattern;
    }


    /**
     * Get current font.
     *
     * @return Zend_Pdf_Resource_Font $font
     */
    public function getFont()
    {
        return $this->_font;
    }

    /**
     * Get current font size
     *
     * @return float $fontSize
     */
    public function getFontSize()
    {
        return $this->_fontSize;
    }

    /**
     * Get line dashing phase
     *
     * @return float
     */
    public function getLineDashingPhase()
    {
        return $this->_lineDashingPhase->value;
    }


    /**
     * Dump style to a string, which can be directly inserted into content stream
     *
     * @return string
     */
    public function instructions()
    {
        $instructions = '';

        if ($this->_fillColor !== null) {
            $instructions .= $this->_fillColor->instructions(false);
        }

        if ($this->_color !== null) {
            $instructions .= $this->_color->instructions(true);
        }

        if ($this->_lineWidth !== null) {
            $instructions .= $this->_lineWidth->toString() . " w\n";
        }

        if ($this->_lineDashingPattern !== null) {
            #require_once 'Zend/Pdf/Element/Array.php';
            $dashPattern = new Zend_Pdf_Element_Array();

            #require_once 'Zend/Pdf/Element/Numeric.php';
            foreach ($this->_lineDashingPattern as $dashItem) {
                $dashElement = new Zend_Pdf_Element_Numeric($dashItem);
                $dashPattern->items[] = $dashElement;
            }

            $instructions .= $dashPattern->toString() . ' '
                           . $this->_lineDashingPhase->toString() . " d\n";
        }

        return $instructions;
    }

}
