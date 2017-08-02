<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Image as MagentoImage;

/**
 * @method string getFile()
 * @method string getLabel()
 * @method string getPosition()
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Image extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $_width;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_height;

    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     * @since 2.0.0
     */
    protected $_quality = 80;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_keepAspectRatio = true;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_keepFrame = true;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_keepTransparency = true;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_constrainOnly = true;

    /**
     * @var int[]
     * @since 2.0.0
     */
    protected $_backgroundColor = [255, 255, 255];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_baseFile;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_isBaseFilePlaceholder;

    /**
     * @var string|bool
     * @since 2.0.0
     */
    protected $_newFile;

    /**
     * @var MagentoImage
     * @since 2.0.0
     */
    protected $_processor;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_destinationSubdir;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_angle;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_watermarkFile;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_watermarkPosition;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_watermarkWidth;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_watermarkHeight;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_watermarkImageOpacity = 70;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Image\Factory
     * @since 2.0.0
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\View\FileSystem
     * @since 2.0.0
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 2.0.0
     */
    protected $_coreFileStorageDatabase = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     * @since 2.0.0
     */
    protected $_catalogProductMediaConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\View\Asset\ImageFactory
     * @since 2.2.0
     */
    private $viewAssetImageFactory;

    /**
     * @var \Magento\Catalog\Model\View\Asset\PlaceholderFactory
     * @since 2.2.0
     */
    private $viewAssetPlaceholderFactory;

    /**
     * @var \Magento\Framework\View\Asset\LocalInterface
     * @since 2.2.0
     */
    private $imageAsset;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Catalog\Model\View\Asset\ImageFactory|null $viewAssetImageFactory
     * @param \Magento\Catalog\Model\View\Asset\PlaceholderFactory|null $viewAssetPlaceholderFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Catalog\Model\View\Asset\ImageFactory $viewAssetImageFactory = null,
        \Magento\Catalog\Model\View\Asset\PlaceholderFactory $viewAssetPlaceholderFactory = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->_coreFileStorageDatabase = $coreFileStorageDatabase;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory;
        $this->_assetRepo = $assetRepo;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_scopeConfig = $scopeConfig;
        $this->viewAssetImageFactory = $viewAssetImageFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\View\Asset\ImageFactory::class);
        $this->viewAssetPlaceholderFactory = $viewAssetPlaceholderFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\View\Asset\PlaceholderFactory::class);
    }

    /**
     * @param int $width
     * @return $this
     * @since 2.0.0
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @param int $height
     * @return $this
     * @since 2.0.0
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality
     * @return $this
     * @since 2.0.0
     */
    public function setQuality($quality)
    {
        $this->_quality = $quality;
        return $this;
    }

    /**
     * Get image quality
     *
     * @return int
     * @since 2.0.0
     */
    public function getQuality()
    {
        return $this->_quality;
    }

    /**
     * @param bool $keep
     * @return $this
     * @since 2.0.0
     */
    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     * @since 2.0.0
     */
    public function setKeepFrame($keep)
    {
        $this->_keepFrame = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     * @since 2.0.0
     */
    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = $flag && $flag !== 'false';
        return $this;
    }

    /**
     * @param int[] $rgbArray
     * @return $this
     * @since 2.0.0
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * @param string $size
     * @return $this
     * @since 2.0.0
     */
    public function setSize($size)
    {
        // determine width and height from string
        list($width, $height) = explode('x', strtolower($size), 2);
        foreach (['width', 'height'] as $wh) {
            ${$wh}
                = (int)${$wh};
            if (empty(${$wh})) {
                ${$wh}
                    = null;
            }
        }

        // set sizes
        $this->setWidth($width)->setHeight($height);

        return $this;
    }

    /**
     * @param string|null $file
     * @return bool
     * @since 2.0.0
     */
    protected function _checkMemory($file = null)
    {
        return $this->_getMemoryLimit() > $this->_getMemoryUsage() + $this->_getNeedMemoryForFile(
            $file
        )
        || $this->_getMemoryLimit() == -1;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function _getMemoryLimit()
    {
        $memoryLimit = trim(strtoupper(ini_get('memory_limit')));

        if (!isset($memoryLimit[0])) {
            $memoryLimit = "128M";
        }

        if (substr($memoryLimit, -1) == 'K') {
            return substr($memoryLimit, 0, -1) * 1024;
        }
        if (substr($memoryLimit, -1) == 'M') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024;
        }
        if (substr($memoryLimit, -1) == 'G') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024 * 1024;
        }
        return $memoryLimit;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    protected function _getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }
        return 0;
    }

    /**
     * @param string|null $file
     * @return float|int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function _getNeedMemoryForFile($file = null)
    {
        $file = $file === null ? $this->getBaseFile() : $file;
        if (!$file) {
            return 0;
        }

        if (!$this->_mediaDirectory->isExist($file)) {
            return 0;
        }

        $imageInfo = getimagesize($this->_mediaDirectory->getAbsolutePath($file));

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
            ($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65
        );
    }

    /**
     * Convert array of 3 items (decimal r, g, b) to string of their hex values
     *
     * @param int[] $rgbArray
     * @return string
     * @since 2.0.0
     */
    protected function _rgbToString($rgbArray)
    {
        $result = [];
        foreach ($rgbArray as $value) {
            if (null === $value) {
                $result[] = 'null';
            } else {
                $result[] = sprintf('%02s', dechex($value));
            }
        }
        return implode($result);
    }

    /**
     * Set filenames for base file and new file
     *
     * @param string $file
     * @return $this
     * @throws \Exception
     * @since 2.0.0
     */
    public function setBaseFile($file)
    {
        $this->_isBaseFilePlaceholder = false;

        $this->imageAsset = $this->viewAssetImageFactory->create(
            [
                'miscParams' => $this->getMiscParams(),
                'filePath' => $file,
            ]
        );
        if ($file == 'no_selection' || !$this->_fileExists($this->imageAsset->getSourceFile())
            || !$this->_checkMemory($this->imageAsset->getSourceFile())
        ) {
            $this->_isBaseFilePlaceholder = true;
            $this->imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $this->getDestinationSubdir(),
                ]
            );
        }

        $this->_baseFile = $this->imageAsset->getSourceFile();

        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    /**
     * @deprecated 2.2.0
     * @return bool|string
     * @since 2.0.0
     */
    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * Retrieve 'true' if image is a base file placeholder
     *
     * @return bool
     * @since 2.0.0
     */
    public function isBaseFilePlaceholder()
    {
        return (bool)$this->_isBaseFilePlaceholder;
    }

    /**
     * @param MagentoImage $processor
     * @return $this
     * @since 2.0.0
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * @return MagentoImage
     * @since 2.0.0
     */
    public function getImageProcessor()
    {
        if (!$this->_processor) {
            $filename = $this->getBaseFile() ? $this->_mediaDirectory->getAbsolutePath($this->getBaseFile()) : null;
            $this->_processor = $this->_imageFactory->create($filename);
        }
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        return $this->_processor;
    }

    /**
     * @see \Magento\Framework\Image\Adapter\AbstractAdapter
     * @return $this
     * @since 2.0.0
     */
    public function resize()
    {
        if ($this->getWidth() === null && $this->getHeight() === null) {
            return $this;
        }
        $this->getImageProcessor()->resize($this->_width, $this->_height);
        return $this;
    }

    /**
     * @param int $angle
     * @return $this
     * @since 2.0.0
     */
    public function rotate($angle)
    {
        $angle = intval($angle);
        $this->getImageProcessor()->rotate($angle);
        return $this;
    }

    /**
     * Set angle for rotating
     *
     * This func actually affects only the cache filename.
     *
     * @param int $angle
     * @return $this
     * @since 2.0.0
     */
    public function setAngle($angle)
    {
        $this->_angle = $angle;
        return $this;
    }

    /**
     * Add watermark to image
     * size param in format 100x200
     *
     * @param string $file
     * @param string $position
     * @param array $size ['width' => int, 'height' => int]
     * @param int $width
     * @param int $height
     * @param int $opacity
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function setWatermark(
        $file,
        $position = null,
        $size = null,
        $width = null,
        $height = null,
        $opacity = null
    ) {
        if ($this->_isBaseFilePlaceholder) {
            return $this;
        }

        if ($file) {
            $this->setWatermarkFile($file);
        } else {
            return $this;
        }

        if ($position) {
            $this->setWatermarkPosition($position);
        }
        if ($size) {
            $this->setWatermarkSize($size);
        }
        if ($width) {
            $this->setWatermarkWidth($width);
        }
        if ($height) {
            $this->setWatermarkHeight($height);
        }
        if ($opacity) {
            $this->setWatermarkImageOpacity($opacity);
        }
        $filePath = $this->_getWatermarkFilePath();

        if ($filePath) {
            $imagePreprocessor = $this->getImageProcessor();
            $imagePreprocessor->setWatermarkPosition($this->getWatermarkPosition());
            $imagePreprocessor->setWatermarkImageOpacity($this->getWatermarkImageOpacity());
            $imagePreprocessor->setWatermarkWidth($this->getWatermarkWidth());
            $imagePreprocessor->setWatermarkHeight($this->getWatermarkHeight());
            $imagePreprocessor->watermark($filePath);
        }

        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function saveFile()
    {
        if ($this->_isBaseFilePlaceholder) {
            return $this;
        }
        $filename = $this->getBaseFile() ? $this->imageAsset->getPath() : null;
        $this->getImageProcessor()->save($filename);
        $this->_coreFileStorageDatabase->saveFile($filename);
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getUrl()
    {
        return $this->imageAsset->getUrl();
    }

    /**
     * @param string $dir
     * @return $this
     * @since 2.0.0
     */
    public function setDestinationSubdir($dir)
    {
        $this->_destinationSubdir = $dir;
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getDestinationSubdir()
    {
        return $this->_destinationSubdir;
    }

    /**
     * @return bool|void
     * @since 2.0.0
     */
    public function isCached()
    {
        return file_exists($this->imageAsset->getPath());
    }

    /**
     * Set watermark file name
     *
     * @param string $file
     * @return $this
     * @since 2.0.0
     */
    public function setWatermarkFile($file)
    {
        $this->_watermarkFile = $file;
        return $this;
    }

    /**
     * Get watermark file name
     *
     * @return string
     * @since 2.0.0
     */
    public function getWatermarkFile()
    {
        return $this->_watermarkFile;
    }

    /**
     * Get relative watermark file path
     * or false if file not found
     *
     * @return string | bool
     * @since 2.0.0
     */
    protected function _getWatermarkFilePath()
    {
        $filePath = false;

        if (!($file = $this->getWatermarkFile())) {
            return $filePath;
        }
        $baseDir = $this->_catalogProductMediaConfig->getBaseMediaPath();

        $candidates = [
            $baseDir . '/watermark/stores/' . $this->_storeManager->getStore()->getId() . $file,
            $baseDir . '/watermark/websites/' . $this->_storeManager->getWebsite()->getId() . $file,
            $baseDir . '/watermark/default/' . $file,
            $baseDir . '/watermark/' . $file,
        ];
        foreach ($candidates as $candidate) {
            if ($this->_mediaDirectory->isExist($candidate)) {
                $filePath = $this->_mediaDirectory->getAbsolutePath($candidate);
                break;
            }
        }
        if (!$filePath) {
            $filePath = $this->_viewFileSystem->getStaticFileName($file);
        }

        return $filePath;
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    /**
     * Set watermark image opacity
     *
     * @param int $imageOpacity
     * @return $this
     * @since 2.0.0
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_watermarkImageOpacity = $imageOpacity;
        return $this;
    }

    /**
     * Get watermark image opacity
     *
     * @return int
     * @since 2.0.0
     */
    public function getWatermarkImageOpacity()
    {
        return $this->_watermarkImageOpacity;
    }

    /**
     * Set watermark size
     *
     * @param array $size
     * @return $this
     * @since 2.0.0
     */
    public function setWatermarkSize($size)
    {
        if (is_array($size)) {
            $this->setWatermarkWidth($size['width'])->setWatermarkHeight($size['height']);
        }
        return $this;
    }

    /**
     * Set watermark width
     *
     * @param int $width
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setWatermarkHeight($height)
    {
        $this->_watermarkHeight = $height;
        return $this;
    }

    /**
     * Get watermark height
     *
     * @return string
     * @since 2.0.0
     */
    public function getWatermarkHeight()
    {
        return $this->_watermarkHeight;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function clearCache()
    {
        $directory = $this->_catalogProductMediaConfig->getBaseMediaPath() . '/cache';
        $this->_mediaDirectory->delete($directory);

        $this->_coreFileStorageDatabase->deleteFolder($this->_mediaDirectory->getAbsolutePath($directory));
    }

    /**
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
     * @since 2.0.0
     */
    protected function _fileExists($filename)
    {
        if ($this->_mediaDirectory->isFile($filename)) {
            return true;
        } else {
            return $this->_coreFileStorageDatabase->saveFileToFilesystem(
                $this->_mediaDirectory->getAbsolutePath($filename)
            );
        }
    }

    /**
     * Return resized product image information
     *
     * @return array
     * @since 2.0.0
     */
    public function getResizedImageInfo()
    {
        if ($this->isBaseFilePlaceholder() == true) {
            $image = $this->imageAsset->getSourceFile();
        } else {
            $image = $this->imageAsset->getPath();
        }
        return getimagesize($image);
    }

    /**
     * Retrieve misc params based on all image attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.2.0
     */
    private function getMiscParams()
    {
        $miscParams = [
            'image_type' => $this->getDestinationSubdir(),
            'image_height' => $this->getHeight(),
            'image_width' => $this->getWidth(),
            'keep_aspect_ratio' => ($this->_keepAspectRatio ? '' : 'non') . 'proportional',
            'keep_frame' => ($this->_keepFrame ? '' : 'no') . 'frame',
            'keep_transparency' => ($this->_keepTransparency ? '' : 'no') . 'transparency',
            'constrain_only' => ($this->_constrainOnly ? 'do' : 'not') . 'constrainonly',
            'background' => $this->_rgbToString($this->_backgroundColor),
            'angle' => $this->_angle,
            'quality' => $this->_quality,
        ];

        // if has watermark add watermark params to hash
        if ($this->getWatermarkFile()) {
            $miscParams['watermark_file'] = $this->getWatermarkFile();
            $miscParams['watermark_image_opacity'] = $this->getWatermarkImageOpacity();
            $miscParams['watermark_position'] = $this->getWatermarkPosition();
            $miscParams['watermark_width'] = $this->getWatermarkWidth();
            $miscParams['watermark_height'] = $this->getWatermarkHeight();
        }

        return $miscParams;
    }
}
