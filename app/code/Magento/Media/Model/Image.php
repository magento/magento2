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
 * @category    Magento
 * @package     Magento_Media
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Media library Image model
 *
 * @category   Magento
 * @package    Magento_Media
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Media\Model;

class Image extends \Magento\Core\Model\AbstractModel
{
    /**
     * Image config instance
     *
     * @var \Magento\Media\Model\Image\Config\ConfigInterface
     */
    protected $_config;

    /**
     * Image resource
     *
     * @var resource
     */
    protected $_image;

    /**
     * Tmp image resource
     *
     * @var resource
     */
    protected $_tmpImage;

    /**
     * Params for filename generation
     *
     * @var array
     */
    protected $_params = array();


    protected function _construct()
    {
        $this->_init('Magento\Media\Model\File\Image');
    }

    /**
     * Set media image config instance
     *
     * @param \Magento\Media\Model\Image\Config\ConfigInterface $config
     * @return unknown
     */
    public function setConfig(\Magento\Media\Model\Image\Config\ConfigInterface $config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * Retrive media image config instance
     *
     * @return \Magento\Media\Model\Image\Config\ConfigInterface
     */
    public function getConfig()
    {
        return $this->_config;
    }

    public function getImage()
    {
        if (is_null($this->_image)) {
            $this->_image = $this->_getResource()->getImage($this);
        }

        return $this->_image;
    }

    public function getTmpImage()
    {
        if (is_null($this->_image)) {
            $this->_tmpImage = $this->_getResource()->getTmpImage($this);
        }

        return $this->_tmpImage;
    }

    /**
     * Retrive source dimensions object
     *
     * @return \Magento\Object
     */
    public function getDimensions()
    {
        if (!$this->getData('dimensions')) {
            $this->setData('dimensions', $this->_getResource()->getDimensions($this));
        }
        return $this->getData('dimensions');
    }

    /**
     * Retrive destanation dimensions object
     *
     * @return \Magento\Object
     */
    public function getDestanationDimensions()
    {
        if (!$this->getData('destanation_dimensions')) {
            $this->setData('destanation_dimensions', clone $this->getDimensions());
        }

        return $this->getData('destanation_dimensions');
    }

    public function getExtension()
    {
        return substr($this->getFileName(), strrpos($this->getFileName(), '.') + 1);
    }

    public function getFilePath($useParams = false)
    {
        if ($useParams && sizeof($this->getParams())) {
            $changes = '.' . $this->getParamsSum();
        } else {
            $changes = '';
        }

        return $this->getConfig()->getBaseMediaPath() . DS . $this->getName() . $changes . '.'
             . ( ( $useParams && $this->getParam('extension')) ? $this->getParam('extension') : $this->getExtension() );
    }

    public function getFileUrl($useParams = false)
    {
        if ($useParams && sizeof($this->getParams())) {
            $changes = '.' . $this->getParamsSum();
        } else {
            $changes = '';
        }

        return $this->getConfig()->getBaseMediaUrl() . '/' . $this->getName() . $changes . '.'
             . ( ( $useParams && $this->getParam('extension')) ? $this->getParam('extension') : $this->getExtension() );
    }

    public function getName()
    {
        return substr($this->getFileName(), 0, strrpos($this->getFileName(), '.'));
    }

    public function addParam($param, $value = null)
    {
        if (is_array($param)) {
            $this->_params = array_merge($this->_params, $param);
        } else {
            $this->_params[$param] = $value;
        }

        return $this;
    }

    public function setParam($param, $value = null)
    {
        if (is_array($param)) {
            $this->_params = $param;
        } else {
            $this->_params[$param] = $value;
        }

        return $this;
    }

    public function getParam($param)
    {
        if (isset($this->_params[$param])) {
            return $this->_params[$param];
        }

        return null;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getParamsSum()
    {
        return md5(serialize($this->_params));
    }

    /**
     * Return special link (with creating image if not exists)
     *
     * @param string $file
     * @param string $size
     * @param string $extension
     * @param string $watermark
     * @return string
     */
    public function getSpecialLink($file, $size, $extension=null, $watermark=null)
    {
        $this->_removeResources();
        $this->setData(array());
        $this->setParam(array());
        $this->setFileName($file);

        $this->addParam('size', $size);
        $this->addParam('watermark', $watermark);
        $this->addParam('extension', $extension);

        if (!$this->hasSpecialImage()) {
            if (strpos($size, 'x') !== false) {
                list($width, $height) = explode('x', $size);
            } else {
                $width = $size;
                $height = $this->getDimensions()->getHeight();
            }

            $sizeHRate = $width / $this->getDimensions()->getWidth();
            $sizeVRate = $height / $this->getDimensions()->getHeight();

            $rate = min($sizeHRate, $sizeVRate);

            if ($rate > 1) { // If image smaller than needed
                $rate = 1;
            }

            $this->getDestanationDimensions()
                ->setWidth($rate*$this->getDimensions()->getWidth())
                ->setHeight($rate*$this->getDimensions()->getHeight());


            $this->_getResource()->resize($this);
            $this->_getResource()->watermark($this);
            $this->_getResource()->saveAs($this, $extension);
            $this->_removeResources();
        }

        return $this->getFileUrl(true);
    }

    public function hasSpecialImage()
    {
        return $this->_getResource()->hasSpecialImage($this);
    }

    protected function _removeResources()
    {
        if ($this->_image) {
            $this->_getResource()->destroyResource($this->_image);
            $this->_image = null;
        }

        if ($this->_tmpImage) {
            $this->_getResource()->destroyResource($this->_tmpImage);
            $this->_tmpImage = null;
        }
    }

}
