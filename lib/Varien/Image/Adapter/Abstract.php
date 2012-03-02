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
 * @file        Abstract.php
 * @author      Magento Core Team <core@magentocommerce.com>
 */

abstract class Varien_Image_Adapter_Abstract
{
    public $fileName = null;
    public $imageBackgroundColor = 0;

    const POSITION_TOP_LEFT = 'top-left';
    const POSITION_TOP_RIGHT = 'top-right';
    const POSITION_BOTTOM_LEFT = 'bottom-left';
    const POSITION_BOTTOM_RIGHT = 'bottom-right';
    const POSITION_STRETCH = 'stretch';
    const POSITION_TILE = 'tile';
    const POSITION_CENTER = 'center';

    protected $_fileType = null;
    protected $_fileName = null;
    protected $_fileMimeType = null;
    protected $_fileSrcName = null;
    protected $_fileSrcPath = null;
    protected $_imageHandler = null;
    protected $_imageSrcWidth = null;
    protected $_imageSrcHeight = null;
    protected $_requiredExtensions = null;
    protected $_watermarkPosition = null;
    protected $_watermarkWidth = null;
    protected $_watermarkHeigth = null;
    protected $_watermarkImageOpacity = null;
    protected $_quality = null;

    protected $_keepAspectRatio;
    protected $_keepFrame;
    protected $_keepTransparency;
    protected $_backgroundColor;
    protected $_constrainOnly;

    abstract public function open($fileName);

    abstract public function save($destination=null, $newName=null);

    abstract public function display();

    abstract public function resize($width=null, $height=null);

    abstract public function rotate($angle);

    abstract public function crop($top=0, $left=0, $right=0, $bottom=0);

    abstract public function watermark($watermarkImage, $positionX=0, $positionY=0, $watermarkImageOpacity=30, $repeat=false);

    abstract public function checkDependencies();

    public function getMimeType()
    {
        if( $this->_fileType ) {
            return $this->_fileType;
        } else {
            list($this->_imageSrcWidth, $this->_imageSrcHeight, $this->_fileType, ) = getimagesize($this->_fileName);
            $this->_fileMimeType = image_type_to_mime_type($this->_fileType);
            return $this->_fileMimeType;
        }
    }

    /**
     * Retrieve Original Image Width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        $this->getMimeType();
        return $this->_imageSrcWidth;
    }

    /**
     * Retrieve Original Image Height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        $this->getMimeType();
        return $this->_imageSrcHeight;
    }

    public function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        return $this;
    }

    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_watermarkImageOpacity = $imageOpacity;
        return $this;
    }

    public function getWatermarkImageOpacity()
    {
        return $this->_watermarkImageOpacity;
    }

    public function setWatermarkWidth($width)
    {
        $this->_watermarkWidth = $width;
        return $this;
    }

    public function getWatermarkWidth()
    {
        return $this->_watermarkWidth;
    }

    public function setWatermarkHeigth($heigth)
    {
        $this->_watermarkHeigth = $heigth;
        return $this;
    }

    public function getWatermarkHeigth()
    {
        return $this->_watermarkHeigth;
    }


    /**
     * Get/set keepAspectRatio
     *
     * @param bool $value
     * @return bool|Varien_Image_Adapter_Abstract
     */
    public function keepAspectRatio($value = null)
    {
        if (null !== $value) {
            $this->_keepAspectRatio = (bool)$value;
        }
        return $this->_keepAspectRatio;
    }

    /**
     * Get/set keepFrame
     *
     * @param bool $value
     * @return bool
     */
    public function keepFrame($value = null)
    {
        if (null !== $value) {
            $this->_keepFrame = (bool)$value;
        }
        return $this->_keepFrame;
    }

    /**
     * Get/set keepTransparency
     *
     * @param bool $value
     * @return bool
     */
    public function keepTransparency($value = null)
    {
        if (null !== $value) {
            $this->_keepTransparency = (bool)$value;
        }
        return $this->_keepTransparency;
    }

    /**
     * Get/set constrainOnly
     *
     * @param bool $value
     * @return bool
     */
    public function constrainOnly($value = null)
    {
        if (null !== $value) {
            $this->_constrainOnly = (bool)$value;
        }
        return $this->_constrainOnly;
    }

    /**
     * Get/set quality, values in percentage from 0 to 100
     *
     * @param int $value
     * @return int
     */
    public function quality($value = null)
    {
        if (null !== $value) {
            $this->_quality = (int)$value;
        }
        return $this->_quality;
    }

    /**
     * Get/set keepBackgroundColor
     *
     * @param array $value
     * @return array
     */
    public function backgroundColor($value = null)
    {
        if (null !== $value) {
            if ((!is_array($value)) || (3 !== count($value))) {
                return;
            }
            foreach ($value as $color) {
                if ((!is_integer($color)) || ($color < 0) || ($color > 255)) {
                    return;
                }
            }
        }
        $this->_backgroundColor = $value;
        return $this->_backgroundColor;
    }

    protected function _getFileAttributes()
    {
        $pathinfo = pathinfo($this->_fileName);

        $this->_fileSrcPath = $pathinfo['dirname'];
        $this->_fileSrcName = $pathinfo['basename'];
    }
}