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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme storage helper
 */
namespace Magento\Theme\Helper;

class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Parameter name of node
     */
    const PARAM_NODE = 'node';

    /**
     * Parameter name of content type
     */
    const PARAM_CONTENT_TYPE = 'content_type';

    /**
     * Parameter name of theme identification number
     */
    const PARAM_THEME_ID = 'theme_id';

    /**
     * Parameter name of filename
     */
    const PARAM_FILENAME = 'filename';

    /**
     * Root node value identification number
     */
    const NODE_ROOT = 'root';

    /**
     * Display name for images storage type
     */
    const IMAGES = 'Images';

    /**
     * Display name for fonts storage type
     */
    const FONTS = 'Fonts';

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
     * @var \Magento\Framework\App\Filesystem
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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->_session = $session;
        $this->_themeFactory = $themeFactory;
        $this->mediaDirectoryWrite = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        $this->mediaDirectoryWrite->create($this->mediaDirectoryWrite->getRelativePath($this->getStorageRoot()));
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
        return $this->urlEncode($path);
    }

    /**
     * Convert id to path
     *
     * @param string $value
     * @return string
     */
    public function convertIdToPath($value)
    {
        $path = $this->urlDecode($value);
        if (!strstr($path, $this->getStorageRoot())) {
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
                array($this->_getTheme()->getCustomization()->getCustomizationPath(), $this->getStorageType())
            );
        }
        return $this->_storageRoot;
    }

    /**
     * Get theme module for custom static files
     *
     * @return \Magento\Core\Model\Theme
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
     * @throws \Magento\Framework\Exception
     */
    public function getStorageType()
    {
        $allowedTypes = array(
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT,
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
        );
        $type = (string)$this->_getRequest()->getParam(self::PARAM_CONTENT_TYPE);
        if (!in_array($type, $allowedTypes)) {
            throw new \Magento\Framework\Exception('Invalid type');
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
        $pathPieces = array('..', $this->getStorageType());
        $node = $this->_getRequest()->getParam(self::PARAM_NODE);
        if ($node !== self::NODE_ROOT) {
            $node = $this->urlDecode($node);
            $nodes = explode('/', trim($node, '/'));
            $pathPieces = array_merge($pathPieces, $nodes);
        }
        $pathPieces[] = $this->urlDecode($this->_getRequest()->getParam(self::PARAM_FILENAME));
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

                if ($this->mediaDirectoryWrite->isDirectory($path) && 0 === strpos($path, $currentPath)) {
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
        return pathinfo($path, PATHINFO_DIRNAME) . '/' . \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY;
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
        if (!$this->mediaDirectoryWrite->isExist($imagePath) || 0 !== strpos($imagePath, $this->getStorageRoot())) {
            throw new \InvalidArgumentException('The image not found.');
        }
        return $this->getThumbnailDirectory($imagePath) . '/' . pathinfo($imageName, PATHINFO_BASENAME);
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
        return array(
            self::PARAM_THEME_ID => $themeId,
            self::PARAM_CONTENT_TYPE => $contentType,
            self::PARAM_NODE => $node
        );
    }

    /**
     * Get allowed extensions by type
     *
     * @return string[]
     * @throws \Magento\Framework\Exception
     */
    public function getAllowedExtensionsByType()
    {
        switch ($this->getStorageType()) {
            case \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT:
                $extensions = array('ttf', 'otf', 'eot', 'svg', 'woff');
                break;
            case \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE:
                $extensions = array('jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp');
                break;
            default:
                throw new \Magento\Framework\Exception('Invalid type');
        }
        return $extensions;
    }

    /**
     * Get storage type name for display.
     *
     * @return string
     * @throws \Magento\Framework\Exception
     */
    public function getStorageTypeName()
    {
        switch ($this->getStorageType()) {
            case \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT:
                $name = self::FONTS;
                break;
            case \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE:
                $name = self::IMAGES;
                break;
            default:
                throw new \Magento\Framework\Exception('Invalid type');
        }

        return $name;
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
