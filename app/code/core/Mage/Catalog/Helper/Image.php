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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog image helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Image extends Mage_Core_Helper_Abstract
{
    protected $_model;
    protected $_scheduleResize = false;
    protected $_scheduleRotate = false;
    protected $_angle;

    protected $_watermark;
    protected $_watermarkPosition;
    protected $_watermarkSize;
    protected $_watermarkImageOpacity;

    protected $_product;
    protected $_imageFile;
    protected $_placeholder;

    /**
     * Reset all previos data
     */
    protected function _reset()
    {
        $this->_model = null;
        $this->_scheduleResize = false;
        $this->_scheduleRotate = false;
        $this->_angle = null;
        $this->_watermark = null;
        $this->_watermarkPosition = null;
        $this->_watermarkSize = null;
        $this->_watermarkImageOpacity = null;
        $this->_product = null;
        $this->_imageFile = null;
        return $this;
    }

    public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile=null)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('Mage_Catalog_Model_Product_Image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setProduct($product);

        $this->setWatermark(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_image"));
        $this->setWatermarkImageOpacity(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity"));
        $this->setWatermarkPosition(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_position"));
        $this->setWatermarkSize(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_size"));

        if ($imageFile) {
            $this->setImageFile($imageFile);
        }
        else {
            // add for work original size
            $this->_getModel()->setBaseFile( $this->getProduct()->getData($this->_getModel()->getDestinationSubdir()) );
        }
        return $this;
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be null - in this case, lacking dimension will be calculated.
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param int $width
     * @param int $height
     * @return Mage_Catalog_Helper_Image
     */
    public function resize($width, $height = null)
    {
        $this->_getModel()->setWidth($width)->setHeight($height);
        $this->_scheduleResize = true;
        return $this;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality
     * @return Mage_Catalog_Helper_Image
     */
    public function setQuality($quality)
    {
        $this->_getModel()->setQuality($quality);
        return $this;
    }

    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param bool $flag
     * @return Mage_Catalog_Helper_Image
     */
    public function keepAspectRatio($flag)
    {
        $this->_getModel()->setKeepAspectRatio($flag);
        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     *
     * $position - TODO, not used for now - picture position inside the frame.
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param bool $flag
     * @param array $position
     * @return Mage_Catalog_Helper_Image
     */
    public function keepFrame($flag, $position = array('center', 'middle'))
    {
        $this->_getModel()->setKeepFrame($flag);
        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param bool $flag
     * @param int $alphaOpacity
     * @return Mage_Catalog_Helper_Image
     */
    public function keepTransparency($flag, $alphaOpacity = null)
    {
        $this->_getModel()->setKeepTransparency($flag);
        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     *
     * @param bool $flag
     * @return Mage_Catalog_Helper_Image
     */
    public function constrainOnly($flag)
    {
        $this->_getModel()->setConstrainOnly($flag);
        return $this;
    }

    /**
     * Set color to fill image frame with.
     * Applicable before calling resize()
     * The keepTransparency(true) overrides this (if image has transparent color)
     * It is white by default.
     *
     * @see Mage_Catalog_Model_Product_Image
     * @param array $colorRGB
     * @return Mage_Catalog_Helper_Image
     */
    public function backgroundColor($colorRGB)
    {
        // assume that 3 params were given instead of array
        if (!is_array($colorRGB)) {
            $colorRGB = func_get_args();
        }
        $this->_getModel()->setBackgroundColor($colorRGB);
        return $this;
    }

    public function rotate($angle)
    {
        $this->setAngle($angle);
        $this->_getModel()->setAngle($angle);
        $this->_scheduleRotate = true;
        return $this;
    }

    /**
     * Add watermark to image
     * size param in format 100x200
     *
     * @param string $fileName
     * @param string $position
     * @param string $size
     * @param int $imageOpacity
     * @return Mage_Catalog_Helper_Image
     */
    public function watermark($fileName, $position, $size=null, $imageOpacity=null)
    {
        $this->setWatermark($fileName)
            ->setWatermarkPosition($position)
            ->setWatermarkSize($size)
            ->setWatermarkImageOpacity($imageOpacity);
        return $this;
    }

    public function placeholder($fileName)
    {
        $this->_placeholder = $fileName;
    }

    public function getPlaceholder()
    {
        if (!$this->_placeholder) {
            $attr = $this->_getModel()->getDestinationSubdir();
            $this->_placeholder = 'Mage_Catalog::images/product/placeholder/' . $attr . '.jpg';
        }
        return $this->_placeholder;
    }

    public function __toString()
    {
        try {
            if( $this->getImageFile() ) {
                $this->_getModel()->setBaseFile( $this->getImageFile() );
            } else {
                $this->_getModel()->setBaseFile( $this->getProduct()->getData($this->_getModel()->getDestinationSubdir()) );
            }

            if( $this->_getModel()->isCached() ) {
                return $this->_getModel()->getUrl();
            } else {
                if( $this->_scheduleRotate ) {
                    $this->_getModel()->rotate( $this->getAngle() );
                }

                if ($this->_scheduleResize) {
                    $this->_getModel()->resize();
                }

                if( $this->getWatermark() ) {
                    $this->_getModel()->setWatermark($this->getWatermark());
                }

                $url = $this->_getModel()->saveFile()->getUrl();
            }
        } catch( Exception $e ) {
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }
        return $url;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Catalog_Helper_Image
     */
    protected function _setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Catalog_Model_Product_Image
     */
    protected function _getModel()
    {
        return $this->_model;
    }

    protected function setAngle($angle)
    {
        $this->_angle = $angle;
        return $this;
    }

    protected function getAngle()
    {
        return $this->_angle;
    }

    /**
     * Set watermark file name
     *
     * @param string $watermark
     * @return Mage_Catalog_Helper_Image
     */
    protected function setWatermark($watermark)
    {
        $this->_watermark = $watermark;
        $this->_getModel()->setWatermarkFile($watermark);
        return $this;
    }

    /**
     * Get watermark file name
     *
     * @return string
     */
    protected function getWatermark()
    {
        return $this->_watermark;
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return Mage_Catalog_Helper_Image
     */
    protected function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        $this->_getModel()->setWatermarkPosition($position);
        return $this;
    }

    /**
     * Get watermark position
     *
     * @return string
     */
    protected function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    /**
     * Set watermark size
     * param size in format 100x200
     *
     * @param string $size
     * @return Mage_Catalog_Helper_Image
     */
    public function setWatermarkSize($size)
    {
        $this->_watermarkSize = $size;
        $this->_getModel()->setWatermarkSize($this->parseSize($size));
        return $this;
    }

    /**
     * Get watermark size
     *
     * @return string
     */
    protected function getWatermarkSize()
    {
        return $this->_watermarkSize;
    }

    /**
     * Set watermark image opacity
     *
     * @param int $imageOpacity
     * @return Mage_Catalog_Helper_Image
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_watermarkImageOpacity = $imageOpacity;
        $this->_getModel()->setWatermarkImageOpacity($imageOpacity);
        return $this;
    }

    /**
     * Get watermark image opacity
     *
     * @return int
     */
    protected function getWatermarkImageOpacity()
    {
        if( $this->_watermarkImageOpacity ) {
            return $this->_watermarkImageOpacity;
        }

        return $this->_getModel()->getWatermarkImageOpacity();
    }

    protected function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    protected function getProduct()
    {
        return $this->_product;
    }

    protected function setImageFile($file)
    {
        $this->_imageFile = $file;
        return $this;
    }

    protected function getImageFile()
    {
        return $this->_imageFile;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    protected function parseSize($string)
    {
        $size = explode('x', strtolower($string));
        if( sizeof($size) == 2 ) {
            return array(
                'width' => ($size[0] > 0) ? $size[0] : null,
                'heigth' => ($size[1] > 0) ? $size[1] : null,
            );
        }
        return false;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalHeight();
    }

    /**
     * Retrieve Original image size as array
     * 0 - width, 1 - height
     *
     * @return array
     */
    public function getOriginalSizeArray()
    {
        return array(
            $this->getOriginalWidth(),
            $this->getOriginalHeight()
        );
    }

    /**
     * Check - is this file an image
     *
     * @param string $filePath
     * @return bool
     * @throw Mage_Core_Exception
     */
    public function validateUploadFile($filePath) {
        if (!getimagesize($filePath)) {
            Mage::throwException($this->__('Disallowed file type.'));
        }
        return true;
    }

}
