<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Image\Adapter;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;

/**
 * Gd2 adapter.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Gd2 extends AbstractAdapter
{
    /**
     * Required extensions
     *
     * @var array
     */
    protected $_requiredExtensions = ["gd"];

    /**
     * Image output callbacks by type
     *
     * @var array
     */
    private static $_callbacks = [
        IMAGETYPE_GIF => ['output' => 'imagegif', 'create' => 'imagecreatefromgif'],
        IMAGETYPE_JPEG => ['output' => 'imagejpeg', 'create' => 'imagecreatefromjpeg'],
        IMAGETYPE_PNG => ['output' => 'imagepng', 'create' => 'imagecreatefrompng'],
        IMAGETYPE_XBM => ['output' => 'imagexbm', 'create' => 'imagecreatefromxbm'],
        IMAGETYPE_WBMP => ['output' => 'imagewbmp', 'create' => 'imagecreatefromxbm'],
    ];

    /**
     * Whether image was resized or not
     *
     * @var bool
     */
    protected $_resized = false;

    /**
     * For properties reset, e.g. mimeType caching.
     *
     * @return void
     */
    protected function _reset()
    {
        $this->_fileMimeType = null;
        $this->_fileType = null;
    }

    /**
     * Open image for processing
     *
     * @param string $filename
     * @return void
     * @throws \OverflowException|FileSystemException
     */
    public function open($filename)
    {
        if (!file_exists($filename)) {
            throw new FileSystemException(
                new Phrase('File "%1" does not exist.', [$this->_fileName])
            );
        }
        if (!$filename || filesize($filename) === 0 || !$this->validateURLScheme($filename)) {
            throw new \InvalidArgumentException('Wrong file');
        }
        $this->_fileName = $filename;
        $this->_reset();
        $this->getMimeType();
        $this->_getFileAttributes();
        if ($this->_isMemoryLimitReached()) {
            throw new \OverflowException('Memory limit has been reached.');
        }
        $this->imageDestroy();
        $this->_imageHandler = call_user_func(
            $this->_getCallback('create', null, sprintf('Unsupported image format. File: %s', $this->_fileName)),
            $this->_fileName
        );
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
     * Checks whether memory limit is reached.
     *
     * @return bool
     */
    protected function _isMemoryLimitReached()
    {
        $limit = $this->_convertToByte(ini_get('memory_limit'));
        $requiredMemory = $this->_getImageNeedMemorySize($this->_fileName);
        if ($limit === -1) {
            // A limit of -1 means no limit: http://www.php.net/manual/en/ini.core.php#ini.memory-limit
            return false;
        }
        return memory_get_usage(true) + $requiredMemory > $limit;
    }

    /**
     * Get image needed memory size
     *
     * @param string $file
     * @return float|int
     */
    protected function _getImageNeedMemorySize($file)
    {
        $imageInfo = getimagesize($file);
        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return 0;
        }
        if (!isset($imageInfo['channels'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['channels'] = 4;
        }
        if (!isset($imageInfo['bits'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['bits'] = 8;
        }

        return round(
            ($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + pow(2, 16)) * 1.65
        );
    }

    /**
     * Converts memory value (e.g. 64M, 129K) to bytes.
     *
     * Case insensitive value might be used.
     *
     * @param string $memoryValue
     * @return int
     */
    protected function _convertToByte($memoryValue)
    {
        if (stripos($memoryValue, 'G') !== false) {
            return (int)$memoryValue * pow(1024, 3);
        } elseif (stripos($memoryValue, 'M') !== false) {
            return (int)$memoryValue * 1024 * 1024;
        } elseif (stripos($memoryValue, 'K') !== false) {
            return (int)$memoryValue * 1024;
        }

        return (int)$memoryValue;
    }

    /**
     * Save image to specific path.
     *
     * If some folders of path does not exist they will be created
     *
     * @param null|string $destination
     * @param null|string $newName
     * @return void
     * @throws \Exception  If destination path is not writable
     */
    public function save($destination = null, $newName = null)
    {
        $fileName = $this->_prepareDestination($destination, $newName);

        if (!$this->_resized) {
            // keep alpha transparency
            $isAlpha = false;
            $isTrueColor = false;
            $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha, $isTrueColor);
            if ($isAlpha) {
                if ($isTrueColor) {
                    $newImage = imagecreatetruecolor($this->_imageSrcWidth, $this->_imageSrcHeight);
                } else {
                    $newImage = imagecreate($this->_imageSrcWidth, $this->_imageSrcHeight);
                }
                $this->_fillBackgroundColor($newImage);
                imagecopy($newImage, $this->_imageHandler, 0, 0, 0, 0, $this->_imageSrcWidth, $this->_imageSrcHeight);
                $this->imageDestroy();
                $this->_imageHandler = $newImage;
            }
        }

        // Enable interlace
        imageinterlace($this->_imageHandler, true);

        // Set image quality value
        switch ($this->_fileType) {
            case IMAGETYPE_PNG:
                $quality = 9;   // For PNG files compression level must be from 0 (no compression) to 9.
                break;

            case IMAGETYPE_JPEG:
                $quality = $this->quality();
                break;

            default:
                $quality = null;    // No compression.
        }

        // Prepare callback method parameters
        $functionParameters = [$this->_imageHandler, $fileName];
        if ($quality) {
            $functionParameters[] = $quality;
        }

        call_user_func_array($this->_getCallback('output'), $functionParameters);
    }

    /**
     * Render image and return its binary contents.
     *
     * @see \Magento\Framework\Image\Adapter\AbstractAdapter::getImage
     *
     * @return string
     */
    public function getImage()
    {
        ob_start();
        call_user_func($this->_getCallback('output'), $this->_imageHandler);
        return ob_get_clean();
    }

    /**
     * Obtain function name, basing on image type and callback type
     *
     * @param string $callbackType
     * @param null|int $fileType
     * @param string $unsupportedText
     * @return string
     * @throws \InvalidArgumentException
     * @throws \BadFunctionCallException
     */
    private function _getCallback($callbackType, $fileType = null, $unsupportedText = 'Unsupported image format.')
    {
        if (null === $fileType) {
            $fileType = $this->_fileType;
        }
        if (empty(self::$_callbacks[$fileType])) {
            throw new \InvalidArgumentException($unsupportedText);
        }
        if (empty(self::$_callbacks[$fileType][$callbackType])) {
            throw new \BadFunctionCallException('Callback not found.');
        }
        return self::$_callbacks[$fileType][$callbackType];
    }

    /**
     * Fill image with main background color.
     *
     * Returns a color identifier.
     *
     * @param resource &$imageResourceTo
     * @return int
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _fillBackgroundColor(&$imageResourceTo)
    {
        // try to keep transparency, if any
        if ($this->_keepTransparency) {
            $isAlpha = false;
            $transparentIndex = $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha);
            try {
                // fill truecolor png with alpha transparency
                if ($isAlpha) {
                    if (!imagealphablending($imageResourceTo, false)) {
                        throw new \InvalidArgumentException('Failed to set alpha blending for PNG image.');
                    }
                    $transparentAlphaColor = imagecolorallocatealpha($imageResourceTo, 0, 0, 0, 127);
                    if (false === $transparentAlphaColor) {
                        throw new \InvalidArgumentException('Failed to allocate alpha transparency for PNG image.');
                    }
                    if (!imagefill($imageResourceTo, 0, 0, $transparentAlphaColor)) {
                        throw new \InvalidArgumentException('Failed to fill PNG image with alpha transparency.');
                    }
                    if (!imagesavealpha($imageResourceTo, true)) {
                        throw new \InvalidArgumentException('Failed to save alpha transparency into PNG image.');
                    }

                    return $transparentAlphaColor;
                } elseif (false !== $transparentIndex) {
                    // fill image with indexed non-alpha transparency
                    $transparentColor = false;
                    if ($transparentIndex >= 0 && $transparentIndex <= imagecolorstotal($this->_imageHandler)) {
                        list($r, $g, $b) = array_values(imagecolorsforindex($this->_imageHandler, $transparentIndex));
                        $transparentColor = imagecolorallocate($imageResourceTo, $r, $g, $b);
                    }
                    if (false === $transparentColor) {
                        throw new \InvalidArgumentException('Failed to allocate transparent color for image.');
                    }
                    if (!imagefill($imageResourceTo, 0, 0, $transparentColor)) {
                        throw new \InvalidArgumentException('Failed to fill image with transparency.');
                    }
                    imagecolortransparent($imageResourceTo, $transparentColor);
                    return $transparentColor;
                }
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\Exception $e) {
                // fallback to default background color
            }
        }
        list($r, $g, $b) = $this->_backgroundColor;
        $color = imagecolorallocate($imageResourceTo, $r, $g, $b);
        if (!imagefill($imageResourceTo, 0, 0, $color)) {
            throw new \InvalidArgumentException("Failed to fill image background with color {$r} {$g} {$b}.");
        }

        return $color;
    }

    /**
     * Gives true for a PNG with alpha, false otherwise
     *
     * @param string $fileName
     * @return boolean
     */
    public function checkAlpha($fileName)
    {
        return (ord(file_get_contents($fileName, false, null, 25, 1)) & 6 & 4) == 4;
    }

    /**
     * Checks if image has alpha transparency
     *
     * @param resource $imageResource
     * @param int $fileType
     * @param bool $isAlpha
     * @param bool $isTrueColor
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    private function _getTransparency($imageResource, $fileType, &$isAlpha = false, &$isTrueColor = false)
    {
        $isAlpha = false;
        $isTrueColor = false;
        // assume that transparency is supported by gif/png only
        if (IMAGETYPE_GIF === $fileType || IMAGETYPE_PNG === $fileType) {
            // check for specific transparent color
            $transparentIndex = imagecolortransparent($imageResource);
            if ($transparentIndex >= 0) {
                return $transparentIndex;
            } elseif (IMAGETYPE_PNG === $fileType) {
                // assume that truecolor PNG has transparency
                $isAlpha = $this->checkAlpha($this->_fileName);
                $isTrueColor = true;
                // -1
                return $transparentIndex;
            }
        }
        if (IMAGETYPE_JPEG === $fileType) {
            $isTrueColor = true;
        }
        return false;
    }

    /**
     * Change the image size
     *
     * @param null|int $frameWidth
     * @param null|int $frameHeight
     * @return void
     */
    public function resize($frameWidth = null, $frameHeight = null)
    {
        $dims = $this->_adaptResizeValues($frameWidth, $frameHeight);

        // create new image
        $isAlpha = false;
        $isTrueColor = false;
        $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha, $isTrueColor);
        if ($isTrueColor) {
            $newImage = imagecreatetruecolor($dims['frame']['width'], $dims['frame']['height']);
        } else {
            $newImage = imagecreate($dims['frame']['width'], $dims['frame']['height']);
        }

        if ($isAlpha) {
            $this->_saveAlpha($newImage);
        }

        // fill new image with required color
        $this->_fillBackgroundColor($newImage);

        if ($this->_imageHandler) {
            // resample source image and copy it into new frame
            imagecopyresampled(
                $newImage,
                $this->_imageHandler,
                $dims['dst']['x'],
                $dims['dst']['y'],
                $dims['src']['x'],
                $dims['src']['y'],
                $dims['dst']['width'],
                $dims['dst']['height'],
                $this->_imageSrcWidth,
                $this->_imageSrcHeight
            );
        }
        $this->imageDestroy();
        $this->_imageHandler = $newImage;
        $this->refreshImageDimensions();
        $this->_resized = true;
    }

    /**
     * Rotate image on specific angle
     *
     * @param int $angle
     * @return void
     */
    public function rotate($angle)
    {
        $rotatedImage = imagerotate($this->_imageHandler, $angle, $this->imageBackgroundColor);
        $this->imageDestroy();
        $this->_imageHandler = $rotatedImage;
        $this->refreshImageDimensions();
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function watermark($imagePath, $positionX = 0, $positionY = 0, $opacity = 30, $tile = false)
    {
        list($watermarkSrcWidth, $watermarkSrcHeight, $watermarkFileType,) = $this->_getImageOptions($imagePath);
        $this->_getFileAttributes();
        $watermark = call_user_func(
            $this->_getCallback('create', $watermarkFileType, 'Unsupported watermark image format.'),
            $imagePath
        );

        $merged = false;

        $watermark = $this->createWatermarkBasedOnPosition($watermark, $positionX, $positionY, $merged, $tile);

        imagedestroy($watermark);
        $this->refreshImageDimensions();
    }

    /**
     * Create watermark based on it's image position.
     *
     * @param resource $watermark
     * @param int $positionX
     * @param int $positionY
     * @param bool $merged
     * @param bool $tile
     * @return false|resource
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function createWatermarkBasedOnPosition(
        $watermark,
        int $positionX,
        int $positionY,
        bool $merged,
        bool $tile
    ) {
        if ($this->getWatermarkWidth() &&
            $this->getWatermarkHeight() &&
            $this->getWatermarkPosition() != self::POSITION_STRETCH
        ) {
            $watermark = $this->createWaterMark($watermark, $this->getWatermarkWidth(), $this->getWatermarkHeight());
        }

        if ($this->getWatermarkPosition() == self::POSITION_TILE) {
            $tile = true;
        } elseif ($this->getWatermarkPosition() == self::POSITION_STRETCH) {
            $watermark = $this->createWaterMark($watermark, $this->_imageSrcWidth, $this->_imageSrcHeight);
        } elseif ($this->getWatermarkPosition() == self::POSITION_CENTER) {
            $positionX = $this->_imageSrcWidth / 2 - imagesx($watermark) / 2;
            $positionY = $this->_imageSrcHeight / 2 - imagesy($watermark) / 2;
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                imagesx($watermark),
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_TOP_RIGHT) {
            $positionX = $this->_imageSrcWidth - imagesx($watermark);
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                imagesx($watermark),
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_TOP_LEFT) {
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                imagesx($watermark),
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_BOTTOM_RIGHT) {
            $positionX = $this->_imageSrcWidth - imagesx($watermark);
            $positionY = $this->_imageSrcHeight - imagesy($watermark);
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                imagesx($watermark),
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } elseif ($this->getWatermarkPosition() == self::POSITION_BOTTOM_LEFT) {
            $positionY = $this->_imageSrcHeight - imagesy($watermark);
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                imagesx($watermark),
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        }

        if ($tile === false && $merged === false) {
            $this->imagecopymergeWithAlphaFix(
                $this->_imageHandler,
                $watermark,
                $positionX,
                $positionY,
                0,
                0,
                imagesx($watermark),
                imagesy($watermark),
                $this->getWatermarkImageOpacity()
            );
        } else {
            $offsetX = $positionX;
            $offsetY = $positionY;
            while ($offsetY <= $this->_imageSrcHeight + imagesy($watermark)) {
                while ($offsetX <= $this->_imageSrcWidth + imagesx($watermark)) {
                    $this->imagecopymergeWithAlphaFix(
                        $this->_imageHandler,
                        $watermark,
                        $offsetX,
                        $offsetY,
                        0,
                        0,
                        imagesx($watermark),
                        imagesy($watermark),
                        $this->getWatermarkImageOpacity()
                    );
                    $offsetX += imagesx($watermark);
                }
                $offsetX = $positionX;
                $offsetY += imagesy($watermark);
            }
        }

        return $watermark;
    }

    /**
     * Create watermark.
     *
     * @param resource $watermark
     * @param string $width
     * @param string $height
     * @return false|resource
     */
    private function createWaterMark($watermark, string $width, string $height)
    {
        $newWatermark = imagecreatetruecolor($width, $height);
        imagealphablending($newWatermark, false);
        $col = imagecolorallocate($newWatermark, 255, 255, 255);
        imagecolortransparent($newWatermark, $col);
        imagefilledrectangle($newWatermark, 0, 0, $width, $height, $col);
        imagesavealpha($newWatermark, true);
        imagecopyresampled(
            $newWatermark,
            $watermark,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($watermark),
            imagesy($watermark)
        );

        return $newWatermark;
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
        if ($left == 0 && $top == 0 && $right == 0 && $bottom == 0) {
            return false;
        }

        $newWidth = $this->_imageSrcWidth - $left - $right;
        $newHeight = $this->_imageSrcHeight - $top - $bottom;

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->_fileType == IMAGETYPE_PNG) {
            $this->_saveAlpha($canvas);
        }

        imagecopyresampled(
            $canvas,
            $this->_imageHandler,
            0,
            0,
            $left,
            $top,
            $newWidth,
            $newHeight,
            $newWidth,
            $newHeight
        );
        $this->imageDestroy();
        $this->_imageHandler = $canvas;
        $this->refreshImageDimensions();
        return true;
    }

    /**
     * Checks required dependencies
     *
     * @return void
     * @throws \RuntimeException If some of dependencies are missing
     */
    public function checkDependencies()
    {
        foreach ($this->_requiredExtensions as $value) {
            if (!extension_loaded($value)) {
                throw new \RuntimeException("Required PHP extension '{$value}' was not loaded.");
            }
        }
    }

    /**
     * Reassign image dimensions
     *
     * @return void
     */
    public function refreshImageDimensions()
    {
        $this->_imageSrcWidth = imagesx($this->_imageHandler);
        $this->_imageSrcHeight = imagesy($this->_imageHandler);
    }

    /**
     * Standard destructor. Destroy stored information about image
     */
    public function __destruct()
    {
        $this->imageDestroy();
    }

    /**
     * Helper function to free up memory associated with _imageHandler resource
     *
     * @return void
     */
    private function imageDestroy()
    {
        if (is_resource($this->_imageHandler)) {
            imagedestroy($this->_imageHandler);
        }
    }

    /**
     * Fixes saving PNG alpha channel
     *
     * @param resource $imageHandler
     * @return void
     */
    private function _saveAlpha($imageHandler)
    {
        $background = imagecolorallocate($imageHandler, 0, 0, 0);
        imagecolortransparent($imageHandler, $background);
        imagealphablending($imageHandler, false);
        imagesavealpha($imageHandler, true);
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
        $colorIndex = imagecolorat($this->_imageHandler, $x, $y);
        return imagecolorsforindex($this->_imageHandler, $colorIndex);
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
        $error = false;
        $this->_resized = true;
        try {
            $this->_createImageFromTtfText($text, $font);
        } catch (\Exception $e) {
            $error = true;
        }

        if ($error || empty($this->_imageHandler)) {
            $this->_createImageFromText($text);
        }

        return $this;
    }

    /**
     * Create Image using standard font
     *
     * @param string $text
     * @return void
     */
    protected function _createImageFromText($text)
    {
        $width = imagefontwidth($this->_fontSize) * strlen($text);
        $height = imagefontheight($this->_fontSize);

        $this->_createEmptyImage($width, $height);

        $black = imagecolorallocate($this->_imageHandler, 0, 0, 0);
        imagestring($this->_imageHandler, $this->_fontSize, 0, 0, $text, $black);
    }

    /**
     * Create Image using ttf font
     *
     * Note: This function requires both the GD library and the FreeType library
     *
     * @param string $text
     * @param string $font
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _createImageFromTtfText($text, $font)
    {
        $boundingBox = imagettfbbox($this->_fontSize, 0, $font, $text);
        $width = abs($boundingBox[4] - $boundingBox[0]);
        $height = abs($boundingBox[5] - $boundingBox[1]);

        $this->_createEmptyImage($width, $height);

        $black = imagecolorallocate($this->_imageHandler, 0, 0, 0);
        $result = imagettftext(
            $this->_imageHandler,
            $this->_fontSize,
            0,
            0,
            $height - $boundingBox[1],
            $black,
            $font,
            $text
        );
        if ($result === false) {
            throw new \InvalidArgumentException('Unable to create TTF text');
        }
    }

    /**
     * Create empty image with transparent background
     *
     * @param int $width
     * @param int $height
     * @return void
     */
    protected function _createEmptyImage($width, $height)
    {
        $this->_fileType = IMAGETYPE_PNG;
        $image = imagecreatetruecolor($width, $height);
        $colorWhite = imagecolorallocatealpha($image, 255, 255, 255, 127);

        imagealphablending($image, true);
        imagesavealpha($image, true);

        imagefill($image, 0, 0, $colorWhite);
        $this->imageDestroy();
        $this->_imageHandler = $image;
    }

    /**
     * Fix an issue with the usage of imagecopymerge where the alpha channel is lost
     *
     * @param resource $dst_im
     * @param resource $src_im
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int $src_w
     * @param int $src_h
     * @param int $pct
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function imagecopymergeWithAlphaFix(
        $dst_im,
        $src_im,
        $dst_x,
        $dst_y,
        $src_x,
        $src_y,
        $src_w,
        $src_h,
        $pct
    ) {
        if ($pct >= 100) {
            return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
        }

        if ($pct < 0) {
            return false;
        }

        $sizeX = imagesx($src_im);
        $sizeY = imagesy($src_im);
        if (false === $sizeX || false === $sizeY) {
            return false;
        }

        $tmpImg = imagecreatetruecolor($src_w, $src_h);
        if (false === $tmpImg) {
            return false;
        }

        if (false === imagealphablending($tmpImg, false)) {
            return false;
        }

        if (false === imagesavealpha($tmpImg, true)) {
            return false;
        }

        if (false === imagecopy($tmpImg, $src_im, 0, 0, 0, 0, $sizeX, $sizeY)) {
            return false;
        }

        $transparency = 127 - (($pct*127)/100);
        if (false === imagefilter($tmpImg, IMG_FILTER_COLORIZE, 0, 0, 0, $transparency)) {
            return false;
        }

        if (false === imagealphablending($dst_im, true)) {
            return false;
        }

        if (false === imagesavealpha($dst_im, true)) {
            return false;
        }

        $result = imagecopy($dst_im, $tmpImg, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
        imagedestroy($tmpImg);

        return $result;
    }
}
