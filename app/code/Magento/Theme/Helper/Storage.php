<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme storage helper
 */
namespace Magento\Theme\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Handles the storage of media files like images and fonts.
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Parameter name of node
     */
    public const PARAM_NODE = 'node';

    /**
     * Parameter name of content type
     */
    public const PARAM_CONTENT_TYPE = 'content_type';

    /**
     * Parameter name of theme identification number
     */
    public const PARAM_THEME_ID = 'theme_id';

    /**
     * Parameter name of filename
     */
    public const PARAM_FILENAME = 'filename';

    /**
     * Root node value identification number
     */
    public const NODE_ROOT = 'root';

    /**
     * Display name for images storage type
     */
    public const IMAGES = 'Images';

    /**
     * Display name for fonts storage type
     */
    public const FONTS = 'Fonts';

    /**
     * Current directory path
     *
     * @var string
     */
    protected $_currentPath;

    /**
     * Current storage root path
     *
     * @var string
     */
    protected $_storageRoot;

    /**
     * Magento filesystem
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $mediaDirectoryWrite;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;
    /**
     * @var DriverInterface
     */
    private $filesystemDriver;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory
     * @param \Magento\Framework\Filesystem\Io\File|null $file
     *
     * @param DriverInterface|null $filesystemDriver
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory,
        \Magento\Framework\Filesystem\Io\File $file = null,
        DriverInterface $filesystemDriver = null
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->_session = $session;
        $this->_themeFactory = $themeFactory;
        $this->mediaDirectoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectoryWrite->create($this->mediaDirectoryWrite->getRelativePath($this->getStorageRoot()));
        $this->file = $file ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Filesystem\Io\File::class
        );
        $this->filesystemDriver = $filesystemDriver ?: ObjectManager::getInstance()->get(DriverInterface::class);
    }

    /**
     * Convert path to id
     *
     * @param string $path
     * @return string
     */
    public function convertPathToId($path)
    {
        $path = str_replace($this->getStorageRoot(), '', $path);
        return $this->urlEncoder->encode($path);
    }

    /**
     * Convert id to path
     *
     * @param string $value
     * @return string
     */
    public function convertIdToPath($value)
    {
        $path = $this->urlDecoder->decode($value);
        if (!strstr($path, (string) $this->getStorageRoot())) {
            $path = $this->getStorageRoot() . $path;
        }
        return $path;
    }

    /**
     * Get short file name
     *
     * @param string $filename
     * @param int $maxLength
     * @return string
     */
    public function getShortFilename($filename, $maxLength = 20)
    {
        return strlen($filename) <= $maxLength ? $filename : substr($filename, 0, $maxLength) . '...';
    }

    /**
     * Get storage root directory
     *
     * @return string
     */
    public function getStorageRoot()
    {
        if (null === $this->_storageRoot) {
            $this->_storageRoot = implode(
                '/',
                [$this->_getTheme()->getCustomization()->getCustomizationPath(), $this->getStorageType()]
            );
        }
        return $this->_storageRoot;
    }

    /**
     * Get theme module for custom static files
     *
     * @return \Magento\Theme\Model\Theme
     * @throws \InvalidArgumentException
     */
    protected function _getTheme()
    {
        $themeId = $this->_getRequest()->getParam(self::PARAM_THEME_ID);
        $theme = $this->_themeFactory->create($themeId);
        if (!$themeId || !$theme) {
            throw new \InvalidArgumentException('Theme was not found.');
        }
        return $theme;
    }

    /**
     * Get storage type
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStorageType()
    {
        $allowedTypes = [
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT,
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
        ];
        $type = (string)$this->_getRequest()->getParam(self::PARAM_CONTENT_TYPE);
        if (!in_array($type, $allowedTypes)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid type'));
        }
        return $type;
    }

    /**
     * Relative url to static content
     *
     * @return string
     */
    public function getRelativeUrl()
    {
        $pathPieces = ['..', $this->getStorageType()];
        $node = $this->_getRequest()->getParam(self::PARAM_NODE, '');
        if ($node !== self::NODE_ROOT) {
            $node = $this->urlDecoder->decode($node);
            $nodes = explode('/', trim($node, '/'));
            $pathPieces = array_merge($pathPieces, $nodes);
        }
        $pathPieces[] = $this->urlDecoder->decode($this->_getRequest()->getParam(self::PARAM_FILENAME));
        return implode('/', $pathPieces);
    }

    /**
     * Get current path
     *
     * @return string
     */
    public function getCurrentPath()
    {
        if (!$this->_currentPath) {
            $currentPath = $this->getStorageRoot();
            $path = $this->_getRequest()->getParam(self::PARAM_NODE);
            if ($path && $path !== self::NODE_ROOT) {
                $path = $this->convertIdToPath($path);

                $path = $this->filesystemDriver->getRealPathSafety($path);

                if (strpos($path, $currentPath) !== 0) {
                    $path = $currentPath;
                }

                if ($this->mediaDirectoryWrite->isDirectory($path)
                    && strpos($path, $currentPath) === 0
                    && $path !== $currentPath
                ) {
                    $currentPath = $this->mediaDirectoryWrite->getRelativePath($path);
                }
            }
            $this->_currentPath = $currentPath;
        }
        return $this->_currentPath;
    }

    /**
     * Get thumbnail directory for path
     *
     * @param string $path
     * @return string
     */
    public function getThumbnailDirectory($path)
    {
        return sprintf(
            "%s/%s",
            $this->file->getPathInfo($path)['dirname'],
            \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY
        );
    }

    /**
     * Get thumbnail path in current directory by image name
     *
     * @param string $imageName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getThumbnailPath($imageName)
    {
        $imagePath = $this->getCurrentPath() . '/' . $imageName;
        if (!$this->mediaDirectoryWrite->isExist($imagePath) ||
            0 !== strpos($imagePath, (string) $this->getStorageRoot())
        ) {
            throw new \InvalidArgumentException('The image not found.');
        }
        return sprintf(
            "%s/%s",
            $this->getThumbnailDirectory($imagePath),
            $this->file->getPathInfo($imageName)['basename']
        );
    }

    /**
     * Request params for selected theme
     *
     * @return array
     */
    public function getRequestParams()
    {
        $themeId = $this->_getRequest()->getParam(self::PARAM_THEME_ID);
        $contentType = $this->_getRequest()->getParam(self::PARAM_CONTENT_TYPE);
        $node = $this->_getRequest()->getParam(self::PARAM_NODE);
        return [
            self::PARAM_THEME_ID => $themeId,
            self::PARAM_CONTENT_TYPE => $contentType,
            self::PARAM_NODE => $node
        ];
    }

    /**
     * Get allowed extensions by type
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllowedExtensionsByType()
    {
        return $this->getStorageType() == \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT
            ? ['ttf', 'otf', 'eot', 'svg', 'woff']
            : ['jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'];
    }

    /**
     * Get storage type name for display.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStorageTypeName()
    {
        return $this->getStorageType() == \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT
            ? self::FONTS
            : self::IMAGES;
    }

    /**
     * Get session model
     *
     * @return \Magento\Backend\Model\Session
     */
    public function getSession()
    {
        return $this->_session;
    }
}
