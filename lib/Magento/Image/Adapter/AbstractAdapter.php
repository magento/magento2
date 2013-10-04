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
 * @category   Magento
 * @package    \Magento\Image
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @file        Abstract.php
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Image\Adapter;

abstract class AbstractAdapter
{
    /**
     * Background color
     * @var mixed
     */
    public $imageBackgroundColor = 0;

    /**
     * Position constants
     */
    const POSITION_TOP_LEFT = 'top-left';
    const POSITION_TOP_RIGHT = 'top-right';
    const POSITION_BOTTOM_LEFT = 'bottom-left';
    const POSITION_BOTTOM_RIGHT = 'bottom-right';
    const POSITION_STRETCH = 'stretch';
    const POSITION_TILE = 'tile';
    const POSITION_CENTER = 'center';

    /**
     * Default font size
     */
    const DEFAULT_FONT_SIZE = 15;

    protected $_fileType;
    protected $_fileName ;
    protected $_fileMimeType;
    protected $_fileSrcName;
    protected $_fileSrcPath;
    protected $_imageHandler;
    protected $_imageSrcWidth;
    protected $_imageSrcHeight;
    protected $_requiredExtensions;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeigth;
    protected $_watermarkImageOpacity;
    protected $_quality;
    protected $_fontSize = self::DEFAULT_FONT_SIZE;

    protected $_keepAspectRatio;
    protected $_keepFrame;
    protected $_keepTransparency;
    protected $_backgroundColor;
    protected $_constrainOnly;

    /**
     * IO model to work with files
     * @var \Magento\Io\File
     */
    protected $_ioFile;

    abstract public function open($fileName);

    abstract public function save($destination = null, $newName = null);

    /**
     * Render image and return its binary contents
     *
     * @return string
     */
    abstract public function getImage();

    abstract public function resize($width=null, $height=null);

    abstract public function rotate($angle);

    abstract public function crop($top = 0, $left = 0, $right = 0, $bottom = 0);

    abstract public function watermark($imagePath, $positionX = 0, $positionY = 0, $opacity = 30, $tile = false);

    abstract public function checkDependencies();

    /**
     * Create Image from string
     *
     * @param string $text
     * @param string $font Path to font file
     * @return \Magento\Image\Adapter\AbstractAdapter
     */
    abstract public function createPngFromString($text, $font = '');

    /**
     * Reassign image dimensions
     */
    abstract public function refreshImageDimensions();

    /**
     * Returns rgba array of the specified pixel
     *
     * @param int $x
     * @param int $y
     * @return array
     */
    abstract public function getColorAt($x, $y);

    /**
     * Initialize default values
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_ioFile = isset($data['io']) ? $data['io'] : new \Magento\Io\File();
    }

    /**
     * Assign image width, height, fileType and fileMimeType to object properties
     * using getimagesize function
     *
     * @return int|null
     */
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

