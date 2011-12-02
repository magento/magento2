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

#require_once 'Zend/Pdf/Canvas/Interface.php';

/** Internally used classes */
#require_once 'Zend/Pdf/Element.php';
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/String/Binary.php';
#require_once 'Zend/Pdf/Element/Boolean.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Null.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String.php';


/**
 * Canvas is an abstract rectangle drawing area which can be dropped into
 * page object at specified place.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Canvas_Abstract implements Zend_Pdf_Canvas_Interface
{
    /**
     * Drawing instructions
     *
     * @var string
     */
    protected $_contents = '';

    /**
     * Current font
     *
     * @var Zend_Pdf_Resource_Font
     */
    protected $_font = null;

    /**
     * Current font size
     *
     * @var float
     */
    protected $_fontSize;

    /**
     * Current style
     *
     * @var Zend_Pdf_Style
     */
    protected $_style = null;


    /**
     * Counter for the "Save" operations
     *
     * @var integer
     */
    protected $_saveCount = 0;


    /**
     * Add procedureSet to the Page description
     *
     * @param string $procSetName
     */
    abstract protected function _addProcSet($procSetName);

    /**
     * Attach resource to the canvas
     *
     * Method returns a name of the resource which can be used
     * as a resource reference within drawing instructions stream
     * Allowed types: 'ExtGState', 'ColorSpace', 'Pattern', 'Shading',
     * 'XObject', 'Font', 'Properties'
     *
     * @param string $type
     * @param Zend_Pdf_Resource $resource
     * @return string
     */
    abstract protected function _attachResource($type, Zend_Pdf_Resource $resource);

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
    public function drawCanvas(Zend_Pdf_Canvas_Interface $canvas, $x1, $y1, $x2 = null, $y2 = null)
    {
        $this->saveGS();

        $this->translate($x1, $y1);

        if ($x2 === null) {
            $with = $canvas->getWidth();
        } else {
            $with = $x2 - $x1;
        }
        if ($y2 === null) {
            $height = $canvas->getHeight();
        } else {
            $height = $y2 - $y1;
        }

        $this->clipRectangle(0, 0, $with, $height);

        if ($x2 !== null  ||  $y2 !== null) {
            // Drawn canvas has to be scaled.
            if ($x2 !== null) {
                $xScale = $with/$canvas->getWidth();
            } else {
                $xScale = 1;
            }

            if ($y2 !== null) {
                $yScale = $height/$canvas->getHeight();
            } else {
                $yScale = 1;
            }

            $this->scale($xScale, $yScale);
        }

        $contentsToDraw = $canvas->getContents();
        /** @todo implementation */

        $this->restoreGS();

        return $this;
    }

    /**
     * Set fill color.
     *
     * @param Zend_Pdf_Color $color
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setFillColor(Zend_Pdf_Color $color)
    {
        $this->_addProcSet('PDF');
        $this->_contents .= $color->instructions(false);

        return $this;
    }

    /**
     * Set line color.
     *
     * @param Zend_Pdf_Color $color
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setLineColor(Zend_Pdf_Color $color)
    {
        $this->_addProcSet('PDF');
        $this->_contents .= $color->instructions(true);

        return $this;
    }

    /**
     * Set line width.
     *
     * @param float $width
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setLineWidth($width)
    {
        $this->_addProcSet('PDF');
        $widthObj = new Zend_Pdf_Element_Numeric($width);
        $this->_contents .= $widthObj->toString() . " w\n";

        return $this;
    }

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
    public function setLineDashingPattern($pattern, $phase = 0)
    {
        $this->_addProcSet('PDF');

        #require_once 'Zend/Pdf/Page.php';
        if ($pattern === Zend_Pdf_Page::LINE_DASHING_SOLID) {
            $pattern = array();
            $phase   = 0;
        }

        $dashPattern  = new Zend_Pdf_Element_Array();
        $phaseEleemnt = new Zend_Pdf_Element_Numeric($phase);

        foreach ($pattern as $dashItem) {
            $dashElement = new Zend_Pdf_Element_Numeric($dashItem);
            $dashPattern->items[] = $dashElement;
        }

        $this->_contents .= $dashPattern->toString() . ' '
                         . $phaseEleemnt->toString() . " d\n";

        return $this;
    }

    /**
     * Set current font.
     *
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setFont(Zend_Pdf_Resource_Font $font, $fontSize)
    {
        $this->_addProcSet('Text');
        $fontName = $this->_attachResource('Font', $font);

        $this->_font     = $font;
        $this->_fontSize = $fontSize;

        $fontNameObj = new Zend_Pdf_Element_Name($fontName);
        $fontSizeObj = new Zend_Pdf_Element_Numeric($fontSize);
        $this->_contents .= $fontNameObj->toString() . ' ' . $fontSizeObj->toString() . " Tf\n";

        return $this;
    }

    /**
     * Set the style to use for future drawing operations on this page
     *
     * @param Zend_Pdf_Style $style
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setStyle(Zend_Pdf_Style $style)
    {
        $this->_addProcSet('Text');
        $this->_addProcSet('PDF');
        if ($style->getFont() !== null) {
            $this->setFont($style->getFont(), $style->getFontSize());
        }
        $this->_contents .= $style->instructions($this->_dictionary->Resources);

        $this->_style = $style;

        return $this;
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
     * Return the style, applied to the page.
     *
     * @return Zend_Pdf_Style
     */
    public function getStyle()
    {
        return $this->_style;
    }

    /**
     * Save the graphics state of this page.
     * This takes a snapshot of the currently applied style, position, clipping area and
     * any rotation/translation/scaling that has been applied.
     *
     * @todo check for the open paths
     * @throws Zend_Pdf_Exception    - if a save is performed with an open path
     * @return Zend_Pdf_Canvas_Interface
     */
    public function saveGS()
    {
        $this->_saveCount++;

        $this->_addProcSet('PDF');
        $this->_contents .= " q\n";

        return $this;
    }

    /**
     * Restore the graphics state that was saved with the last call to saveGS().
     *
     * @throws Zend_Pdf_Exception   - if there is no previously saved state
     * @return Zend_Pdf_Canvas_Interface
     */
    public function restoreGS()
    {
        if ($this->_saveCount-- <= 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Restoring graphics state which is not saved');
        }
        $this->_contents .= " Q\n";

        return $this;
    }

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
     * @return Zend_Pdf_Canvas_Interface
     */
    public function setAlpha($alpha, $mode = 'Normal')
    {
        $this->_addProcSet('Text');
        $this->_addProcSet('PDF');

        $graphicsState = new Zend_Pdf_Resource_GraphicsState();

        $graphicsState->setAlpha($alpha, $mode);
        $gStateName = $this->_attachResource('ExtGState', $graphicsState);

        $gStateNameObject = new Zend_Pdf_Element_Name($gStateName);
        $this->_contents .= $gStateNameObject->toString() . " gs\n";

        return $this;
    }

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
    public function clipCircle($x, $y, $radius, $startAngle = null, $endAngle = null)
    {
        $this->clipEllipse($x - $radius, $y - $radius,
                           $x + $radius, $y + $radius,
                           $startAngle, $endAngle);

        return $this;
    }

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
    public function clipEllipse($x1, $y1, $x2, $y2, $startAngle = null, $endAngle = null)
    {
        $this->_addProcSet('PDF');

        if ($x2 < $x1) {
            $temp = $x1;
            $x1   = $x2;
            $x2   = $temp;
        }
        if ($y2 < $y1) {
            $temp = $y1;
            $y1   = $y2;
            $y2   = $temp;
        }

        $x = ($x1 + $x2)/2.;
        $y = ($y1 + $y2)/2.;

        $xC = new Zend_Pdf_Element_Numeric($x);
        $yC = new Zend_Pdf_Element_Numeric($y);

        if ($startAngle !== null) {
            if ($startAngle != 0) { $startAngle = fmod($startAngle, M_PI*2); }
            if ($endAngle   != 0) { $endAngle   = fmod($endAngle,   M_PI*2); }

            if ($startAngle > $endAngle) {
                $endAngle += M_PI*2;
            }

            $clipPath    = $xC->toString() . ' ' . $yC->toString() . " m\n";
            $clipSectors = (int)ceil(($endAngle - $startAngle)/M_PI_4);
            $clipRadius  = max($x2 - $x1, $y2 - $y1);

            for($count = 0; $count <= $clipSectors; $count++) {
                $pAngle = $startAngle + ($endAngle - $startAngle)*$count/(float)$clipSectors;

                $pX = new Zend_Pdf_Element_Numeric($x + cos($pAngle)*$clipRadius);
                $pY = new Zend_Pdf_Element_Numeric($y + sin($pAngle)*$clipRadius);
                $clipPath .= $pX->toString() . ' ' . $pY->toString() . " l\n";
            }

            $this->_contents .= $clipPath . "h\nW\nn\n";
        }

        $xLeft  = new Zend_Pdf_Element_Numeric($x1);
        $xRight = new Zend_Pdf_Element_Numeric($x2);
        $yUp    = new Zend_Pdf_Element_Numeric($y2);
        $yDown  = new Zend_Pdf_Element_Numeric($y1);

        $xDelta  = 2*(M_SQRT2 - 1)*($x2 - $x1)/3.;
        $yDelta  = 2*(M_SQRT2 - 1)*($y2 - $y1)/3.;
        $xr = new Zend_Pdf_Element_Numeric($x + $xDelta);
        $xl = new Zend_Pdf_Element_Numeric($x - $xDelta);
        $yu = new Zend_Pdf_Element_Numeric($y + $yDelta);
        $yd = new Zend_Pdf_Element_Numeric($y - $yDelta);

        $this->_contents .= $xC->toString() . ' ' . $yUp->toString() . " m\n"
                         .  $xr->toString() . ' ' . $yUp->toString() . ' '
                         .    $xRight->toString() . ' ' . $yu->toString() . ' '
                         .      $xRight->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xRight->toString() . ' ' . $yd->toString() . ' '
                         .    $xr->toString() . ' ' . $yDown->toString() . ' '
                         .      $xC->toString() . ' ' . $yDown->toString() . " c\n"
                         .  $xl->toString() . ' ' . $yDown->toString() . ' '
                         .    $xLeft->toString() . ' ' . $yd->toString() . ' '
                         .      $xLeft->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xLeft->toString() . ' ' . $yu->toString() . ' '
                         .    $xl->toString() . ' ' . $yUp->toString() . ' '
                         .      $xC->toString() . ' ' . $yUp->toString() . " c\n"
                         .  "h\nW\nn\n";

        return $this;
    }

    /**
     * Intersect current clipping area with a polygon.
     *
     * @param array $x  - array of float (the X co-ordinates of the vertices)
     * @param array $y  - array of float (the Y co-ordinates of the vertices)
     * @param integer $fillMethod
     * @return Zend_Pdf_Canvas_Interface
     */
    public function clipPolygon($x, $y, $fillMethod = Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING)
    {
        $this->_addProcSet('PDF');

        $firstPoint = true;
        foreach ($x as $id => $xVal) {
            $xObj = new Zend_Pdf_Element_Numeric($xVal);
            $yObj = new Zend_Pdf_Element_Numeric($y[$id]);

            if ($firstPoint) {
                $path = $xObj->toString() . ' ' . $yObj->toString() . " m\n";
                $firstPoint = false;
            } else {
                $path .= $xObj->toString() . ' ' . $yObj->toString() . " l\n";
            }
        }

        $this->_contents .= $path;

        if ($fillMethod == Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING) {
            $this->_contents .= " h\n W\nn\n";
        } else {
            // Even-Odd fill method.
            $this->_contents .= " h\n W*\nn\n";
        }

        return $this;
    }

    /**
     * Intersect current clipping area with a rectangle.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return Zend_Pdf_Canvas_Interface
     */
    public function clipRectangle($x1, $y1, $x2, $y2)
    {
        $this->_addProcSet('PDF');

        $x1Obj      = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj      = new Zend_Pdf_Element_Numeric($y1);
        $widthObj   = new Zend_Pdf_Element_Numeric($x2 - $x1);
        $height2Obj = new Zend_Pdf_Element_Numeric($y2 - $y1);

        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                         .      $widthObj->toString() . ' ' . $height2Obj->toString() . " re\n"
                         .  " W\nn\n";

        return $this;
    }

