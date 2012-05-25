<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Image
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Image handler library
 *
 * @category   Varien
 * @package    Varien_Image
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Image
{
    protected $_adapter;

    protected $_fileName;

    /**
     * Constructor
     *
     * @param Varien_Image_Adapter $adapter. Default value is GD2
     * @param string $fileName
     * @return void
     */
    function __construct($fileName=null, $adapter=Varien_Image_Adapter::ADAPTER_GD2)
    {
        $this->_getAdapter($adapter);
        $this->_fileName = $fileName;
        if( isset($fileName) ) {
            $this->open();
        }
    }

    /**
     * Opens an image and creates image handle
     *
     * @access public
     * @return void
     */
    public function open()
    {
        $this->_getAdapter()->checkDependencies();

        if( !file_exists($this->_fileName) ) {
            throw new Exception("File '{$this->_fileName}' does not exists.");
        }

        $this->_getAdapter()->open($this->_fileName);
    }

    /**
     * Display handled image in your browser
     *
     * @access public
     * @return void
     */
    public function display()
    {
        $this->_getAdapter()->display();
    }

    /**
     * Save handled image into file
     *
     * @param string $destination. Default value is NULL
     * @param string $newFileName. Default value is NULL
     * @access public
     * @return void
     */
    public function save($destination=null, $newFileName=null)
    {
        $this->_getAdapter()->save($destination, $newFileName);
    }

    /**
     * Rotate an image.
     *
     * @param int $angle
     * @access public
     * @return void
     */
    public function rotate($angle)
    {
        $this->_getAdapter()->rotate($angle);
    }

    /**
     * Crop an image.
     *
     * @param int $top. Default value is 0
     * @param int $left. Default value is 0
     * @param int $right. Default value is 0
     * @param int $bottom. Default value is 0
     * @access public
     * @return void
     */
    public function crop($top=0, $left=0, $right=0, $bottom=0)
    {
        $this->_getAdapter()->crop($top, $left, $right, $bottom);
    }

    /**
     * Resize an image
     *
     * @param int $width
     * @param int $height
     * @access public
     * @return void
     */
    public function resize($width, $height = null)
    {
        $this->_getAdapter()->resize($width, $height);
    }

    public function keepAspectRatio($value)
    {
        return $this->_getAdapter()->keepAspectRatio($value);
    }

    public function keepFrame($value)
    {
        return $this->_getAdapter()->keepFrame($value);
    }

    public function keepTransparency($value)
    {
        return $this->_getAdapter()->keepTransparency($value);
    }

    public function constrainOnly($value)
    {
        return $this->_getAdapter()->constrainOnly($value);
    }

    public function backgroundColor($value)
    {
        return $this->_getAdapter()->backgroundColor($value);
    }

    /**
     * Get/set quality, values in percentage from 0 to 100
     *
     * @param int $value
     * @return int
     */
    public function quality($value)
    {
        return $this->_getAdapter()->quality($value);
    }

    /**
     * Adds watermark to our image.
     *
     * @param string $watermarkImage. Absolute path to watermark image.
     * @param int $positionX. Watermark X position.
     * @param int $positionY. Watermark Y position.
     * @param int $watermarkImageOpacity. Watermark image opacity.
     * @param bool $repeat. Enable or disable watermark brick.
     * @access public
     * @return void
     */
    public function watermark($watermarkImage, $positionX=0, $positionY=0, $watermarkImageOpacity=30, $repeat=false)
    {
        if( !file_exists($watermarkImage) ) {
            throw new Exception("Required file '{$watermarkImage}' does not exists.");
        }
        $this->_getAdapter()->watermark($watermarkImage, $positionX, $positionY, $watermarkImageOpacity, $repeat);
    }

    /**
     * Get mime type of handled image
     *
     * @access public
     * @return string
     */
    public function getMimeType()
    {
        return $this->_getAdapter()->getMimeType();
    }

    /**
     * process
     *
     * @access public
     * @return void
     */
    public function process()
    {

    }

    /**
     * instruction
     *
     * @access public
     * @return void
     */
    public function instruction()
    {

    }

    /**
     * Set image background color
     *
     * @param int $color
     * @access public
     * @return void
     */
    public function setImageBackgroundColor($color)
    {
        $this->_getAdapter()->imageBackgroundColor = intval($color);
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return Varien_Image
     */
    public function setWatermarkPosition($position)
    {
        $this->_getAdapter()->setWatermarkPosition($position);
        return $this;
    }

    /**
     * Set watermark image opacity
     *
     * @param int $imageOpacity
     * @return Varien_Image
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_getAdapter()->setWatermarkImageOpacity($imageOpacity);
        return $this;
    }

    /**
     * Set watermark width
     *
     * @param int $width
     * @return Varien_Image
     */
    public function setWatermarkWidth($width)
    {
        $this->_getAdapter()->setWatermarkWidth($width);
        return $this;
    }

    /**
     * Set watermark height
     *
     * @param int $height
     * @return Varien_Image
     */
    public function setWatermarkHeight($height)
    {
        $this->_getAdapter()->setWatermarkHeight($height);
        return $this;
    }

    /**
     * Retrieve image adapter object
     *
     * @param string $adapter
     * @return Varien_Image_Adapter_Abstract
     */
    protected function _getAdapter($adapter=null)
    {
        if( !isset($this->_adapter) ) {
            $this->_adapter = Varien_Image_Adapter::factory( $adapter );
        }
        return $this->_adapter;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->_getAdapter()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->_getAdapter()->getOriginalHeight();
    }
}
