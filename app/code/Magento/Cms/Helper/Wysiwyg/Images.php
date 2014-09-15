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
namespace Magento\Cms\Helper\Wysiwyg;

/**
 * Wysiwyg Images Helper
 */
class Images extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Current directory path
     * @var string
     */
    protected $_currentPath;

    /**
     * Current directory URL
     * @var string
     */
    protected $_currentUrl;

    /**
     * Currenty selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_backendData = $backendData;
        $this->_coreData = $coreData;
        $this->_storeManager = $storeManager;

        $this->_directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        $this->_directory->create(\Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY);
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Images Storage root directory
     *
     * @return string
     */
    public function getStorageRoot()
    {
        return $this->_directory->getAbsolutePath(\Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY);
    }

    /**
     * Images Storage base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Ext Tree node key name
     *
     * @return string
     */
    public function getTreeNodeName()
    {
        return 'node';
    }

    /**
     * Encode path to HTML element id
     *
     * @param string $path Path to file/directory
     * @return string
     */
    public function convertPathToId($path)
    {
        $path = str_replace($this->getStorageRoot(), '', $path);
        return $this->idEncode($path);
    }

    /**
     * Decode HTML element id
     *
     * @param string $id
     * @return string
     */
    public function convertIdToPath($id)
    {
        if ($id === \Magento\Theme\Helper\Storage::NODE_ROOT) {
            return $this->getStorageRoot();
        } else {
            return $this->getStorageRoot() . $this->idDecode($id);
        }
    }

    /**
     * Check whether using static URLs is allowed
     *
     * @return bool
     */
    public function isUsingStaticUrlsAllowed()
    {
        $checkResult = new \StdClass();
        $checkResult->isAllowed = false;
        $this->_eventManager->dispatch(
            'cms_wysiwyg_images_static_urls_allowed',
            array('result' => $checkResult, 'store_id' => $this->_storeId)
        );
        return $checkResult->isAllowed;
    }

    /**
     * Prepare Image insertion declaration for Wysiwyg or textarea(as_is mode)
     *
     * @param string $filename Filename transferred via Ajax
     * @param bool $renderAsTag Leave image HTML as is or transform it to controller directive
     * @return string
     */
    public function getImageHtmlDeclaration($filename, $renderAsTag = false)
    {
        $fileurl = $this->getCurrentUrl() . $filename;
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaPath = str_replace($mediaUrl, '', $fileurl);
        $directive = sprintf('{{media url="%s"}}', $mediaPath);
        if ($renderAsTag) {
            $html = sprintf('<img src="%s" alt="" />', $this->isUsingStaticUrlsAllowed() ? $fileurl : $directive);
        } else {
            if ($this->isUsingStaticUrlsAllowed()) {
                $html = $fileurl; // $mediaPath;
            } else {
                $directive = $this->_coreData->urlEncode($directive);
                $html = $this->_backendData->getUrl('cms/wysiwyg/directive', array('___directive' => $directive));
            }
        }
        return $html;
    }

    /**
     * Return path of the current selected directory or root directory for startup
     * Try to create target directory if it doesn't exist
     *
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function getCurrentPath()
    {
        if (!$this->_currentPath) {
            $currentPath = $this->_directory->getAbsolutePath() . \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY;
            $path = $this->_getRequest()->getParam($this->getTreeNodeName());
            if ($path) {
                $path = $this->convertIdToPath($path);
                if ($this->_directory->isDirectory($this->_directory->getRelativePath($path))) {
                    $currentPath = $path;
                }
            }
            try {
                $currentDir = $this->_directory->getRelativePath($currentPath);
                if (!$this->_directory->isExist($currentDir)) {
                    $this->_directory->create($currentDir);
                }
            } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
                $message = __('The directory %1 is not writable by server.', $currentPath);
                throw new \Magento\Framework\Model\Exception($message);
            }
            $this->_currentPath = $currentPath;
        }
        return $this->_currentPath;
    }

    /**
     * Return URL based on current selected directory or root directory for startup
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        if (!$this->_currentUrl) {
            $path = $this->getCurrentPath();
            $mediaUrl = $this->_storeManager->getStore(
                $this->_storeId
            )->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            $this->_currentUrl = $mediaUrl . $this->_directory->getRelativePath($path) . '/';
        }
        return $this->_currentUrl;
    }

    /**
     * Encode string to valid HTML id element, based on base64 encoding
     *
     * @param string $string
     * @return string
     */
    public function idEncode($string)
    {
        return strtr(base64_encode($string), '+/=', ':_-');
    }

    /**
     * Revert opration to idEncode
     *
     * @param string $string
     * @return string
     */
    public function idDecode($string)
    {
        $string = strtr($string, ':_-', '+/=');
        return base64_decode($string);
    }

    /**
     * Reduce filename by replacing some characters with dots
     *
     * @param string $filename
     * @param int $maxLength Maximum filename
     * @return string Truncated filename
     */
    public function getShortFilename($filename, $maxLength = 20)
    {
        if (strlen($filename) <= $maxLength) {
            return $filename;
        }
        return substr($filename, 0, $maxLength) . '...';
    }
}