// ------------------------------------------------------------------------------------------
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
    public function  drawCircle($x, $y, $radius, $param4 = null, $param5 = null, $param6 = null)
    {
        $this->drawEllipse($x - $radius, $y - $radius,
                           $x + $radius, $y + $radius,
                           $param4, $param5, $param6);

        return $this;
    }

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
    public function drawEllipse($x1, $y1, $x2, $y2, $param5 = null, $param6 = null, $param7 = null)
    {
        if ($param5 === null) {
            // drawEllipse($x1, $y1, $x2, $y2);
            $startAngle = null;
            $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE;
        } else if ($param6 === null) {
            // drawEllipse($x1, $y1, $x2, $y2, $fillType);
            $startAngle = null;
            $fillType = $param5;
        } else {
            // drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle);
            // drawEllipse($x1, $y1, $x2, $y2, $startAngle, $endAngle, $fillType);
            $startAngle = $param5;
            $endAngle   = $param6;

            if ($param7 === null) {
                $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE;
            } else {
                $fillType = $param7;
            }
        }

        $this->_addProcSet('PDF');

        if ($x2 < $x1) {
            $temp = $x1;
            $x1   = $x2;
            $x2   = $temp;
        }
        if ($y2 < $y1) {
            $temp = $y1;
            $y1   = $y2;
            $y2   = $temp;
        }

        $x = ($x1 + $x2)/2.;
        $y = ($y1 + $y2)/2.;

        $xC = new Zend_Pdf_Element_Numeric($x);
        $yC = new Zend_Pdf_Element_Numeric($y);

        if ($startAngle !== null) {
            if ($startAngle != 0) { $startAngle = fmod($startAngle, M_PI*2); }
            if ($endAngle   != 0) { $endAngle   = fmod($endAngle,   M_PI*2); }

            if ($startAngle > $endAngle) {
                $endAngle += M_PI*2;
            }

            $clipPath    = $xC->toString() . ' ' . $yC->toString() . " m\n";
            $clipSectors = (int)ceil(($endAngle - $startAngle)/M_PI_4);
            $clipRadius  = max($x2 - $x1, $y2 - $y1);

            for($count = 0; $count <= $clipSectors; $count++) {
                $pAngle = $startAngle + ($endAngle - $startAngle)*$count/(float)$clipSectors;

                $pX = new Zend_Pdf_Element_Numeric($x + cos($pAngle)*$clipRadius);
                $pY = new Zend_Pdf_Element_Numeric($y + sin($pAngle)*$clipRadius);
                $clipPath .= $pX->toString() . ' ' . $pY->toString() . " l\n";
            }

            $this->_contents .= "q\n" . $clipPath . "h\nW\nn\n";
        }

        $xLeft  = new Zend_Pdf_Element_Numeric($x1);
        $xRight = new Zend_Pdf_Element_Numeric($x2);
        $yUp    = new Zend_Pdf_Element_Numeric($y2);
        $yDown  = new Zend_Pdf_Element_Numeric($y1);

        $xDelta  = 2*(M_SQRT2 - 1)*($x2 - $x1)/3.;
        $yDelta  = 2*(M_SQRT2 - 1)*($y2 - $y1)/3.;
        $xr = new Zend_Pdf_Element_Numeric($x + $xDelta);
        $xl = new Zend_Pdf_Element_Numeric($x - $xDelta);
        $yu = new Zend_Pdf_Element_Numeric($y + $yDelta);
        $yd = new Zend_Pdf_Element_Numeric($y - $yDelta);

        $this->_contents .= $xC->toString() . ' ' . $yUp->toString() . " m\n"
                         .  $xr->toString() . ' ' . $yUp->toString() . ' '
                         .    $xRight->toString() . ' ' . $yu->toString() . ' '
                         .      $xRight->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xRight->toString() . ' ' . $yd->toString() . ' '
                         .    $xr->toString() . ' ' . $yDown->toString() . ' '
                         .      $xC->toString() . ' ' . $yDown->toString() . " c\n"
                         .  $xl->toString() . ' ' . $yDown->toString() . ' '
                         .    $xLeft->toString() . ' ' . $yd->toString() . ' '
                         .      $xLeft->toString() . ' ' . $yC->toString() . " c\n"
                         .  $xLeft->toString() . ' ' . $yu->toString() . ' '
                         .    $xl->toString() . ' ' . $yUp->toString() . ' '
                         .      $xC->toString() . ' ' . $yUp->toString() . " c\n";

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                $this->_contents .= " B*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                $this->_contents .= " f*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }

        if ($startAngle !== null) {
            $this->_contents .= "Q\n";
        }

        return $this;
    }

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
    public function drawImage(Zend_Pdf_Resource_Image $image, $x1, $y1, $x2, $y2)
    {
        $this->_addProcSet('PDF');

        $imageName    = $this->_attachResource('XObject', $image);
        $imageNameObj = new Zend_Pdf_Element_Name($imageName);

        $x1Obj     = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj     = new Zend_Pdf_Element_Numeric($y1);
        $widthObj  = new Zend_Pdf_Element_Numeric($x2 - $x1);
        $heightObj = new Zend_Pdf_Element_Numeric($y2 - $y1);

        $this->_contents .= "q\n"
                         .  '1 0 0 1 ' . $x1Obj->toString() . ' ' . $y1Obj->toString() . " cm\n"
                         .  $widthObj->toString() . ' 0 0 ' . $heightObj->toString() . " 0 0 cm\n"
                         .  $imageNameObj->toString() . " Do\n"
                         .  "Q\n";

        return $this;
    }

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
    public function drawLayoutBox($box, $x, $y)
    {
        /** @todo implementation */
        return $this;
    }

    /**
     * Draw a line from x1,y1 to x2,y2.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return Zend_Pdf_Canvas_Interface
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $this->_addProcSet('PDF');

        $x1Obj = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj = new Zend_Pdf_Element_Numeric($y1);
        $x2Obj = new Zend_Pdf_Element_Numeric($x2);
        $y2Obj = new Zend_Pdf_Element_Numeric($y2);

        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " m\n"
                         .  $x2Obj->toString() . ' ' . $y2Obj->toString() . " l\n S\n";

        return $this;
    }

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
                                $fillMethod = Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING)
    {
        $this->_addProcSet('PDF');

        $firstPoint = true;
        foreach ($x as $id => $xVal) {
            $xObj = new Zend_Pdf_Element_Numeric($xVal);
            $yObj = new Zend_Pdf_Element_Numeric($y[$id]);

            if ($firstPoint) {
                $path = $xObj->toString() . ' ' . $yObj->toString() . " m\n";
                $firstPoint = false;
            } else {
                $path .= $xObj->toString() . ' ' . $yObj->toString() . " l\n";
            }
        }

        $this->_contents .= $path;

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                if ($fillMethod == Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING) {
                    $this->_contents .= " b\n";
                } else {
                    // Even-Odd fill method.
                    $this->_contents .= " b*\n";
                }
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                if ($fillMethod == Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING) {
                    $this->_contents .= " h\n f\n";
                } else {
                    // Even-Odd fill method.
                    $this->_contents .= " h\n f*\n";
                }
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }

        return $this;
    }

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
    public function drawRectangle($x1, $y1, $x2, $y2, $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE)
    {
        $this->_addProcSet('PDF');

        $x1Obj      = new Zend_Pdf_Element_Numeric($x1);
        $y1Obj      = new Zend_Pdf_Element_Numeric($y1);
        $widthObj   = new Zend_Pdf_Element_Numeric($x2 - $x1);
        $height2Obj = new Zend_Pdf_Element_Numeric($y2 - $y1);

        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                             .  $widthObj->toString() . ' ' . $height2Obj->toString() . " re\n";

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                $this->_contents .= " B*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                $this->_contents .= " f*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }

        return $this;
    }

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
                                         $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE)
    {

        $this->_addProcSet('PDF');

        if(!is_array($radius)) {
            $radius = array($radius, $radius, $radius, $radius);
        } else {
            for ($i = 0; $i < 4; $i++) {
                if(!isset($radius[$i])) {
                    $radius[$i] = 0;
                }
            }
        }

        $topLeftX      = $x1;
        $topLeftY      = $y2;
        $topRightX     = $x2;
        $topRightY     = $y2;
        $bottomRightX  = $x2;
        $bottomRightY  = $y1;
        $bottomLeftX   = $x1;
        $bottomLeftY   = $y1;

        //draw top side
        $x1Obj = new Zend_Pdf_Element_Numeric($topLeftX + $radius[0]);
        $y1Obj = new Zend_Pdf_Element_Numeric($topLeftY);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " m\n";
        $x1Obj = new Zend_Pdf_Element_Numeric($topRightX - $radius[1]);
        $y1Obj = new Zend_Pdf_Element_Numeric($topRightY);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw top right corner if needed
        if ($radius[1] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($topRightX);
            $y1Obj = new Zend_Pdf_Element_Numeric($topRightY);
            $x2Obj = new Zend_Pdf_Element_Numeric($topRightX);
            $y2Obj = new Zend_Pdf_Element_Numeric($topRightY);
            $x3Obj = new Zend_Pdf_Element_Numeric($topRightX);
            $y3Obj = new Zend_Pdf_Element_Numeric($topRightY - $radius[1]);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                              . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                              . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                              . " c\n";
        }

        //draw right side
        $x1Obj = new Zend_Pdf_Element_Numeric($bottomRightX);
        $y1Obj = new Zend_Pdf_Element_Numeric($bottomRightY + $radius[2]);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw bottom right corner if needed
        if ($radius[2] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($bottomRightX);
            $y1Obj = new Zend_Pdf_Element_Numeric($bottomRightY);
            $x2Obj = new Zend_Pdf_Element_Numeric($bottomRightX);
            $y2Obj = new Zend_Pdf_Element_Numeric($bottomRightY);
            $x3Obj = new Zend_Pdf_Element_Numeric($bottomRightX - $radius[2]);
            $y3Obj = new Zend_Pdf_Element_Numeric($bottomRightY);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                              . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                              . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                              . " c\n";
        }

        //draw bottom side
        $x1Obj = new Zend_Pdf_Element_Numeric($bottomLeftX + $radius[3]);
        $y1Obj = new Zend_Pdf_Element_Numeric($bottomLeftY);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw bottom left corner if needed
        if ($radius[3] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($bottomLeftX);
            $y1Obj = new Zend_Pdf_Element_Numeric($bottomLeftY);
            $x2Obj = new Zend_Pdf_Element_Numeric($bottomLeftX);
            $y2Obj = new Zend_Pdf_Element_Numeric($bottomLeftY);
            $x3Obj = new Zend_Pdf_Element_Numeric($bottomLeftX);
            $y3Obj = new Zend_Pdf_Element_Numeric($bottomLeftY + $radius[3]);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                              . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                              . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                              . " c\n";
        }

        //draw left side
        $x1Obj = new Zend_Pdf_Element_Numeric($topLeftX);
        $y1Obj = new Zend_Pdf_Element_Numeric($topLeftY - $radius[0]);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw top left corner if needed
        if ($radius[0] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($topLeftX);
            $y1Obj = new Zend_Pdf_Element_Numeric($topLeftY);
            $x2Obj = new Zend_Pdf_Element_Numeric($topLeftX);
            $y2Obj = new Zend_Pdf_Element_Numeric($topLeftY);
            $x3Obj = new Zend_Pdf_Element_Numeric($topLeftX + $radius[0]);
            $y3Obj = new Zend_Pdf_Element_Numeric($topLeftY);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                              . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                              . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                              . " c\n";
        }

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                $this->_contents .= " B*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                $this->_contents .= " f*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }

        return $this;
    }

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
    public function drawText($text, $x, $y, $charEncoding = '')
    {
        if ($this->_font === null) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Font has not been set');
        }

        $this->_addProcSet('Text');

        $textObj = new Zend_Pdf_Element_String($this->_font->encodeString($text, $charEncoding));
        $xObj    = new Zend_Pdf_Element_Numeric($x);
        $yObj    = new Zend_Pdf_Element_Numeric($y);

        $this->_contents .= "BT\n"
                         .  $xObj->toString() . ' ' . $yObj->toString() . " Td\n"
                         .  $textObj->toString() . " Tj\n"
                         .  "ET\n";

        return $this;
    }

     /**
     * Close the path by drawing a straight line back to it's beginning.
     *
     * @internal (needs implementation)
     *
     * @throws Zend_Pdf_Exception    - if a path hasn't been started with pathMove()
     * @return Zend_Pdf_Canvas_Interface
     */
    public function pathClose()
    {
        /** @todo implementation */
        return $this;
    }

    /**
     * Continue the open path in a straight line to the specified position.
     *
     * @internal (needs implementation)
     *
     * @param float $x  - the X co-ordinate to move to
     * @param float $y  - the Y co-ordinate to move to
     * @return Zend_Pdf_Canvas_Interface
     */
    public function pathLine($x, $y)
    {
        /** @todo implementation */
        return $this;
    }

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
    public function pathMove($x, $y)
    {
        /** @todo implementation */
        return $this;
    }

    /**
     * Rotate the page.
     *
     * @param float $x  - the X co-ordinate of rotation point
     * @param float $y  - the Y co-ordinate of rotation point
     * @param float $angle - rotation angle
     * @return Zend_Pdf_Canvas_Interface
     */
    public function rotate($x, $y, $angle)
    {
        $cos  = new Zend_Pdf_Element_Numeric(cos($angle));
        $sin  = new Zend_Pdf_Element_Numeric(sin($angle));
        $mSin = new Zend_Pdf_Element_Numeric(-$sin->value);

        $xObj = new Zend_Pdf_Element_Numeric($x);
        $yObj = new Zend_Pdf_Element_Numeric($y);

        $mXObj = new Zend_Pdf_Element_Numeric(-$x);
        $mYObj = new Zend_Pdf_Element_Numeric(-$y);


        $this->_addProcSet('PDF');
        $this->_contents .= '1 0 0 1 ' . $xObj->toString() . ' ' . $yObj->toString() . " cm\n"
                         .  $cos->toString() . ' ' . $sin->toString() . ' ' . $mSin->toString() . ' ' . $cos->toString() . " 0 0 cm\n"
                         .  '1 0 0 1 ' . $mXObj->toString() . ' ' . $mYObj->toString() . " cm\n";

        return $this;
    }

    /**
     * Scale coordination system.
     *
     * @param float $xScale - X dimention scale factor
     * @param float $yScale - Y dimention scale factor
     * @return Zend_Pdf_Canvas_Interface
     */
    public function scale($xScale, $yScale)
    {
        $xScaleObj = new Zend_Pdf_Element_Numeric($xScale);
        $yScaleObj = new Zend_Pdf_Element_Numeric($yScale);

        $this->_addProcSet('PDF');
        $this->_contents .= $xScaleObj->toString() . ' 0 0 ' . $yScaleObj->toString() . " 0 0 cm\n";

        return $this;
    }

    /**
     * Translate coordination system.
     *
     * @param float $xShift - X coordinate shift
     * @param float $yShift - Y coordinate shift
     * @return Zend_Pdf_Canvas_Interface
     */
    public function translate($xShift, $yShift)
    {
        $xShiftObj = new Zend_Pdf_Element_Numeric($xShift);
        $yShiftObj = new Zend_Pdf_Element_Numeric($yShift);

        $this->_addProcSet('PDF');
        $this->_contents .= '1 0 0 1 ' . $xShiftObj->toString() . ' ' . $yShiftObj->toString() . " cm\n";

        return $this;
    }

    /**
     * Translate coordination system.
     *
     * @param float $x  - the X co-ordinate of axis skew point
     * @param float $y  - the Y co-ordinate of axis skew point
     * @param float $xAngle - X axis skew angle
     * @param float $yAngle - Y axis skew angle
     * @return Zend_Pdf_Canvas_Interface
     */
    public function skew($x, $y, $xAngle, $yAngle)
    {
        $tanXObj = new Zend_Pdf_Element_Numeric(tan($xAngle));
        $tanYObj = new Zend_Pdf_Element_Numeric(-tan($yAngle));

        $xObj = new Zend_Pdf_Element_Numeric($x);
        $yObj = new Zend_Pdf_Element_Numeric($y);

        $mXObj = new Zend_Pdf_Element_Numeric(-$x);
        $mYObj = new Zend_Pdf_Element_Numeric(-$y);

        $this->_addProcSet('PDF');
        $this->_contents .= '1 0 0 1 ' . $xObj->toString() . ' ' . $yObj->toString() . " cm\n"
                         .  '1 ' . $tanXObj->toString() . ' ' . $tanYObj->toString() . " 1 0 0 cm\n"
                         .  '1 0 0 1 ' . $mXObj->toString() . ' ' . $mYObj->toString() . " cm\n";

        return $this;
    }

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
    public function rawWrite($data, $procSet = null)
    {
        if (! empty($procSet)) {
            $this->_addProcSet($procSet);
        }
        $this->_contents .= $data;

        return $this;
    }
}
