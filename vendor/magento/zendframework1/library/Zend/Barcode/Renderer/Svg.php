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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Image.php 20366 2010-01-18 03:56:52Z ralph $
 */

/** @see Zend_Barcode_Renderer_RendererAbstract*/
#require_once 'Zend/Barcode/Renderer/RendererAbstract.php';

/**
 * Class for rendering the barcode as svg
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Renderer_Svg extends Zend_Barcode_Renderer_RendererAbstract
{

    /**
     * Resource for the image
     * @var DOMDocument
     */
    protected $_resource = null;

    /**
     * Root element of the XML structure
     * @var DOMElement
     */
    protected $_rootElement = null;

    /**
     * Height of the rendered image wanted by user
     * @var integer
     */
    protected $_userHeight = 0;

    /**
     * Width of the rendered image wanted by user
     * @var integer
     */
    protected $_userWidth = 0;

    /**
     * Set height of the result image
     *
     * @param null|integer $value
     * @return Zend_Image_Barcode_Abstract
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setHeight($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Svg height must be greater than or equals 0'
            );
        }
        $this->_userHeight = intval($value);
        return $this;
    }

    /**
     * Get barcode height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_userHeight;
    }

    /**
     * Set barcode width
     *
     * @param mixed $value
     * @return self
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setWidth($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Svg width must be greater than or equals 0'
            );
        }
        $this->_userWidth = intval($value);
        return $this;
    }

    /**
     * Get barcode width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_userWidth;
    }

    /**
     * Set an image resource to draw the barcode inside
     *
     * @param $svg
     * @return Zend_Barcode_Renderer
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setResource($svg)
    {
        if (!$svg instanceof DOMDocument) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Invalid DOMDocument resource provided to setResource()'
            );
        }
        $this->_resource = $svg;
        return $this;
    }

    /**
     * Initialize the image resource
     *
     * @return void
     */
    protected function _initRenderer()
    {
        $barcodeWidth  = $this->_barcode->getWidth(true);
        $barcodeHeight = $this->_barcode->getHeight(true);

        $backgroundColor = $this->_barcode->getBackgroundColor();
        $imageBackgroundColor = 'rgb(' . implode(', ', array(($backgroundColor & 0xFF0000) >> 16,
                                                             ($backgroundColor & 0x00FF00) >> 8,
                                                             ($backgroundColor & 0x0000FF))) . ')';

        $width = $barcodeWidth;
        $height = $barcodeHeight;
        if ($this->_userWidth && $this->_barcode->getType() != 'error') {
            $width = $this->_userWidth;
        }
        if ($this->_userHeight && $this->_barcode->getType() != 'error') {
            $height = $this->_userHeight;
        }
        if ($this->_resource === null) {
            $this->_resource = new DOMDocument('1.0', 'utf-8');
            $this->_resource->formatOutput = true;
            $this->_rootElement = $this->_resource->createElement('svg');
            $this->_rootElement->setAttribute('xmlns', "http://www.w3.org/2000/svg");
            $this->_rootElement->setAttribute('version', '1.1');
            $this->_rootElement->setAttribute('width', $width);
            $this->_rootElement->setAttribute('height', $height);

            $this->_appendRootElement('title',
                                      array(),
                                      "Barcode " . strtoupper($this->_barcode->getType()) . " " . $this->_barcode->getText());
        } else {
            $this->_readRootElement();
            $width = $this->_rootElement->getAttribute('width');
            $height = $this->_rootElement->getAttribute('height');
        }
        $this->_adjustPosition($height, $width);

        $this->_appendRootElement('rect',
                          array('x' => $this->_leftOffset,
                                'y' => $this->_topOffset,
                                'width' => ($this->_leftOffset + $barcodeWidth - 1),
                                'height' => ($this->_topOffset + $barcodeHeight - 1),
                                'fill' => $imageBackgroundColor));
    }

    protected function _readRootElement()
    {
        if ($this->_resource !== null) {
            $this->_rootElement = $this->_resource->documentElement;
        }
    }

    /**
     * Append a new DOMElement to the root element
     *
     * @param string $tagName
     * @param array $attributes
     * @param string $textContent
     */
    protected function _appendRootElement($tagName, $attributes = array(), $textContent = null)
    {
        $newElement = $this->_createElement($tagName, $attributes, $textContent);
        $this->_rootElement->appendChild($newElement);
    }

    /**
     * Create DOMElement
     *
     * @param string $tagName
     * @param array $attributes
     * @param string $textContent
     * @return DOMElement
     */
    protected function _createElement($tagName, $attributes = array(), $textContent = null)
    {
        $element = $this->_resource->createElement($tagName);
        foreach ($attributes as $k =>$v) {
            $element->setAttribute($k, $v);
        }
        if ($textContent !== null) {
            $element->appendChild(new DOMText((string) $textContent));
        }
        return $element;
    }

    /**
     * Check barcode parameters
     *
     * @return void
     */
    protected function _checkParams()
    {
        $this->_checkDimensions();
    }

    /**
     * Check barcode dimensions
     *
     * @return void
     * @throws Zend_Barcode_Renderer_Exception
     */
    protected function _checkDimensions()
    {
        if ($this->_resource !== null) {
            $this->_readRootElement();
            $height = (float) $this->_rootElement->getAttribute('height');
            if ($height < $this->_barcode->getHeight(true)) {
                #require_once 'Zend/Barcode/Renderer/Exception.php';
                throw new Zend_Barcode_Renderer_Exception(
                    'Barcode is define outside the image (height)'
                );
            }
        } else {
            if ($this->_userHeight) {
                $height = $this->_barcode->getHeight(true);
                if ($this->_userHeight < $height) {
                    #require_once 'Zend/Barcode/Renderer/Exception.php';
                    throw new Zend_Barcode_Renderer_Exception(sprintf(
                        "Barcode is define outside the image (calculated: '%d', provided: '%d')",
                        $height,
                        $this->_userHeight
                    ));
                }
            }
        }
        if ($this->_resource !== null) {
            $this->_readRootElement();
            $width = $this->_rootElement->getAttribute('width');
            if ($width < $this->_barcode->getWidth(true)) {
                #require_once 'Zend/Barcode/Renderer/Exception.php';
                throw new Zend_Barcode_Renderer_Exception(
                    'Barcode is define outside the image (width)'
                );
            }
        } else {
            if ($this->_userWidth) {
                $width = (float) $this->_barcode->getWidth(true);
                if ($this->_userWidth < $width) {
                    #require_once 'Zend/Barcode/Renderer/Exception.php';
                    throw new Zend_Barcode_Renderer_Exception(sprintf(
                        "Barcode is define outside the image (calculated: '%d', provided: '%d')",
                        $width,
                        $this->_userWidth
                    ));
                }
            }
        }
    }

    /**
     * Draw the barcode in the rendering resource
     * @return mixed
     */
    public function draw()
    {
        parent::draw();
        $this->_resource->appendChild($this->_rootElement);
        return $this->_resource;
    }

    /**
     * Draw and render the barcode with correct headers
     *
     * @return mixed
     */
    public function render()
    {
        $this->draw();
        header("Content-Type: image/svg+xml");
        echo $this->_resource->saveXML();
    }

    /**
     * Draw a polygon in the svg resource
     *
     * @param array $points
     * @param integer $color
     * @param boolean $filled
     */
    protected function _drawPolygon($points, $color, $filled = true)
    {
        $color = 'rgb(' . implode(', ', array(($color & 0xFF0000) >> 16,
                                              ($color & 0x00FF00) >> 8,
                                              ($color & 0x0000FF))) . ')';
        $orientation = $this->getBarcode()->getOrientation();
        $newPoints = array(
            $points[0][0] + $this->_leftOffset,
            $points[0][1] + $this->_topOffset,
            $points[1][0] + $this->_leftOffset,
            $points[1][1] + $this->_topOffset,
            $points[2][0] + $this->_leftOffset + cos(-$orientation),
            $points[2][1] + $this->_topOffset - sin($orientation),
            $points[3][0] + $this->_leftOffset + cos(-$orientation),
            $points[3][1] + $this->_topOffset - sin($orientation),
        );
        $newPoints = implode(' ', $newPoints);
        $attributes['points'] = $newPoints;
        $attributes['fill'] = $color;
        $this->_appendRootElement('polygon', $attributes);
    }

    /**
     * Draw a polygon in the svg resource
     *
     * @param string    $text
     * @param float     $size
     * @param array     $position
     * @param string    $font
     * @param integer   $color
     * @param string    $alignment
     * @param float|int $orientation
     */
    protected function _drawText($text, $size, $position, $font, $color, $alignment = 'center', $orientation = 0)
    {
        $color = 'rgb(' . implode(', ', array(($color & 0xFF0000) >> 16,
                                              ($color & 0x00FF00) >> 8,
                                              ($color & 0x0000FF))) . ')';
        $attributes['x'] = $position[0] + $this->_leftOffset;
        $attributes['y'] = $position[1] + $this->_topOffset;
        //$attributes['font-family'] = $font;
        $attributes['color'] = $color;
        $attributes['font-size'] = $size * 1.2;
        switch ($alignment) {
            case 'left':
                $textAnchor = 'start';
                break;
            case 'right':
                $textAnchor = 'end';
                break;
            case 'center':
            default:
                $textAnchor = 'middle';
        }
        $attributes['style'] = 'text-anchor: ' . $textAnchor;
        $attributes['transform'] = 'rotate('
                                 . (- $orientation)
                                 . ', '
                                 . ($position[0] + $this->_leftOffset)
                                 . ', ' . ($position[1] + $this->_topOffset)
                                 . ')';
        $this->_appendRootElement('text', $attributes, $text);
    }
}
