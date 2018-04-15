<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product\Image\NotLoadInfoImageException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Image as MagentoImage;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @method string getFile()
 * @method string getLabel()
 * @method string getPosition()
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Image extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var int
     */
    protected $_width;

    /**
     * @var int
     */
    protected $_height;

    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 80;

    /**
     * @var bool
     */
    protected $_keepAspectRatio = true;

    /**
     * @var bool
     */
    protected $_keepFrame = true;

    /**
     * @var bool
     */
    protected $_keepTransparency = true;

    /**
     * @var bool
     */
    protected $_constrainOnly = true;

    /**
     * @var int[]
     */
    protected $_backgroundColor = [255, 255, 255];

    /**
     * @var string
     */
    protected $_baseFile;

    /**
     * @var bool
     */
    protected $_isBaseFilePlaceholder;

    /**
     * @var string|bool
     */
    protected $_newFile;

    /**
     * @var MagentoImage
     */
    protected $_processor;

    /**
     * @var string
     */
    protected $_destinationSubdir;

    /**
     * @var int
     */
    protected $_angle;

    /**
     * @var string
     */
    protected $_watermarkFile;

    /**
     * @var int
     */
    protected $_watermarkPosition;

    /**
     * @var int
     */
    protected $_watermarkWidth;

    /**
     * @var int
     */
    protected $_watermarkHeight;

    /**
     * @var int
     */
    protected $_watermarkImageOpacity = 70;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDatabase = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_catalogProductMediaConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\View\Asset\ImageFactory
     */
    private $viewAssetImageFactory;

    /**
     * @var \Magento\Catalog\Model\View\Asset\PlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;

    /**
     * @var \Magento\Framework\View\Asset\LocalInterface
     */
    private $imageAsset;

    /**
     * @var string
     */
    private $cachePrefix = 'IMG_INFO';

    /**
     * @var SerializerInterface
     */
    private $serializer;

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
     * @param SerializerInterface|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
        \Magento\Catalog\Model\View\Asset\PlaceholderFactory $viewAssetPlaceholderFactory = null,
        SerializerInterface $serializer = null
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
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    /**
     * @return int
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
     */
    public function getQuality()
    {
        return $this->_quality;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepFrame($keep)
    {
        $this->_keepFrame = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = $flag && $flag !== 'false';
        return $this;
    }

    /**
     * @param int[] $rgbArray
     * @return $this
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * @param string $size
     * @return $this
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
     * Convert array of 3 items (decimal r, g, b) to string of their hex values
     *
     * @param int[] $rgbArray
     * @return string
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
        if ($file == 'no_selection' || !$this->_fileExists($this->imageAsset->getSourceFile())) {
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
     */
    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    /**
     * @deprecated 101.1.0
     * @return bool|string
     */
    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * Retrieve 'true' if image is a base file placeholder
     *
     * @return bool
     */
    public function isBaseFilePlaceholder()
    {
        return (bool)$this->_isBaseFilePlaceholder;
    }

    /**
     * @param MagentoImage $processor
     * @return $this
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * @return MagentoImage
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
     */
    public function getUrl()
    {
        return $this->imageAsset->getUrl();
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setDestinationSubdir($dir)
    {
        $this->_destinationSubdir = $dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationSubdir()
    {
        return $this->_destinationSubdir;
    }

    /**
     * @return bool
     */
    public function isCached()
    {
        return (
            is_array($this->loadImageInfoFromCache($this->imageAsset->getPath())) ||
            file_exists($this->imageAsset->getPath())
        );
    }

    /**
     * Set watermark file name
     *
     * @param string $file
     * @return $this
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
     * Set watermark image opacity
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
     * Get watermark image opacity
     *
     * @return int
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
     * Get watermark height
     *
     * @return string
     */
    public function getWatermarkHeight()
    {
        return $this->_watermarkHeight;
    }

    /**
     * @return void
     */
    public function clearCache()
    {
        $directory = $this->_catalogProductMediaConfig->getBaseMediaPath() . '/cache';
        $this->_mediaDirectory->delete($directory);

        $this->_coreFileStorageDatabase->deleteFolder($this->_mediaDirectory->getAbsolutePath($directory));
        $this->clearImageInfoFromCache();
    }

    /**
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
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
     * @return array
     * @throws NotLoadInfoImageException
     */
    public function getResizedImageInfo()
    {
        try {
            if ($this->isBaseFilePlaceholder() == true) {
                $image = $this->imageAsset->getSourceFile();
            } else {
                $image = $this->imageAsset->getPath();
            }

            $imageProperties = $this->getimagesize($image);

            return $imageProperties;
        } finally {
            if (empty($imageProperties)) {
                throw new NotLoadInfoImageException(__('Can\'t get information about the picture: %1', $image));
            }
        }
    }

    /**
     * Retrieve misc params based on all image attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
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

    /**
     * Get image size
     *
     * @param string $imagePath
     * @return array
     */
    private function getImageSize($imagePath)
    {
        $imageInfo = $this->loadImageInfoFromCache($imagePath);
        if (!isset($imageInfo['size'])) {
            $imageSize = getimagesize($imagePath);
            $this->saveImageInfoToCache(['size' => $imageSize], $imagePath);
            return $imageSize;
        } else {
            return $imageInfo['size'];
        }
    }

    /**
     * Save image data to cache
     *
     * @param array $imageInfo
     * @param string $imagePath
     * @return void
     */
    private function saveImageInfoToCache(array $imageInfo, string $imagePath)
    {
        $imagePath = $this->cachePrefix  . $imagePath;
        $this->_cacheManager->save(
            $this->serializer->serialize($imageInfo),
            $imagePath,
            [$this->cachePrefix]
        );
    }

    /**
     * Load image data from cache
     *
     * @param string $imagePath
     * @return array|false
     */
    private function loadImageInfoFromCache(string $imagePath)
    {
        $imagePath = $this->cachePrefix  . $imagePath;
        $cacheData = $this->_cacheManager->load($imagePath);
        if (!$cacheData) {
            return false;
        } else {
            return $this->serializer->unserialize($cacheData);
        }
    }

    /**
     * Clear image data from cache
     *
     * @return void
     */
    private function clearImageInfoFromCache()
    {
        $this->_cacheManager->clean([$this->cachePrefix]);
    }
}