    /**
     * Set watermark position
     *
     * @return \Magento\Image\Adapter\AbstractAdapter
     */
    public function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        return $this;
    }

    /**
     * Get watermark position
     *
     * @return \Magento\Image\Adapter\AbstractAdapter
     */
    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    /**
     * Set watermark opacity
     *
     * @return \Magento\Image\Adapter\AbstractAdapter
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_watermarkImageOpacity = $imageOpacity;
        return $this;
    }

    /**
     * Get watermark opacity
     *
     * @return int
     */
    public function getWatermarkImageOpacity()
    {
        return $this->_watermarkImageOpacity;
    }

    /**
     * Set watermark width
     *
     * @return \Magento\Image\Adapter\AbstractAdapter
     */
    public function setWatermarkWidth($width)
    {
        $this->_watermarkWidth = $width;
        return $this;
    }

    /**
     * Get watermark width
     *
     * @return int
     */
    public function getWatermarkWidth()
    {
        return $this->_watermarkWidth;
    }

    /**
     * Set watermark height
     *
     * @return \Magento\Image\Adapter\AbstractAdapter
     */
    public function setWatermarkHeight($heigth)
    {
        $this->_watermarkHeigth = $heigth;
        return $this;
    }

    /**
     * Return watermark height
     *
     * @return int
     */
    public function getWatermarkHeight()
    {
        return $this->_watermarkHeigth;
    }


    /**
     * Get/set keepAspectRatio
     *
     * @param bool $value
     * @return bool|\Magento\Image\Adapter\AbstractAdapter
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

    /**
     * Assign file dirname and basename to object properties
     *
     */
    protected function _getFileAttributes()
    {
        $pathinfo = pathinfo($this->_fileName);

        $this->_fileSrcPath = $pathinfo['dirname'];
        $this->_fileSrcName = $pathinfo['basename'];
    }

    /**
     * Adapt resize values based on image configuration
     *
     * @throws \Exception
     * @param int $frameWidth
     * @param int $frameHeight
     * @return array
     */
    protected function _adaptResizeValues($frameWidth, $frameHeight)
    {
        $this->_checkDimensions($frameWidth, $frameHeight);

        // calculate lacking dimension
        if (!$this->_keepFrame && $this->_checkSrcDimensions()) {
            if (null === $frameWidth) {
                $frameWidth = round($frameHeight * ($this->_imageSrcWidth / $this->_imageSrcHeight));
            } elseif (null === $frameHeight) {
                $frameHeight = round($frameWidth * ($this->_imageSrcHeight / $this->_imageSrcWidth));
            }
        } else {
            if (null === $frameWidth) {
                $frameWidth = $frameHeight;
            } elseif (null === $frameHeight) {
                $frameHeight = $frameWidth;
            }
        }

        // define coordinates of image inside new frame
        $srcX = 0;
        $srcY = 0;
        list($dstWidth, $dstHeight) = $this->_checkAspectRatio($frameWidth, $frameHeight);

        // define position in center
        // TODO: add positions option
        $dstY = round(($frameHeight - $dstHeight) / 2);
        $dstX = round(($frameWidth - $dstWidth) / 2);

        // get rid of frame (fallback to zero position coordinates)
        if (!$this->_keepFrame) {
            $frameWidth  = $dstWidth;
            $frameHeight = $dstHeight;
            $dstY = 0;
            $dstX = 0;
        }

        return array(
            'src' => array(
                'x' => $srcX,
                'y' => $srcY
            ),
            'dst' => array(
                'x' => $dstX,
                'y' => $dstY,
                'width'  => $dstWidth,
                'height' => $dstHeight
            ),
            'frame' => array( // size for new image
                'width'  => $frameWidth,
                'height' => $frameHeight
            )
        );
    }

    /**
     * Check aspect ratio
     *
     * @param int $frameWidth
     * @param int $frameHeight
     * @return array
     */
    protected function _checkAspectRatio($frameWidth, $frameHeight)
    {
        $dstWidth  = $frameWidth;
        $dstHeight = $frameHeight;
        if ($this->_keepAspectRatio && $this->_checkSrcDimensions()) {
            // do not make picture bigger, than it is, if required
            if ($this->_constrainOnly) {
                if (($frameWidth >= $this->_imageSrcWidth) && ($frameHeight >= $this->_imageSrcHeight)) {
                    $dstWidth  = $this->_imageSrcWidth;
                    $dstHeight = $this->_imageSrcHeight;
                }
            }
            // keep aspect ratio
            if ($this->_imageSrcWidth / $this->_imageSrcHeight >= $frameWidth / $frameHeight) {
                $dstHeight = round(($dstWidth / $this->_imageSrcWidth) * $this->_imageSrcHeight);
            } else {
                $dstWidth = round(($dstHeight / $this->_imageSrcHeight) * $this->_imageSrcWidth);
            }
        }
        return array($dstWidth, $dstHeight);
    }

    /**
     * Check Frame dimensions and throw exception if they are not valid
     *
     * @param int $frameWidth
     * @param int $frameHeight
     * @throws \Exception
     */
    protected function _checkDimensions($frameWidth, $frameHeight)
    {
        if ($frameWidth !== null && $frameWidth <= 0
            || $frameHeight !== null && $frameHeight <= 0
            || empty($frameWidth) && empty($frameHeight)
        ) {
            throw new \Exception('Invalid image dimensions.');
        }
    }

    /**
     * Return false if source width or height is empty
     *
     * @return bool
     */
    protected function _checkSrcDimensions()
    {
        return !empty($this->_imageSrcWidth) && !empty($this->_imageSrcHeight);
    }

    /**
     * Return information about image using getimagesize function
     *
     * @param string $filePath
     * @return array
     */
    protected function _getImageOptions($filePath)
    {
        return getimagesize($filePath);
    }

    /**
     * Return supported image formats
     *
     * @return array
     */
    public function getSupportedFormats()
    {
        return array('gif', 'jpeg', 'jpg', 'png');
    }

    /**
     * Create destination folder if not exists and return full file path
     *
     * @throws \Exception
     * @param string $destination
     * @param string $newName
     * @return string
     */
    protected function _prepareDestination($destination = null, $newName = null)
    {
        if (empty($destination)) {
            $destination = $this->_fileSrcPath;
        } else {
            if (empty($newName)) {
                $info = pathinfo($destination);
                $newName = $info['basename'];
                $destination = $info['dirname'];
            }
        }

        if (empty($newName)) {
            $newFileName = $this->_fileSrcName;
        } else {
            $newFileName = $newName;
        }
        $fileName = $destination . DIRECTORY_SEPARATOR . $newFileName;

        if (!is_writable($destination)) {
            try {
                $result = $this->_ioFile->mkdir($destination);
            } catch (\Exception $e) {
                $result = false;
            }

            if (!$result) {
                throw new \Exception('Unable to write file into directory ' . $destination . '. Access forbidden.');
            }
        }

        return $fileName;
    }

    /**
     * Checks is adapter can work with image
     *
     * @return bool
     */
    protected function _canProcess()
    {
        return !empty($this->_fileName);
    }

    /**
     * Check - is this file an image
     *
     * @param string $filePath
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateUploadFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException ("File '{$filePath}' does not exists.");
        }
        if (!getimagesize($filePath)) {
            throw new \InvalidArgumentException ('Disallowed file type.');
        }
        $this->checkDependencies();
        $this->open($filePath);

        return $this->getMimeType() !== null;
    }
}
