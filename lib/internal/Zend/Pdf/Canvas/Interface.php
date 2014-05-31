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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Style.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Canvas is an abstract rectangle drawing area which can be dropped into
 * page object at specified place.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Pdf_Canvas_Interface
{
    /**
     * Returns dictionaries of used resources.
     *
     * Used for canvas implementations interoperability
     *
     * Structure of the returned array:
     * array(
     *   <resTypeName> => array(
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      ...
     *                    ),
     *   <resTypeName> => array(
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      ...
     *                    ),
     *   ...
     *   'ProcSet' => array()
     * )
     *
     * where ProcSet array is a list of used procedure sets names (strings).
     * Allowed procedure set names: 'PDF', 'Text', 'ImageB', 'ImageC', 'ImageI'
     *
     * @internal
     * @return array
     */
    public function getResources();

    /**
     * Get drawing instructions stream
     *
     * It has to be returned as a PDF stream object to make it reusable.
     *
     * @internal
     * @returns Zend_Pdf_Resource_ContentStream
     */
    public function getContents();

    /**
     * Return canvas height.
     *
     * @return float
     */
    public function getHeight();

    /**
     * Return canvas width.
     *
     * @return float
     */
    public function getWidth();

    /**
     * Draw a canvas at the specified location
     *
     * If upper right corner is not specified then canvas heght and width
     * are used.
     *
     * @param Zend_Pdf_Canvas_Interface $canvas
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawCanvas(Zend_Pdf_Canvas_Interface $canvas, $x1, $y1, $x2 = null, $y2 = null);

    /**
     * Set fill color.
     *
     * @param Zend_Pdf_Color $color
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setFillColor(Zend_Pdf_Color $color);

    /**
     * Set line color.
     *
     * @param Zend_Pdf_Color $color
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setLineColor(Zend_Pdf_Color $color);

    /**
     * Set line width.
     *
     * @param float $width
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setLineWidth($width);

    /**
     * Set line dashing pattern
     *
     * Pattern is an array of floats: array(on_length, off_length, on_length, off_length, ...)
     * or Zend_Pdf_Page::LINE_DASHING_SOLID constant
     * Phase is shift from the beginning of line.
     *
     * @param mixed $pattern
     * @param array $phase
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setLineDashingPattern($pattern, $phase = 0);

    /**
     * Set current font.
     *
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setFont(Zend_Pdf_Resource_Font $font, $fontSize);

    /**
     * Set the style to use for future drawing operations on this page
     *
     * @param Zend_Pdf_Style $style
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setStyle(Zend_Pdf_Style $style);

    /**
     * Get current font.
     *
     * @return Zend_Pdf_Resource_Font $font
     */
    public function getFont();

    /**
     * Get current font size
     *
     * @return float $fontSize
     */
    public function getFontSize();

    /**
     * Return the style, applied to the page.
     *
     * @return Zend_Pdf_Style|null
     */
    public function getStyle();

    /**
     * Save the graphics state of this page.
     * This takes a snapshot of the currently applied style, position, clipping area and
     * any rotation/translation/scaling that has been applied.
     *
     * @throws Zend_Pdf_Exception    - if a save is performed with an open path
     * @return Zend_Pdf_Page
     */
    public function saveGS();

    /**
     * Set the transparancy
     *
     * $alpha == 0  - transparent
     * $alpha == 1  - opaque
     *
     * Transparency modes, supported by PDF:
     * Normal (default), Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight,
     * SoftLight, Difference, Exclusion
     *
     * @param float $alpha
     * @param string $mode
     * @throws Zend_Pdf_Exception
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setAlpha($alpha, $mode = 'Normal');

    /**
     * Intersect current clipping area with a circle.
     *
     * @param float $x
     * @param float $y
     * @param float $radius
     * @param float $startAngle
     * @param float $endAngle
     * @return Zend_Pdf_Canvas_Interface
     */
    public function clipCircle($x, $y, $radius, $startAngle = null, $endAngle = null);

    /**
     * Intersect current clipping area with a polygon.
     *
     * Method signatures:
     * drawEllipse($x1, $y1, $x2, $y2);
     * drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle);
     *
     * @todo process special cases with $x2-$x1 == 0 or $y2-$y1 == 0
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $startAngle
     * @param float $endAngle
     * @return Zend_Pdf_Canvas_Interface
     */
    public function clipEllipse($x1, $y1, $x2, $y2, $startAngle = null, $endAngle = null);

    /**
     * Intersect current clipping area with a polygon.
     *
     * @param array $x  - array of float (the X co-ordinates of the vertices)
     * @param array $y  - array of float (the Y co-ordinates of the vertices)
     * @param integer $fillMethod
     * @return Zend_Pdf_Canvas_Interface
     */
    public function clipPolygon($x, $y, $fillMethod = Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING);

    /**
     * Intersect current clipping area with a rectangle.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return Zend_Pdf_Canvas_Interface
     */
    public function clipRectangle($x1, $y1, $x2, $y2);

    /**
     * Draw a circle centered on x, y with a radius of radius.
     *
     * Method signatures:
     * drawCircle($x, $y, $radius);
     * drawCircle($x, $y, $radius, $fillType);
     * drawCircle($x, $y, $radius, $startAngle, $endAngle);
     * drawCircle($x, $y, $radius, $startAngle, $endAngle, $fillType);
     *
     *
     * It's not a really circle, because PDF supports only cubic Bezier curves.
     * But _very_ good approximation.
     * It differs from a real circle on a maximum 0.00026 radiuses
     * (at PI/8, 3*PI/8, 5*PI/8, 7*PI/8, 9*PI/8, 11*PI/8, 13*PI/8 and 15*PI/8 angles).
     * At 0, PI/4, PI/2, 3*PI/4, PI, 5*PI/4, 3*PI/2 and 7*PI/4 it's exactly a tangent to a circle.
     *
     * @param float $x
     * @param float $y
     * @param float $radius
     * @param mixed $param4
     * @param mixed $param5
     * @param mixed $param6
     * @return Zend_Pdf_Canvas_Interface
     */
    public function  drawCircle($x, $y, $radius, $param4 = null, $param5 = null, $param6 = null);

    /**
     * Draw an ellipse inside the specified rectangle.
     *
     * Method signatures:
     * drawEllipse($x1, $y1, $x2, $y2);
     * drawEllipse($x1, $y1, $x2, $y2, $fillType);
     * drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle);
     * drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle, $fillType);
     *
     * @todo process special cases with $x2-$x1 == 0 or $y2-$y1 == 0
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param mixed $param5
     * @param mixed $param6
     * @param mixed $param7
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawEllipse($x1, $y1, $x2, $y2, $param5 = null, $param6 = null, $param7 = null);

    /**
     * Draw an image at the specified position on the page.
     *
     * @param Zend_Pdf_Image $image
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawImage(Zend_Pdf_Resource_Image $image, $x1, $y1, $x2, $y2);

    /**
     * Draw a LayoutBox at the specified position on the page.
     *
     * @internal (not implemented now)
     *
     * @param Zend_Pdf_Element_LayoutBox $box
     * @param float $x
     * @param float $y
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawLayoutBox($box, $x, $y);

    /**
     * Draw a line from x1,y1 to x2,y2.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawLine($x1, $y1, $x2, $y2);

    /**
     * Draw a polygon.
     *
     * If $fillType is Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE or
     * Zend_Pdf_Page::SHAPE_DRAW_FILL, then polygon is automatically closed.
     * See detailed description of these methods in a PDF documentation
     * (section 4.4.2 Path painting Operators, Filling)
     *
     * @param array $x  - array of float (the X co-ordinates of the vertices)
     * @param array $y  - array of float (the Y co-ordinates of the vertices)
     * @param integer $fillType
     * @param integer $fillMethod
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawPolygon($x, $y,
                                $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE,
                                $fillMethod = Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING);
    /**
     * Draw a rectangle.
     *
     * Fill types:
     * Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE - fill rectangle and stroke (default)
     * Zend_Pdf_Page::SHAPE_DRAW_STROKE      - stroke rectangle
     * Zend_Pdf_Page::SHAPE_DRAW_FILL        - fill rectangle
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param integer $fillType
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawRectangle($x1, $y1, $x2, $y2, $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

    /**
     * Draw a rounded rectangle.
     *
     * Fill types:
     * Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE - fill rectangle and stroke (default)
     * Zend_Pdf_Page::SHAPE_DRAW_STROKE      - stroke rectangle
     * Zend_Pdf_Page::SHAPE_DRAW_FILL        - fill rectangle
     *
     * radius is an integer representing radius of the four corners, or an array
     * of four integers representing the radius starting at top left, going
     * clockwise
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param integer|array $radius
     * @param integer $fillType
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawRoundedRectangle($x1, $y1, $x2, $y2, $radius,
                                         $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

    /**
     * Draw a line of text at the specified position.
     *
     * @param string $text
     * @param float $x
     * @param float $y
     * @param string $charEncoding (optional) Character encoding of source text.
     *   Defaults to current locale.
     * @throws Zend_Pdf_Exception
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawText($text, $x, $y, $charEncoding = '');

     /**
     * Close the path by drawing a straight line back to it's beginning.
     *
     * @internal (needs implementation)
     *
     * @throws Zend_Pdf_Exception    - if a path hasn't been started with pathMove()
     * @return Zend_Pdf_Canvas_Interface
     */
    public function pathClose();

    /**
     * Continue the open path in a straight line to the specified position.
     *
     * @internal (needs implementation)
     *
     * @param float $x  - the X co-ordinate to move to
     * @param float $y  - the Y co-ordinate to move to
     * @return Zend_Pdf_Canvas_Interface
     */
    public function pathLine($x, $y);

    /**
     * Start a new path at the specified position. If a path has already been started,
     * move the cursor without drawing a line.
     *
     * @internal (needs implementation)
     *
     * @param float $x  - the X co-ordinate to move to
     * @param float $y  - the Y co-ordinate to move to
     * @return Zend_Pdf_Canvas_Interface
     */
    public function pathMove($x, $y);

    /**
     * Rotate the page.
     *
     * @param float $x  - the X co-ordinate of rotation point
     * @param float $y  - the Y co-ordinate of rotation point
     * @param float $angle - rotation angle
     * @return Zend_Pdf_Canvas_Interface
     */
    public function rotate($x, $y, $angle);

    /**
     * Scale coordination system.
     *
     * @param float $xScale - X dimention scale factor
     * @param float $yScale - Y dimention scale factor
     * @return Zend_Pdf_Canvas_Interface
     */
    public function scale($xScale, $yScale);

    /**
     * Translate coordination system.
     *
     * @param float $xShift - X coordinate shift
     * @param float $yShift - Y coordinate shift
     * @return Zend_Pdf_Canvas_Interface
     */
    public function translate($xShift, $yShift);

    /**
     * Translate coordination system.
     *
     * @param float $x  - the X co-ordinate of axis skew point
     * @param float $y  - the Y co-ordinate of axis skew point
     * @param float $xAngle - X axis skew angle
     * @param float $yAngle - Y axis skew angle
     * @return Zend_Pdf_Canvas_Interface
     */
    public function skew($x, $y, $xAngle, $yAngle);

    /**
     * Writes the raw data to the page's content stream.
     *
     * Be sure to consult the PDF reference to ensure your syntax is correct. No
     * attempt is made to ensure the validity of the stream data.
     *
     * @param string $data
     * @param string $procSet (optional) Name of ProcSet to add.
     * @return Zend_Pdf_Canvas_Interface
     */
    public function rawWrite($data, $procSet = null);
}
