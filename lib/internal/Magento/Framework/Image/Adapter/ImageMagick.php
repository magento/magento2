<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Image\Adapter;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Image adapter from ImageMagick.
 */
class ImageMagick extends AbstractAdapter
{
    /**
     * The blur factor where > 1 is blurry, < 1 is sharp
     */
    public const BLUR_FACTOR = 0.7;

    /**
     * Error messages
     */
    public const ERROR_WATERMARK_IMAGE_ABSENT = 'Watermark Image absent.';

    public const ERROR_WRONG_IMAGE = 'Image is not readable or file name is empty.';

    /**
     * Options Container
     *
     * @var array
     */
    protected $_options = [
        'resolution' => ['x' => 72, 'y' => 72],
        'small_image' => ['width' => 300, 'height' => 300],
        'sharpen' => ['radius' => 4, 'deviation' => 1],
    ];

    /**
     * @var \Imagick
     */
    protected $_imageHandler;

    /**
     * Colorspace of the image
     *
     * @var int
     */
    private $colorspace = -1;

    /**
     * Set/get background color. Check Imagick::COLOR_* constants
     *
     * @param int|string|array $color
     * @return int
     */
    public function backgroundColor($color = null)
    {
        if ($color) {
            if (is_array($color)) {
                $color = "rgb(" . join(',', $color) . ")";
            }

            $pixel = new \ImagickPixel();
            if (is_numeric($color)) {
                $pixel->setColorValue($color, 1);
            } else {
                $pixel->setColor($color);
            }
            if ($this->_imageHandler) {
                $this->_imageHandler->setImageBackgroundColor($color);
            }
        } else {
            $pixel = $this->_imageHandler->getImageBackgroundColor();
        }

        $this->imageBackgroundColor = $pixel->getColorAsString();

        return $this->imageBackgroundColor;
    }

    /**
     * Open image for processing
     *
     * @param string $filename
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function open($filename)
    {
        if ($filename === null || !file_exists($filename)) {
            throw new FileSystemException(
                new Phrase('File "%1" does not exist.', [$filename])
            );
        }
        if (!empty($filename) && !$this->validateURLScheme($filename)) {
            throw new \InvalidArgumentException('Wrong file');
        }

        $this->_fileName = $filename;
        $this->_checkCanProcess();
        $this->_getFileAttributes();

        try {
            if (is_callable('exif_imagetype')) {
                $fileType = exif_imagetype($this->_fileName);

                if ($fileType === IMAGETYPE_ICO) {
                    $filename = 'ico:' . $this->_fileName;
                }
            }

            $this->_imageHandler = new \Imagick($filename);
        } catch (\ImagickException $e) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new LocalizedException(
                __('Unsupported image format. File: %1', $this->_fileName),
                $e,
                $e->getCode()
            );
        }

        $this->getColorspace();
        $this->maybeConvertColorspace();
        $this->backgroundColor();
        $this->getMimeType();
    }

    /**
     * Checks for invalid URL schema if it exists
     *
     * @param string $filename
     * @return bool
     */
    private function validateURLScheme(string $filename) : bool
    {
        $allowed_schemes = ['ftp', 'ftps', 'http', 'https'];
        $url = parse_url($filename);
        if ($url && isset($url['scheme']) && !in_array($url['scheme'], $allowed_schemes)) {
            return false;
        }

        return true;
    }

    /**
     * Save image to specific path.
     *
     * If some folders of path does not exist they will be created
     *
     * @param null|string $destination
     * @param null|string $newName
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException If destination path is not writable
     */
    public function save($destination = null, $newName = null)
    {
        $fileName = $this->_prepareDestination($destination, $newName);

        $this->_applyOptions();
        $this->_imageHandler->stripImage();
        $this->_imageHandler->writeImage($fileName);
    }

    /**
     * Apply options to image. Will be usable later when create an option container
     *
     * @return $this
     */
    protected function _applyOptions()
    {
        $this->_imageHandler->setImageCompressionQuality((int)$this->quality());
        $this->_imageHandler->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $this->_imageHandler->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
        $this->_imageHandler->setImageResolution(
            $this->_options['resolution']['x'],
            $this->_options['resolution']['y']
        );
        if (method_exists($this->_imageHandler, 'optimizeImageLayers')) {
            $this->_imageHandler->optimizeImageLayers();
        }

        return $this;
    }

