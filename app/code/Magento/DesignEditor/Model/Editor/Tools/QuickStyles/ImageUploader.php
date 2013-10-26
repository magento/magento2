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
 * @package     Magento_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quick style file uploader
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles;

class ImageUploader extends \Magento\Object
{
    /**
     * Quick style images path prefix
     */
    const PATH_PREFIX_QUICK_STYLE = 'quick_style_images';

    /**
     * Storage path
     *
     * @var string
     */
    protected $_storagePath;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Allowed extensions
     *
     * @var array
     */
    protected $_allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');


    /**
     * Generic constructor of change instance
     *
     * @param \Magento\Core\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Filesystem $filesystem,
        array $data = array()
    ) {
        $this->_uploaderFactory = $uploaderFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($data);
    }

    /**
     * Get storage folder
     *
     * @return string
     */
    public function getStoragePath()
    {
        if (null === $this->_storagePath) {
            $this->_storagePath = implode(\Magento\Filesystem::DIRECTORY_SEPARATOR, array(
                \Magento\Filesystem::fixSeparator($this->_getTheme()->getCustomization()->getCustomizationPath()),
                self::PATH_PREFIX_QUICK_STYLE,
            ));
        }
        return $this->_storagePath;
    }

    /**
     * Set storage path
     *
     * @param string $path
     * @return \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader
     */
    public function setStoragePath($path)
    {
        $this->_storagePath = $path;
        return $this;
    }

    /**
     * Get theme
     *
     * @return \Magento\Core\Model\Theme
     * @throws \InvalidArgumentException
     */
    protected function _getTheme()
    {
        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $this->getTheme();
        if (!$theme->getId()) {
            throw new \InvalidArgumentException('Theme was not found.');
        }
        return $theme;
    }

    /**
     * Upload image file
     *
     * @param string $key
     * @return array
     */
    public function uploadFile($key)
    {
        $result = array();
        /** @var $uploader \Magento\Core\Model\File\Uploader */
        $uploader = $this->_uploaderFactory->create(array('fileId' => $key));
        $uploader->setAllowedExtensions($this->_allowedExtensions);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowCreateFolders(true);

        if (!$uploader->save($this->getStoragePath())) {
            /** @todo add translator */
            throw new \Magento\Core\Exception('Cannot upload file.');
        }
        $result['css_path'] = implode(
            '/', array('..', self::PATH_PREFIX_QUICK_STYLE, $uploader->getUploadedFileName())
        );
        $result['name'] = $uploader->getUploadedFileName();
        return $result;
    }

    /**
     * Remove file
     *
     * @param string $file
     * @return \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader
     */
    public function removeFile($file)
    {
        $path = $this->getStoragePath();
        $filePath = $this->_filesystem->normalizePath($path . '/' . $file);

        if ($this->_filesystem->isPathInDirectory($filePath, $path)
            && $this->_filesystem->isPathInDirectory($filePath, $this->getStoragePath())
        ) {
            $this->_filesystem->delete($filePath);
        }

        return $this;
    }
}
