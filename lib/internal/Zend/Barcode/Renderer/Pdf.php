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
 * @package    Zend_Barcode
 * @subpackage Renderer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Pdf.php 22418 2010-06-11 16:27:22Z mikaelkael $
 */

/** @see Zend_Barcode_Renderer_RendererAbstract */
#require_once 'Zend/Barcode/Renderer/RendererAbstract.php';

/** @see Zend_Pdf */
#require_once 'Zend/Pdf.php';

/** @see Zend_Pdf_Page */
#require_once 'Zend/Pdf/Page.php';

/** @see Zend_Pdf_Color_Rgb */
#require_once 'Zend/Pdf/Color/Rgb.php';

/**
 * Class for rendering the barcode in PDF resource
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Renderer_Pdf extends Zend_Barcode_Renderer_RendererAbstract
{
    /**
     * PDF resource
     * @var Zend_Pdf
     */
    protected $_resource = null;

    /**
     * Page number in PDF resource
     * @var integer
     */
    protected $_page = 0;

    /**
     * Module size rendering
     * @var float
     */
    protected $_moduleSize = 0.5;

    /**
     * Set an image resource to draw the barcode inside
     * @param resource $value
     * @return Zend_Barcode_Renderer
     * @throw Zend_Barcode_Renderer_Exception
     */
    public function setResource($pdf, $page = 0)
    {
        if (!$pdf instanceof Zend_Pdf) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Invalid Zend_Pdf resource provided to setResource()'
            );
        }

        $this->_resource = $pdf;
        $this->_page     = intval($page);

        if (!count($this->_resource->pages)) {
            $this->_page = 0;
            $this->_resource->pages[] = new Zend_Pdf_Page(
                Zend_Pdf_Page::SIZE_A4
            );
        }
        return $this;
    }

    /**
     * Check renderer parameters
     *
     * @return void
     */
    protected function _checkParams()
    {
    }

    /**
     * Draw the barcode in the PDF, send headers and the PDF
     * @return mixed
     */
    public function render()
    {
        $this->draw();
        header("Content-Type: application/pdf");
        echo $this->_resource->render();
    }

    /**
     * Initialize the PDF resource
     * @return void
     */
    protected function _initRenderer()
    {
        if ($this->_resource === null) {
            $this->_resource = new Zend_Pdf();
            $this->_resource->pages[] = new Zend_Pdf_Page(
                Zend_Pdf_Page::SIZE_A4
            );
        }

        $pdfPage = $this->_resource->pages[$this->_page];
        $this->_adjustPosition($pdfPage->getHeight(), $pdfPage->getWidth());
    }

    /**
     * Draw a polygon in the rendering resource
     * @param array $points
     * @param integer $color
     * @param boolean $filled
     */
    protected function _drawPolygon($points, $color, $filled = true)
    {
        $page = $this->_resource->pages[$this->_page];
        foreach ($points as $point) {
            $x[] = $point[0] * $this->_moduleSize + $this->_leftOffset;
            $y[] = $page->getHeight() - $point[1] * $this->_moduleSize - $this->_topOffset;
        }
        if (count($y) == 4) {
            if ($x[0] != $x[3] && $y[0] == $y[3]) {
                $y[0] -= ($this->_moduleSize / 2);
                $y[3] -= ($this->_moduleSize / 2);
            }
            if ($x[1] != $x[2] && $y[1] == $y[2]) {
                $y[1] += ($this->_moduleSize / 2);
                $y[2] += ($this->_moduleSize / 2);
            }
        }

        $color = new Zend_Pdf_Color_Rgb(
            (($color & 0xFF0000) >> 16) / 255.0,
            (($color & 0x00FF00) >> 8) / 255.0,
            ($color & 0x0000FF) / 255.0
        );

        $page->setLineColor($color);
        $page->setFillColor($color);
        $page->setLineWidth($this->_moduleSize);

        $fillType = ($filled)
                  ? Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE
                  : Zend_Pdf_Page::SHAPE_DRAW_STROKE;

        $page->drawPolygon($x, $y, $fillType);
    }

    /**
     * Draw a text in the rendering resource
     * @param string $text
     * @param float $size
     * @param array $position
     * @param string $font
     * @param integer $color
     * @param string $alignment
     * @param float $orientation
     */
    protected function _drawText(
        $text,
        $size,
        $position,
        $font,
        $color,
        $alignment = 'center',
        $orientation = 0
    ) {
        $page  = $this->_resource->pages[$this->_page];
        $color = new Zend_Pdf_Color_Rgb(
            (($color & 0xFF0000) >> 16) / 255.0,
            (($color & 0x00FF00) >> 8) / 255.0,
            ($color & 0x0000FF) / 255.0
        );

        $page->setLineColor($color);
        $page->setFillColor($color);
        $page->setFont(Zend_Pdf_Font::fontWithPath($font), $size * $this->_moduleSize * 1.2);

        $width = $this->widthForStringUsingFontSize(
            $text,
            Zend_Pdf_Font::fontWithPath($font),
            $size * $this->_moduleSize
        );

        $angle = pi() * $orientation / 180;
        $left = $position[0] * $this->_moduleSize + $this->_leftOffset;
        $top  = $page->getHeight() - $position[1] * $this->_moduleSize - $this->_topOffset;

        switch ($alignment) {
            case 'center':
                $left -= ($width / 2) * cos($angle);
                $top  -= ($width / 2) * sin($angle);
                break;
            case 'right':
                $left -= $width;
                break;
        }
        $page->rotate($left, $top, $angle);
        $page->drawText($text, $left, $top);
        $page->rotate($left, $top, - $angle);
    }

    /**
     * Calculate the width of a string:
     * in case of using alignment parameter in drawText
     * @param string $text
     * @param Zend_Pdf_Font $font
     * @param float $fontSize
     * @return float
     */
    public function widthForStringUsingFontSize($text, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $text);
        $characters    = array();
        for ($i = 0; $i < strlen($drawingString); $i ++) {
            $characters[] = (ord($drawingString[$i ++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }
}