    /**
     * Render image and return its binary contents
     *
     * @return string
     * @see \Magento\Framework\Image\Adapter\AbstractAdapter::getImage
     */
    public function getImage()
    {
        $this->_applyOptions();
        return $this->_imageHandler->getImageBlob();
    }

    /**
     * Change the image size.
     *
     * @param null|int $frameWidth
     * @param null|int $frameHeight
     * @return void
     * @throws \ImagickException
     */
    public function resize($frameWidth = null, $frameHeight = null)
    {
        $this->_checkCanProcess();
        $dims = $this->_adaptResizeValues($frameWidth, $frameHeight);

        $newImage = new \Imagick();
        $newImage->newImage(
            $dims['frame']['width'],
            $dims['frame']['height'],
            $this->_imageHandler->getImageBackgroundColor()
        );

        $this->_imageHandler->resizeImage(
            $dims['dst']['width'],
            $dims['dst']['height'],
            \Imagick::FILTER_CUBIC,
            self::BLUR_FACTOR
        );

        if ($this->_imageHandler->getImageWidth() < $this->_options['small_image']['width'] ||
            $this->_imageHandler->getImageHeight() < $this->_options['small_image']['height']
        ) {
            $this->_imageHandler->sharpenImage(
                $this->_options['sharpen']['radius'],
                $this->_options['sharpen']['deviation']
            );
        }

        $newImage->compositeImage(
            $this->_imageHandler,
            \Imagick::COMPOSITE_COPYOPACITY,
            $dims['dst']['x'],
            $dims['dst']['y']
        );

        $newImage->compositeImage(
            $this->_imageHandler,
            \Imagick::COMPOSITE_OVER,
            $dims['dst']['x'],
            $dims['dst']['y']
        );

        $newImage->setImageFormat($this->_imageHandler->getImageFormat());
        $this->_imageHandler->clear();
        $this->_imageHandler->destroy();
        $this->_imageHandler = $newImage;

        $this->refreshImageDimensions();
    }

    /**
     * Rotate image on specific angle
     *
     * @param int $angle
     * @return void
     */
    public function rotate($angle)
    {
        $this->_checkCanProcess();
        // compatibility with GD2 adapter
        $angle = 360 - $angle;
        $pixel = new \ImagickPixel();
        $pixel->setColor("rgb(" . $this->imageBackgroundColor . ")");

        $this->_imageHandler->rotateImage($pixel, $angle);
        $this->refreshImageDimensions();
    }

    /**
     * Crop image
     *
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     * @return bool
     */
    public function crop($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        if ($left == 0 && $top == 0 && $right == 0 && $bottom == 0 || !$this->_canProcess()) {
            return false;
        }

        $newWidth = $this->_imageSrcWidth - $left - $right;
        $newHeight = $this->_imageSrcHeight - $top - $bottom;

        $this->_imageHandler->cropImage($newWidth, $newHeight, $left, $top);
        $this->refreshImageDimensions();
        return true;
    }

