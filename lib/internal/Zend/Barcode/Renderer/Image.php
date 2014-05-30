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
 * @version    $Id: Image.php 22999 2010-09-23 19:43:14Z mikaelkael $
 */

/** @see Zend_Barcode_Renderer_RendererAbstract*/
#require_once 'Zend/Barcode/Renderer/RendererAbstract.php';

/**
 * Class for rendering the barcode as image
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Renderer_Image extends Zend_Barcode_Renderer_RendererAbstract
{
    /**
     * List of authorized output format
     * @var array
     */
    protected $_allowedImageType = array(
        'png',
        'jpeg',
        'gif',
    );

    /**
     * Image format
     * @var string
     */
    protected $_imageType = 'png';

    /**
     * Resource for the image
     * @var resource
     */
    protected $_resource = null;

    /**
     * Resource for the font and bars color of the image
     * @var integer
     */
    protected $_imageForeColor = null;

    /**
     * Resource for the background color of the image
     * @var integer
     */
    protected $_imageBackgroundColor = null;

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

    public function __construct($options = null)
    {
        if (!function_exists('gd_info')) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception('Zend_Barcode_Renderer_Image requires the GD extension');
        }

        parent::__construct($options);
    }

    /**
     * Set height of the result image
     * @param null|integer $value
     * @return Zend_Image_Barcode_Abstract
     * @throw Zend_Image_Barcode_Exception
     */
    public function setHeight($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Image height must be greater than or equals 0'
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
     * @return void
     */
    public function setWidth($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Image width must be greater than or equals 0'
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
     * @param resource $value
     * @return Zend_Barcode_Renderer
     * @throw Zend_Barcode_Renderer_Exception
     */
    public function setResource($image)
    {
        if (gettype($image) != 'resource' || get_resource_type($image) != 'gd') {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Invalid image resource provided to setResource()'
            );
        }
        $this->_resource = $image;
        return $this;
    }

    /**
     * Set the image type to produce (png, jpeg, gif)
     *
     * @param string $value
     * @return Zend_Barcode_RendererAbstract
     * @throw Zend_Barcode_Renderer_Exception
     */
    public function setImageType($value)
    {
        if ($value == 'jpg') {
            $value = 'jpeg';
        }

        if (!in_array($value, $this->_allowedImageType)) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(sprintf(
                'Invalid type "%s" provided to setImageType()',
                $value
            ));
        }

        $this->_imageType = $value;
        return $this;
    }

    /**
     * Retrieve the image type to produce
     *
     * @return string
     */
    public function getImageType()
    {
        return $this->_imageType;
    }

    /**
     * Initialize the image resource
     *
     * @return void
     */
    protected function _initRenderer()
    {
        if (!extension_loaded('gd')) {
            #require_once 'Zend/Barcode/Exception.php';
            $e = new Zend_Barcode_Exception(
                'Gd extension must be loaded to render barcode as image'
            );
            $e->setIsRenderable(false);
            throw $e;
        }

        $barcodeWidth  = $this->_barcode->getWidth(true);
        $barcodeHeight = $this->_barcode->getHeight(true);

        if ($this->_resource !== null) {
            $foreColor       = $this->_barcode->getForeColor();
            $backgroundColor = $this->_barcode->getBackgroundColor();
            $this->_imageBackgroundColor = imagecolorallocate(
                $this->_resource,
                ($backgroundColor & 0xFF0000) >> 16,
                ($backgroundColor & 0x00FF00) >> 8,
                $backgroundColor & 0x0000FF
            );
            $this->_imageForeColor = imagecolorallocate(
                $this->_resource,
                ($foreColor & 0xFF0000) >> 16,
                ($foreColor & 0x00FF00) >> 8,
                $foreColor & 0x0000FF
            );
        } else {
            $width = $barcodeWidth;
            $height = $barcodeHeight;
            if ($this->_userWidth && $this->_barcode->getType() != 'error') {
                $width = $this->_userWidth;
            }
            if ($this->_userHeight && $this->_barcode->getType() != 'error') {
                $height = $this->_userHeight;
            }

            $foreColor       = $this->_barcode->getForeColor();
            $backgroundColor = $this->_barcode->getBackgroundColor();
            $this->_resource = imagecreatetruecolor($width, $height);

            $this->_imageBackgroundColor = imagecolorallocate(
                $this->_resource,
                ($backgroundColor & 0xFF0000) >> 16,
                ($backgroundColor & 0x00FF00) >> 8,
                $backgroundColor & 0x0000FF
            );
            $this->_imageForeColor = imagecolorallocate(
                $this->_resource,
                ($foreColor & 0xFF0000) >> 16,
                ($foreColor & 0x00FF00) >> 8,
                $foreColor & 0x0000FF
            );
            $white = imagecolorallocate($this->_resource, 255, 255, 255);
            imagefilledrectangle($this->_resource, 0, 0, $width - 1, $height - 1, $white);
        }
        $this->_adjustPosition(imagesy($this->_resource), imagesx($this->_resource));
        imagefilledrectangle(
            $this->_resource,
            $this->_leftOffset,
            $this->_topOffset,
            $this->_leftOffset + $barcodeWidth - 1,
            $this->_topOffset + $barcodeHeight - 1,
            $this->_imageBackgroundColor
        );
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
     */
    protected function _checkDimensions()
    {
        if ($this->_resource !== null) {
            if (imagesy($this->_resource) < $this->_barcode->getHeight(true)) {
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
            if (imagesx($this->_resource) < $this->_barcode->getWidth(true)) {
                #require_once 'Zend/Barcode/Renderer/Exception.php';
                throw new Zend_Barcode_Renderer_Exception(
                    'Barcode is define outside the image (width)'
                );
            }
        } else {
            if ($this->_userWidth) {
                $width = $this->_barcode->getWidth(true);
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
     * Draw and render the barcode with correct headers
     *
     * @return mixed
     */
    public function render()
    {
        $this->draw();
        header("Content-Type: image/" . $this->_imageType);
        $functionName = 'image' . $this->_imageType;
        call_user_func($functionName, $this->_resource);
        @imagedestroy($this->_resource);
    }

    /**
     * Draw a polygon in the image resource
     *
     * @param array $points
     * @param integer $color
     * @param boolean $filled
     */
    protected function _drawPolygon($points, $color, $filled = true)
    {
        $newPoints = array(
            $points[0][0] + $this->_leftOffset,
            $points[0][1] + $this->_topOffset,
            $points[1][0] + $this->_leftOffset,
            $points[1][1] + $this->_topOffset,
            $points[2][0] + $this->_leftOffset,
            $points[2][1] + $this->_topOffset,
            $points[3][0] + $this->_leftOffset,
            $points[3][1] + $this->_topOffset,
        );

        $allocatedColor = imagecolorallocate(
            $this->_resource,
            ($color & 0xFF0000) >> 16,
            ($color & 0x00FF00) >> 8,
            $color & 0x0000FF
        );

        if ($filled) {
            imagefilledpolygon($this->_resource, $newPoints, 4, $allocatedColor);
        } else {
            imagepolygon($this->_resource, $newPoints, 4, $allocatedColor);
        }
    }

    /**
     * Draw a polygon in the image resource
     *
     * @param string $text
     * @param float $size
     * @param array $position
     * @param string $font
     * @param integer $color
     * @param string $alignment
     * @param float $orientation
     */
    protected function _drawText($text, $size, $position, $font, $color, $alignment = 'center', $orientation = 0)
    {
        $allocatedColor = imagecolorallocate(
            $this->_resource,
            ($color & 0xFF0000) >> 16,
            ($color & 0x00FF00) >> 8,
            $color & 0x0000FF
        );

        if ($font == null) {
            $font = 3;
        }
        $position[0] += $this->_leftOffset;
        $position[1] += $this->_topOffset;

        if (is_numeric($font)) {
            if ($orientation) {
                /**
                 * imagestring() doesn't allow orientation, if orientation
                 * needed: a TTF font is required.
                 * Throwing an exception here, allow to use automaticRenderError
                 * to informe user of the problem instead of simply not drawing
                 * the text
                 */
                #require_once 'Zend/Barcode/Renderer/Exception.php';
                throw new Zend_Barcode_Renderer_Exception(
                    'No orientation possible with GD internal font'
                );
            }
            $fontWidth = imagefontwidth($font);
            $positionY = $position[1] - imagefontheight($font) + 1;
            switch ($alignment) {
                case 'left':
                    $positionX = $position[0];
                    break;
                case 'center':
                    $positionX = $position[0] - ceil(($fontWidth * strlen($text)) / 2);
                    break;
                case 'right':
                    $positionX = $position[0] - ($fontWidth * strlen($text));
                    break;
            }
            imagestring($this->_resource, $font, $positionX, $positionY, $text, $color);
        } else {

            if (!function_exists('imagettfbbox')) {
                #require_once 'Zend/Barcode/Renderer/Exception.php';
                throw new Zend_Barcode_Renderer_Exception(
                    'A font was provided, but this instance of PHP does not have TTF (FreeType) support'
                    );
            }

            $box = imagettfbbox($size, 0, $font, $text);
            switch ($alignment) {
                case 'left':
                    $width = 0;
                    break;
                case 'center':
                    $width = ($box[2] - $box[0]) / 2;
                    break;
                case 'right':
                    $width = ($box[2] - $box[0]);
                    break;
            }
            imagettftext(
                $this->_resource,
                $size,
                $orientation,
                $position[0] - ($width * cos(pi() * $orientation / 180)),
                $position[1] + ($width * sin(pi() * $orientation / 180)),
                $allocatedColor,
                $font,
                $text
            );
        }
    }
}
