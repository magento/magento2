<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Image\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Image abstract adapter
 *
 * @file        Abstract.php
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Background color
     * @var int|string
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

    /**
     * @var  int
     */
    protected $_fileType;

    /**
     * @var  string
     */
    protected $_fileName;

    /**
     * @var  string
     */
    protected $_fileMimeType;

    /**
     * @var  string
     */
    protected $_fileSrcName;

    /**
     * @var  string
     */
    protected $_fileSrcPath;

    /**
     * @var resource
     */
    protected $_imageHandler;

    /**
     * @var  int
     */
    protected $_imageSrcWidth;

    /**
     * @var  int
     */
    protected $_imageSrcHeight;

    /**
     * @var array
     */
    protected $_requiredExtensions;

    /**
     * @var  string
     */
    protected $_watermarkPosition;

    /**
     * @var  int
     */
    protected $_watermarkWidth;

    /**
     * @var  int
     */
    protected $_watermarkHeight;

    /**
     * @var  int
     */
    protected $_watermarkImageOpacity;

    /**
     * @var  int
     */
    protected $_quality;

    /**
     * @var int
     */
    protected $_fontSize = self::DEFAULT_FONT_SIZE;

    /**
     * @var  bool
     */
    protected $_keepAspectRatio;

    /**
     * @var  bool
     */
    protected $_keepFrame;

    /**
     * @var  bool
     */
    protected $_keepTransparency;

    /**
     * @var  array
     */
    protected $_backgroundColor;

    /**
     * @var  bool
     */
    protected $_constrainOnly;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $directoryWrite;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Open image for processing
     *
     * @param string $fileName
     * @return void
     */
    abstract public function open($fileName);

    /**
     * Save image to specific path.
     *
     * If some folders of the path do not exist they will be created.
     *
     * @param null|string $destination
     * @param null|string $newName
     * @return void
     * @throws \Exception  If destination path is not writable
     */
    abstract public function save($destination = null, $newName = null);

    /**
     * Render image and return its binary contents
     *
     * @return string
     */
    abstract public function getImage();

    /**
     * Change the image size
     *
     * @param null|int $width
     * @param null|int $height
     * @return void
     */
    abstract public function resize($width = null, $height = null);

    /**
     * Rotate image on specific angle
     *
     * @param int $angle
     * @return void
     */
    abstract public function rotate($angle);

    /**
     * Crop image
     *
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     * @return bool
     */
    abstract public function crop($top = 0, $left = 0, $right = 0, $bottom = 0);

    /**
     * Add watermark to image
     *
     * @param string $imagePath
     * @param int $positionX
     * @param int $positionY
     * @param int $opacity
     * @param bool $tile
     * @return void
     */
    abstract public function watermark($imagePath, $positionX = 0, $positionY = 0, $opacity = 30, $tile = false);

    /**
     * Checks required dependencies
     *
     * @return void
     * @throws \Exception If some of dependencies are missing
     */
    abstract public function checkDependencies();

    /**
     * Create Image from string
     *
     * @param string $text
     * @param string $font Path to font file
     * @return AbstractAdapter
     */
    abstract public function createPngFromString($text, $font = '');

    /**
     * Reassign image dimensions
     *
     * @return void
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
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_filesystem = $filesystem;
        $this->logger = $logger;
        $this->directoryWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
    }

    /**
     * Assign image width, height, fileMimeType to object properties
     *
     * @return string|null
     */
    public function getMimeType()
    {
        if ($this->_fileMimeType) {
            return $this->_fileMimeType;
        } else {
            $this->_fileMimeType = image_type_to_mime_type((int) $this->getImageType());
            return $this->_fileMimeType;
        }
    }

    /**
     * Assign image width, height, fileType to object properties using getimagesize function
     *
     * @return int|null
     */
    public function getImageType()
    {
        if ($this->_fileType) {
            return $this->_fileType;
        } else {
            if ($this->_canProcess()) {
                list($this->_imageSrcWidth, $this->_imageSrcHeight, $this->_fileType) = getimagesize($this->_fileName);
                return $this->_fileType;
            }
        }
        return null;
    }

    /**
     * Retrieve Original Image Width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        $this->getImageType();
        return $this->_imageSrcWidth;
    }

    /**
     * Retrieve Original Image Height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        $this->getImageType();
        return $this->_imageSrcHeight;
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return $this
     */
    public function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        return $this;
    }

    /**
     * Get watermark position
     *
     * @return string
     */
    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    /**
     * Set watermark opacity
     *
     * @param int $imageOpacity
     * @return $this
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
     * @param int $width
     * @return $this
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
     * @param int $height
     * @return $this
     */
    public function setWatermarkHeight($height)
    {
        $this->_watermarkHeight = $height;
        return $this;
    }

    /**
     * Return watermark height
     *
     * @return int
     */
    public function getWatermarkHeight()
    {
        return $this->_watermarkHeight;
    }

    /**
     * Get/set keepAspectRatio
     *
     * @param bool $value
     * @return bool|\Magento\Framework\Image\Adapter\AbstractAdapter
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
     * @param null|array $value
     * @return array|void
     */
    public function backgroundColor($value = null)
    {
        if (null !== $value) {
            if (!is_array($value) || 3 !== count($value)) {
                return;
            }
            foreach ($value as $color) {
                if (!is_integer($color) || $color < 0 || $color > 255) {
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
     * @return void
     */
    protected function _getFileAttributes()
    {
        $pathinfo = pathinfo((string) $this->_fileName);

        $this->_fileSrcPath = $pathinfo['dirname'];
        $this->_fileSrcName = $pathinfo['basename'];
    }

    /**
     * Adapt resize values based on image configuration
     *
     * @param int $frameWidth
     * @param int $frameHeight
     * @return array
     * @throws \Exception
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
            $frameWidth = $dstWidth;
            $frameHeight = $dstHeight;
            $dstY = 0;
            $dstX = 0;
        }

        return [
            'src' => ['x' => $srcX, 'y' => $srcY],
            'dst' => ['x' => $dstX, 'y' => $dstY, 'width' => $dstWidth, 'height' => $dstHeight],
            // size for new image
            'frame' => ['width' => $frameWidth, 'height' => $frameHeight]
        ];
    }

    /**
     * Check aspect ratio
     *
     * @param int $frameWidth
     * @param int $frameHeight
     * @return int[]
     */
    protected function _checkAspectRatio($frameWidth, $frameHeight)
    {
        $dstWidth = $frameWidth;
        $dstHeight = $frameHeight;
        if ($this->_keepAspectRatio && $this->_checkSrcDimensions()) {
            // do not make picture bigger, than it is, if required
            if ($this->_constrainOnly) {
                if ($frameWidth >= $this->_imageSrcWidth && $frameHeight >= $this->_imageSrcHeight) {
                    $dstWidth = $this->_imageSrcWidth;
                    $dstHeight = $this->_imageSrcHeight;
                }
            }
            // keep aspect ratio
            if ($this->_imageSrcWidth / $this->_imageSrcHeight >= $frameWidth / $frameHeight) {
                $dstHeight = round($dstWidth / $this->_imageSrcWidth * $this->_imageSrcHeight);
            } else {
                $dstWidth = round($dstHeight / $this->_imageSrcHeight * $this->_imageSrcWidth);
            }
        }
        return [$dstWidth, $dstHeight];
    }

    /**
     * Check Frame dimensions and throw exception if they are not valid
     *
     * @param int $frameWidth
     * @param int $frameHeight
     * @return void
     * @throws \Exception
     */
    protected function _checkDimensions($frameWidth, $frameHeight)
    {
        if ($frameWidth !== null && $frameWidth <= 0 ||
            $frameHeight !== null && $frameHeight <= 0 ||
            empty($frameWidth) && empty($frameHeight)
        ) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \InvalidArgumentException('Invalid image dimensions.');
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
     * @return string[]
     */
    public function getSupportedFormats()
    {
        return ['gif', 'jpeg', 'jpg', 'png'];
    }

    /**
     * Create destination folder if not exists and return full file path
     *
     * @param string $destination
     * @param string $newName
     * @return string
     * @throws \Exception
     */
    protected function _prepareDestination($destination = null, $newName = null)
    {
        if (empty($destination)) {
            $destination = $this->_fileSrcPath;
        } else {
            if (empty($newName)) {
                $info = pathinfo((string) $destination);
                $newName = $info['basename'];
                $destination = $info['dirname'];
            }
        }

        if (empty($newName)) {
            $newFileName = $this->_fileSrcName;
        } else {
            $newFileName = $newName;
        }
        $fileName = $destination . '/' . $newFileName;

        if (!is_writable($destination)) {
            try {
                $this->directoryWrite->create($this->directoryWrite->getRelativePath($destination));
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
                $this->logger->critical($e);
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \DomainException(
                    'Unable to write file into directory ' . $destination . '. Access forbidden.'
                );
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
        return !empty($this->_fileName) && filesize($this->_fileName) > 0;
    }

    /**
     * Check - is this file an image
     *
     * @param string $filePath
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @throws \BadFunctionCallException
     * @throws \RuntimeException
     * @throws \OverflowException
     */
    public function validateUploadFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('Upload file does not exist.');
        }

        if (filesize($filePath) === 0) {
            throw new \InvalidArgumentException('Wrong file size.');
        }

        try {
            $imageSize = getimagesize($filePath);
        } catch (\Exception $e) {
            $imageSize = false;
        }

        if (!$imageSize) {
            throw new \InvalidArgumentException('Disallowed file type.');
        }

        $this->checkDependencies();
        $this->open($filePath);

        return $this->getImageType() !== null;
    }
}