    /**
     * Add watermark to image
     *
     * @param string $imagePath
     * @param int $positionX
     * @param int $positionY
     * @param int $opacity
     * @param bool $tile
     * @return void
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function watermark($imagePath, $positionX = 0, $positionY = 0, $opacity = 30, $tile = false)
    {
        if (empty($imagePath) || !file_exists($imagePath)) {
            throw new \LogicException(self::ERROR_WATERMARK_IMAGE_ABSENT);
        }
        $this->_checkCanProcess();

        $opacity = $this->getWatermarkImageOpacity() ? $this->getWatermarkImageOpacity() : $opacity;
        $opacity = (double) number_format($opacity / 100, 1);

        $watermark = new \Imagick($imagePath);

        $this->resizeWatermark($watermark);
        $this->handleWatermarkAlphaChannel($watermark);

        $compositeChannels = \Imagick::CHANNEL_ALL;
        $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_OPACITY);
        $compositeChannels &= ~(\Imagick::CHANNEL_OPACITY);

        switch ($this->getWatermarkPosition()) {
            case self::POSITION_STRETCH:
                $watermark->sampleImage($this->_imageSrcWidth, $this->_imageSrcHeight);
                break;
            case self::POSITION_CENTER:
                $positionX = ($this->_imageSrcWidth - $watermark->getImageWidth()) / 2;
                $positionY = ($this->_imageSrcHeight - $watermark->getImageHeight()) / 2;
                break;
            case self::POSITION_TOP_RIGHT:
                $positionX = $this->_imageSrcWidth - $watermark->getImageWidth();
                break;
            case self::POSITION_BOTTOM_RIGHT:
                $positionX = $this->_imageSrcWidth - $watermark->getImageWidth();
                $positionY = $this->_imageSrcHeight - $watermark->getImageHeight();
                break;
            case self::POSITION_BOTTOM_LEFT:
                $positionY = $this->_imageSrcHeight - $watermark->getImageHeight();
                break;
            case self::POSITION_TILE:
                $positionX = 0;
                $positionY = 0;
                $tile = true;
                break;
        }

        try {
            if ($tile) {
                $this->addTiledWatermark($positionX, $positionY, $watermark, $compositeChannels);
            } else {
                $this->addSingleWatermark($positionX, $positionY, $watermark, $compositeChannels);
            }
        } catch (\ImagickException $e) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new LocalizedException(
                __('Unable to create watermark.'),
                $e,
                $e->getCode()
            );
        }

        // merge layers
        $this->_imageHandler->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $watermark->clear();
        $watermark->destroy();
    }

    /**
     * Checks required dependencies
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException If some of dependencies are missing
     */
    public function checkDependencies()
    {
        if (!class_exists('Imagick', false)) {
            throw new LocalizedException(
                __('Required PHP extension \'Imagick\' was not loaded.')
            );
        }
    }

    /**
     * Reassign image dimensions
     *
     * @return void
     */
    public function refreshImageDimensions()
    {
        $this->_imageSrcWidth = $this->_imageHandler->getImageWidth();
        $this->_imageSrcHeight = $this->_imageHandler->getImageHeight();
        $this->_imageHandler->setImagePage($this->_imageSrcWidth, $this->_imageSrcHeight, 0, 0);
    }

    /**
     * Standard destructor. Destroy stored information about image
     */
    public function __destruct()
    {
        $this->destroy();
    }

    /**
     * Destroy stored information about image
     *
     * @return $this
     */
    public function destroy()
    {
        if (null !== $this->_imageHandler && $this->_imageHandler instanceof \Imagick) {
            $this->_imageHandler->clear();
            $this->_imageHandler->destroy();
            $this->_imageHandler = null;
        }
        return $this;
    }

    /**
     * Returns rgba array of the specified pixel
     *
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getColorAt($x, $y)
    {
        $pixel = $this->_imageHandler->getImagePixelColor($x, $y);

        $color = $pixel->getColor();
        $rgbaColor = [
            'red' => $color['r'],
            'green' => $color['g'],
            'blue' => $color['b'],
            'alpha' => (1 - $color['a']) * 127,
        ];
        return $rgbaColor;
    }

    /**
     * Check whether the adapter can work with the image
     *
     * @return true
     * @throws \LogicException
     */
    protected function _checkCanProcess()
    {
        if (!$this->_canProcess()) {
            throw new \LogicException(self::ERROR_WRONG_IMAGE);
        }
        return true;
    }

    /**
     * Create Image from string
     *
     * @param string $text
     * @param string $font
     * @return \Magento\Framework\Image\Adapter\AbstractAdapter
     */
    public function createPngFromString($text, $font = '')
    {
        $image = $this->_getImagickObject();
        $draw = $this->_getImagickDrawObject();
        $color = $this->_getImagickPixelObject('#000000');
        $background = $this->_getImagickPixelObject('#ffffff00');
        // Transparent

        if (!empty($font)) {
            if (method_exists($image, 'setFont')) {
                $image->setFont($font);
            } elseif (method_exists($draw, 'setFont')) {
                $draw->setFont($font);
            }
        }

        // Font size for ImageMagick is set in pixels, while the for GD2 it is in points. 3/4 is ratio between them
        $draw->setFontSize($this->_fontSize * 4 / 3);
        $draw->setFillColor($color);
        $draw->setStrokeAntialias(true);
        $draw->setTextAntialias(true);

        $metrics = $image->queryFontMetrics($draw, $text);

        $draw->annotation(0, $metrics['ascender'], $text);

        $height = abs($metrics['ascender']) + abs($metrics['descender']);
        $image->newImage($metrics['textWidth'], $height, $background);
        $this->_fileType = IMAGETYPE_PNG;
        $image->setImageFormat('png');
        $image->drawImage($draw);
        $this->_imageHandler = $image;

        return $this;
    }

