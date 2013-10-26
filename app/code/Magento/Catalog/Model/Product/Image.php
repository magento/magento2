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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product link model
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product;

class Image extends \Magento\Core\Model\AbstractModel
{
    protected $_width;
    protected $_height;
    protected $_quality = 90;

    protected $_keepAspectRatio  = true;
    protected $_keepFrame        = true;
    protected $_keepTransparency = true;
    protected $_constrainOnly    = false;
    protected $_backgroundColor  = array(255, 255, 255);

    protected $_baseFile;
    protected $_isBaseFilePlaceholder;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;
    protected $_angle;

    protected $_watermarkFile;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeigth;
    protected $_watermarkImageOpacity = 70;

    /**
     * @var \Magento\Filesystem $filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\Image\Factory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Core\Model\View\Url
     */
    protected $_viewUrl;

    /**
     * @var \Magento\Core\Model\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDatabase = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Catalog product media config
     *
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_catalogProductMediaConfig;

    /**
     * Dir
     *
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\Dir $dir
     * @param \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\Image\Factory $imageFactory
     * @param \Magento\Core\Model\View\Url $viewUrl
     * @param \Magento\Core\Model\View\FileSystem $viewFileSystem
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\Dir $dir,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\Image\Factory $imageFactory,
        \Magento\Core\Model\View\Url $viewUrl,
        \Magento\Core\Model\View\FileSystem $viewFileSystem,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_dir = $dir;
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->_coreFileStorageDatabase = $coreFileStorageDatabase;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $baseDir = $this->_catalogProductMediaConfig->getBaseMediaPath();
        $this->_filesystem = $filesystem;
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($baseDir);
        $this->_filesystem->setIsAllowCreateDirectories(false);
        $this->_filesystem->setWorkingDirectory($baseDir);
        $this->_imageFactory = $imageFactory;
        $this->_viewUrl = $viewUrl;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_coreStoreConfig = $coreStoreConfig;
    }

    /**
     * @param $width
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality
     * @return \Magento\Catalog\Model\Product\Image
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
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = (bool)$keep;
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setKeepFrame($keep)
    {
        $this->_keepFrame = (bool)$keep;
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = (bool)$keep;
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = (bool)$flag;
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setSize($size)
    {
        // determine width and height from string
        list($width, $height) = explode('x', strtolower($size), 2);
        foreach (array('width', 'height') as $wh) {
            $$wh  = (int)$$wh;
            if (empty($$wh)) {
                $$wh = null;
            }
        }

        // set sizes
        $this->setWidth($width)->setHeight($height);

        return $this;
    }

    protected function _checkMemory($file = null)
    {
        return $this->_getMemoryLimit() > ($this->_getMemoryUsage() + $this->_getNeedMemoryForFile($file))
            || $this->_getMemoryLimit() == -1;
    }

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

    protected function _getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }
        return 0;
    }

    protected function _getNeedMemoryForFile($file = null)
    {
        $file = is_null($file) ? $this->getBaseFile() : $file;
        if (!$file) {
            return 0;
        }

        if (!$this->_filesystem->isFile($file)) {
            return 0;
        }

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
            ($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65
        );
    }

    /**
     * Convert array of 3 items (decimal r, g, b) to string of their hex values
     *
     * @param array $rgbArray
     * @return string
     */
    protected function _rgbToString($rgbArray)
    {
        $result = array();
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
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setBaseFile($file)
    {
        $this->_isBaseFilePlaceholder = false;

        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }
        $baseDir = $this->_catalogProductMediaConfig->getBaseMediaPath();

        if ('/no_selection' == $file) {
            $file = null;
        }
        if ($file) {
            if ((!$this->_fileExists($baseDir . $file)) || !$this->_checkMemory($baseDir . $file)) {
                $file = null;
            }
        }
        if (!$file) {
            $this->_isBaseFilePlaceholder = true;
            // check if placeholder defined in config
            $isConfigPlaceholder = $this->_coreStoreConfig->getConfig(
                "catalog/placeholder/{$this->getDestinationSubdir()}_placeholder"
            );
            $configPlaceholder   = '/placeholder/' . $isConfigPlaceholder;
            if (!empty($isConfigPlaceholder) && $this->_fileExists($baseDir . $configPlaceholder)) {
                $file = $configPlaceholder;
            } else {
                $this->_newFile = true;
                return $this;
            }
        }

        $baseFile = $baseDir . $file;

        if (!$file || !$this->_filesystem->isFile($baseFile)) {
            throw new \Exception(__('We can\'t find the image file.'));
        }

        $this->_baseFile = $baseFile;

        // build new filename (most important params)
        $path = array(
            $this->_catalogProductMediaConfig->getBaseMediaPath(),
            'cache',
            $this->_storeManager->getStore()->getId(),
            $path[] = $this->getDestinationSubdir()
        );
        if ((!empty($this->_width)) || (!empty($this->_height))) {
            $path[] = "{$this->_width}x{$this->_height}";
        }

        // add misk params as a hash
        $miscParams = array(
                ($this->_keepAspectRatio  ? '' : 'non') . 'proportional',
                ($this->_keepFrame        ? '' : 'no')  . 'frame',
                ($this->_keepTransparency ? '' : 'no')  . 'transparency',
                ($this->_constrainOnly ? 'do' : 'not')  . 'constrainonly',
                $this->_rgbToString($this->_backgroundColor),
                'angle' . $this->_angle,
                'quality' . $this->_quality
        );

        // if has watermark add watermark params to hash
        if ($this->getWatermarkFile()) {
            $miscParams[] = $this->getWatermarkFile();
            $miscParams[] = $this->getWatermarkImageOpacity();
            $miscParams[] = $this->getWatermarkPosition();
            $miscParams[] = $this->getWatermarkWidth();
            $miscParams[] = $this->getWatermarkHeight();
        }

        $path[] = md5(implode('_', $miscParams));

        // append prepared filename
        $this->_newFile = implode('/', $path) . $file; // the $file contains heading slash

        return $this;
    }

    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * @return \Magento\Image
     */
    public function getImageProcessor()
    {
        if (!$this->_processor) {
            $this->_processor = $this->_imageFactory->create($this->getBaseFile());
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
     * @see \Magento\Image\Adapter\AbstractAdapter
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function resize()
    {
        if (is_null($this->getWidth()) && is_null($this->getHeight())) {
            return $this;
        }
        $this->getImageProcessor()->resize($this->_width, $this->_height);
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
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
     * @return \Magento\Catalog\Model\Product\Image
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
     * @param string $size
     * @param int $width
     * @param int $heigth
     * @param int $imageOpacity
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setWatermark($file, $position=null, $size=null, $width=null, $heigth=null, $imageOpacity=null)
    {
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
        if ($heigth) {
            $this->setWatermarkHeight($heigth);
        }
        if ($imageOpacity) {
            $this->setImageOpacity($imageOpacity);
        }
        $filePath = $this->_getWatermarkFilePath();

        if ($filePath) {
            $this->getImageProcessor()
                ->setWatermarkPosition( $this->getWatermarkPosition() )
                ->setWatermarkImageOpacity( $this->getWatermarkImageOpacity() )
                ->setWatermarkWidth( $this->getWatermarkWidth() )
                ->setWatermarkHeight( $this->getWatermarkHeight() )
                ->watermark($filePath);
        }

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function saveFile()
    {
        if ($this->_isBaseFilePlaceholder && $this->_newFile === true) {
            return $this;
        }
        $filename = $this->getNewFile();
        $this->getImageProcessor()->save($filename);
        $this->_coreFileStorageDatabase->saveFile($filename);
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->_newFile === true) {
            $url = $this->_viewUrl->getViewFileUrl(
                "Magento_Catalog::images/product/placeholder/{$this->getDestinationSubdir()}.jpg"
            );
        } else {
            $baseDir = $this->_dir->getDir(\Magento\App\Dir::MEDIA);
            $path = str_replace($baseDir . DS, "", $this->_newFile);
            $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_MEDIA) . str_replace(DS, '/', $path);;
        }

        return $url;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Image
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

    public function isCached()
    {
        if (is_string($this->_newFile)) {
            return $this->_fileExists($this->_newFile);
        }
    }

    /**
     * Set watermark file name
     *
     * @param string $file
     * @return \Magento\Catalog\Model\Product\Image
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

        if (!$file = $this->getWatermarkFile()) {
            return $filePath;
        }

        $baseDir = $this->_catalogProductMediaConfig->getBaseMediaPath();

        if ($this->_fileExists($baseDir . '/watermark/stores/' . $this->_storeManager->getStore()->getId() . $file)) {
            $filePath = $baseDir . '/watermark/stores/' . $this->_storeManager->getStore()->getId() . $file;
        } elseif ($this->_fileExists($baseDir . '/watermark/websites/' . $this->_storeManager->getWebsite()->getId() . $file)) {
            $filePath = $baseDir . '/watermark/websites/' . $this->_storeManager->getWebsite()->getId() . $file;
        } elseif ($this->_fileExists($baseDir . '/watermark/default/' . $file)) {
            $filePath = $baseDir . '/watermark/default/' . $file;
        } elseif ($this->_fileExists($baseDir . '/watermark/' . $file)) {
            $filePath = $baseDir . '/watermark/' . $file;
        } else {
            $viewFile = $this->_viewFileSystem->getViewFile($file);
            if ($this->_filesystem->isFile($viewFile)) {
                $filePath = $viewFile;
            }
        }

        return $filePath;
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return \Magento\Catalog\Model\Product\Image
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
     * @return \Magento\Catalog\Model\Product\Image
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
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setWatermarkSize($size)
    {
        if (is_array($size)) {
            $this->setWatermarkWidth($size['width'])
                ->setWatermarkHeight($size['heigth']);
        }
        return $this;
    }

    /**
     * Set watermark width
     *
     * @param int $width
     * @return \Magento\Catalog\Model\Product\Image
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
     * Set watermark heigth
     *
     * @param int $heigth
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function setWatermarkHeight($heigth)
    {
        $this->_watermarkHeigth = $heigth;
        return $this;
    }

    /**
     * Get watermark height
     *
     * @return string
     */
    public function getWatermarkHeight()
    {
        return $this->_watermarkHeigth;
    }

    public function clearCache()
    {
        $directory = $this->_dir->getDir(\Magento\App\Dir::MEDIA) . DS . 'catalog' . DS . 'product'
            . DS . 'cache' . DS;
        $this->_filesystem->delete($directory);

        $this->_coreFileStorageDatabase->deleteFolder($directory);
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
        if ($this->_filesystem->isFile($filename)) {
            return true;
        } else {
            return $this->_coreFileStorageDatabase->saveFileToFilesystem($filename);
        }
    }
}
