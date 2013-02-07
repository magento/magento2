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
 * @category    Mage
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme storage helper
 */
class Mage_Theme_Helper_Storage extends Mage_Core_Helper_Abstract
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
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Backend_Model_Session
     */
    protected $_session;

    /**
     * @var Mage_Core_Model_Theme_Factory
     */
    protected $_themeFactory;

    /**
     * Constructor
     *
     * @param Magento_Filesystem $filesystem
     * @param Mage_Backend_Model_Session $session
     * @param Mage_Core_Model_Theme_Factory $themeFactory
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Backend_Model_Session $session,
        Mage_Core_Model_Theme_Factory $themeFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_session = $session;
        $this->_themeFactory = $themeFactory;

        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($this->getStorageRoot());
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
            $this->_storageRoot = implode(Magento_Filesystem::DIRECTORY_SEPARATOR, array(
                Magento_Filesystem::fixSeparator($this->_getTheme()->getCustomizationPath()),
                Mage_Core_Model_Theme_Files::PATH_PREFIX_CUSTOMIZED,
                $this->getStorageType()
            ));
        }
        return $this->_storageRoot;
    }

    /**
     * Get theme module for custom static files
     *
     * @return Mage_Core_Model_Theme
     * @throws InvalidArgumentException
     */
    protected function _getTheme()
    {
        $themeId = $this->_getRequest()->getParam(self::PARAM_THEME_ID);
        $theme = $this->_themeFactory->create();
        if (!$themeId || $themeId && !$theme->load($themeId)->getId()) {
            throw new InvalidArgumentException('Theme was not found.');
        }
        return $theme;
    }

    /**
     * Get storage type
     *
     * @return string
     * @throws Magento_Exception
     */
    public function getStorageType()
    {
        $allowedTypes = array(
            Mage_Theme_Model_Wysiwyg_Storage::TYPE_FONT,
            Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE
        );
        $type = (string)$this->_getRequest()->getParam(self::PARAM_CONTENT_TYPE);
        if (!in_array($type, $allowedTypes)) {
            throw new Magento_Exception('Invalid type');
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
            $nodes = explode(
                Magento_Filesystem::DIRECTORY_SEPARATOR,
                trim($node, Magento_Filesystem::DIRECTORY_SEPARATOR)
            );
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
                if ($this->_filesystem->isDirectory($path)
                    && $this->_filesystem->isPathInDirectory($path, $currentPath)
                ) {
                    $currentPath = $this->_filesystem->getAbsolutePath($path);
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
        return pathinfo($path, PATHINFO_DIRNAME) . Magento_Filesystem::DIRECTORY_SEPARATOR
            . Mage_Theme_Model_Wysiwyg_Storage::THUMBNAIL_DIRECTORY;
    }

    /**
     * Get thumbnail path in current directory by image name
     *
     * @param $imageName
     * @return string
     * @throws InvalidArgumentException
     */
    public function getThumbnailPath($imageName)
    {
        $imagePath = $this->getCurrentPath() . Magento_Filesystem::DIRECTORY_SEPARATOR . $imageName;
        if (!$this->_filesystem->has($imagePath)
            || !$this->_filesystem->isPathInDirectory($imagePath, $this->getStorageRoot())
        ) {
            throw new InvalidArgumentException('The image not found.');
        }
        return $this->getThumbnailDirectory($imagePath) . Magento_Filesystem::DIRECTORY_SEPARATOR
            . pathinfo($imageName, PATHINFO_BASENAME);
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
            self::PARAM_THEME_ID     => $themeId,
            self::PARAM_CONTENT_TYPE => $contentType,
            self::PARAM_NODE         => $node
        );
    }

    /**
     * Get allowed extensions by type
     *
     * @return array
     * @throws Magento_Exception
     */
    public function getAllowedExtensionsByType()
    {
        switch ($this->getStorageType()) {
            case Mage_Theme_Model_Wysiwyg_Storage::TYPE_FONT:
                $extensions = array('ttf', 'otf', 'eot', 'svg', 'woff');
                break;
            case Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE:
                $extensions = array('jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp');
                break;
            default:
                throw new Magento_Exception('Invalid type');
        }

        return $extensions;
    }

    /**
     * Get session model
     *
     * @return Mage_Backend_Model_Session
     */
    public function getSession()
    {
        return $this->_session;
    }
}