    /**
     * Get Imagick object
     *
     * @param mixed $files
     * @return \Imagick
     */
    protected function _getImagickObject($files = null)
    {
        return new \Imagick($files);
    }

    /**
     * Get ImagickDraw object
     *
     * @return \ImagickDraw
     */
    protected function _getImagickDrawObject()
    {
        return new \ImagickDraw();
    }

    /**
     * Get ImagickPixel object
     *
     * @param string|null $color
     * @return \ImagickPixel
     */
    protected function _getImagickPixelObject($color = null)
    {
        return new \ImagickPixel($color);
    }

    /**
     * Resizes watermark to desired size, when it is not stretched
     *
     * @param \Imagick $watermark
     */
    private function resizeWatermark(\Imagick $watermark): void
    {
        if ($this->getWatermarkWidth() &&
            $this->getWatermarkHeight() &&
            $this->getWatermarkPosition() != self::POSITION_STRETCH
        ) {
            $watermark->resizeImage(
                $this->getWatermarkWidth(),
                $this->getWatermarkHeight(),
                \Imagick::FILTER_CUBIC,
                self::BLUR_FACTOR
            );
        }
    }

    /**
     * Keeps transparenty if watermark is transparent
     *
     * @param \Imagick $watermark
     */
    private function handleWatermarkAlphaChannel(\Imagick $watermark): void
    {
        if (method_exists($watermark, 'getImageAlphaChannel')) {
            // available from imagick 6.4.0
            if ($watermark->getImageAlphaChannel() == 0) {
                $watermark->setImageAlphaChannel(\Imagick::ALPHACHANNEL_OPAQUE);
            }
        }
    }

    /**
     * Add tiled watermark at starting given X,Y position
     *
     * @param int $positionX
     * @param int $positionY
     * @param \Imagick $watermark
     * @param bool $compositeChannels
     */
    private function addTiledWatermark($positionX, $positionY, \Imagick $watermark, $compositeChannels): void
    {
        $offsetX = $positionX;
        $offsetY = $positionY;
        while ($offsetY <= $this->_imageSrcHeight + $watermark->getImageHeight()) {
            while ($offsetX <= $this->_imageSrcWidth + $watermark->getImageWidth()) {
                $this->_imageHandler->compositeImage(
                    $watermark,
                    \Imagick::COMPOSITE_OVER,
                    $offsetX,
                    $offsetY,
                    $compositeChannels
                );
                $offsetX += $watermark->getImageWidth();
            }
            $offsetX = $positionX;
            $offsetY += $watermark->getImageHeight();
        }
    }

    /**
     * Add watermark at given X,Y position
     *
     * @param int $positionX
     * @param int $positionY
     * @param \Imagick $watermark
     * @param bool $compositeChannels
     */
    private function addSingleWatermark($positionX, int $positionY, \Imagick $watermark, bool $compositeChannels): void
    {
        $this->_imageHandler->compositeImage(
            $watermark,
            \Imagick::COMPOSITE_OVER,
            $positionX,
            $positionY,
            $compositeChannels
        );
    }

    /**
     * Get and store the image colorspace.
     *
     * @return int
     */
    private function getColorspace(): int
    {
        if ($this->colorspace === -1) {
            $this->colorspace = $this->_imageHandler->getImageColorspace();
        }

        return $this->colorspace;
    }

    /**
     * Convert colorspace to SRGB if current colorspace is COLORSPACE_CMYK or COLORSPACE_UNDEFINED.
     *
     * @return void
     */
    private function maybeConvertColorspace(): void
    {
        if ($this->colorspace === \Imagick::COLORSPACE_CMYK || $this->colorspace === \Imagick::COLORSPACE_UNDEFINED) {
            $this->_imageHandler->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
            $this->colorspace = $this->_imageHandler->getImageColorspace();
        }
    }
}
