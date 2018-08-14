<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Image handler library
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Image
{
    /**
     * @var Image\Adapter\AdapterInterface
     */
    protected $_adapter;

    /**
     * @var null|string
     */
    protected $_fileName;

    /**
     * Constructor
     *
     * @param Image\Adapter\AdapterInterface $adapter
     * @param string|null $fileName
     */
    public function __construct(\Magento\Framework\Image\Adapter\AdapterInterface $adapter, $fileName = null)
    {
        $this->_adapter = $adapter;
        $this->_fileName = $fileName;
        if (isset($fileName)) {
            $this->open();
        }
    }

    /**
     * Opens an image and creates image handle
     *
     * @access public
     * @return void
     * @throws \Exception
     */
    public function open()
    {
        $this->_adapter->checkDependencies();

        if (!file_exists($this->_fileName)) {
            throw new \Exception("File '{$this->_fileName}' does not exist.");
        }

        $this->_adapter->open($this->_fileName);
    }

    /**
     * Display handled image in your browser
     *
     * @access public
     * @return void
     */
    public function display()
    {
        $this->_adapter->display();
    }

    /**
     * Save handled image into file
     *
     * @param null|string $destination Default value is NULL
     * @param null|string $newFileName Default value is NULL
     * @access public
     * @return void
     */
    public function save($destination = null, $newFileName = null)
    {
        $this->_adapter->save($destination, $newFileName);
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
        $this->_adapter->rotate($angle);
    }

    /**
     * Crop an image.
     *
     * @param int $top  Default value is 0
     * @param int $left Default value is 0
     * @param int $right Default value is 0
     * @param int $bottom Default value is 0
     * @access public
     * @return void
     */
    public function crop($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        $this->_adapter->crop($top, $left, $right, $bottom);
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
        $this->_adapter->resize($width, $height);
    }

    /**
     * Get/set keepAspectRatio
     *
     * @param bool $value
     * @return bool
     */
    public function keepAspectRatio($value)
    {
        return $this->_adapter->keepAspectRatio($value);
    }

    /**
     * Get/set keepFrame
     *
     * @param bool $value
     * @return bool
     */
    public function keepFrame($value)
    {
        return $this->_adapter->keepFrame($value);
    }

    /**
     * Get/set keepTransparency
     *
     * @param bool $value
     * @return bool
     */
    public function keepTransparency($value)
    {
        return $this->_adapter->keepTransparency($value);
    }

    /**
     * Get/set constrainOnly
     *
     * @param bool $value
     * @return bool
     */
    public function constrainOnly($value)
    {
        return $this->_adapter->constrainOnly($value);
    }

    /**
     * Get/set backgroundColor
     *
     * @param null|array $value
     * @return array|null
     */
    public function backgroundColor($value)
    {
        return $this->_adapter->backgroundColor($value);
    }

    /**
     * Get/set quality, values in percentage from 0 to 100
     *
     * @param int $value
     * @return int
     */
    public function quality($value)
    {
        return $this->_adapter->quality($value);
    }

    /**
     * Adds watermark to our image.
     *
     * @param string $watermarkImage Absolute path to watermark image.
     * @param int $positionX Watermark X position.
     * @param int $positionY Watermark Y position.
     * @param int $watermarkImageOpacity Watermark image opacity.
     * @param bool $repeat Enable or disable watermark brick.
     * @access public
     * @throws \Exception
     * @return void
     */
    public function watermark(
        $watermarkImage,
        $positionX = 0,
        $positionY = 0,
        $watermarkImageOpacity = 30,
        $repeat = false
    ) {
        if (!file_exists($watermarkImage)) {
            throw new \Exception("Required file '{$watermarkImage}' does not exists.");
        }
        $this->_adapter->watermark($watermarkImage, $positionX, $positionY, $watermarkImageOpacity, $repeat);
    }

    /**
     * Get mime type of handled image
     *
     * @access public
     * @return string
     */
    public function getMimeType()
    {
        return $this->_adapter->getMimeType();
    }

    /**
     * Get image type of handled image
     *
     * @access public
     * @return int
     */
    public function getImageType()
    {
        return $this->_adapter->getImageType();
    }

    /**
     * Process
     *
     * @access public
     * @return void
     */
    public function process()
    {
    }

    /**
     * Instruction
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
        $this->_adapter->imageBackgroundColor = intval($color);
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return $this
     */
    public function setWatermarkPosition($position)
    {
        $this->_adapter->setWatermarkPosition($position);
        return $this;
    }

    /**
     * Set watermark image opacity
     *
     * @param int $imageOpacity
     * @return $this
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_adapter->setWatermarkImageOpacity($imageOpacity);
        return $this;
    }

    /**
     * Set watermark width
     *
     * @param int $width
     * @return $this
     */
    public function setWatermarkWidth($width)
    {
        $this->_adapter->setWatermarkWidth($width);
        return $this;
    }

    /**
     * Set watermark height
     *
     * @param int $height
     * @return $this
     */
    public function setWatermarkHeight($height)
    {
        $this->_adapter->setWatermarkHeight($height);
        return $this;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->_adapter->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->_adapter->getOriginalHeight();
    }

    /**
     * Create Image from string
     *
     * @param string $text
     * @param string $font Path to font file
     * @return $this
     */
    public function createPngFromString($text, $font = '')
    {
        $this->_adapter->createPngFromString($text, $font);
        return $this;
    }
}
