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
 * Theme wysiwyg storage model
 */
class Mage_Theme_Model_Wysiwyg_Storage
{
    /**
     * Type font
     */
    const TYPE_FONT = 'font';

    /**
     * Type image
     */
    const TYPE_IMAGE = 'image';

    /**
     * Directory for image thumbnail
     */
    const THUMBNAIL_DIRECTORY = '.thumbnail';

    /**
     * Image thumbnail width
     */
    const THUMBNAIL_WIDTH = 100;

    /**
     * Image thumbnail height
     */
    const THUMBNAIL_HEIGHT = 100;

    /**
     * Directory name regular expression
     */
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Storage helper
     *
     * @var Mage_Theme_Helper_Storage
     */
    protected $_helper;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Image_AdapterFactory
     */
    protected $_imageFactory;

    /**
     * Initialize dependencies
     *
     * @param Magento_Filesystem $filesystem
     * @param Mage_Theme_Helper_Storage $helper
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Image_AdapterFactory $imageFactory
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Theme_Helper_Storage $helper,
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Image_AdapterFactory $imageFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_imageFactory = $imageFactory;
    }

    /**
     * Upload file
     *
     * @param string $targetPath
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function uploadFile($targetPath)
    {
        /** @var $uploader Mage_Core_Model_File_Uploader */
        $uploader = $this->_objectManager->create('Mage_Core_Model_File_Uploader', array('fileId' => 'file'));
        $uploader->setAllowedExtensions($this->_helper->getAllowedExtensionsByType());
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);

        if (!$result) {
            throw new Mage_Core_Exception($this->_helper->__('We cannot upload the file.') );
        }

        $this->_createThumbnail(
            $targetPath . Magento_Filesystem::DIRECTORY_SEPARATOR . $uploader->getUploadedFileName()
        );

        $result['cookie'] = array(
            'name'     => $this->_helper->getSession()->getSessionName(),
            'value'    => $this->_helper->getSession()->getSessionId(),
            'lifetime' => $this->_helper->getSession()->getCookieLifetime(),
            'path'     => $this->_helper->getSession()->getCookiePath(),
            'domain'   => $this->_helper->getSession()->getCookieDomain()
        );

        return $result;
    }

    /**
     * Create thumbnail for image and save it to thumbnails directory
     *
     * @param string $source
     * @return bool|string Resized filepath or false if errors were occurred
     */
    public function _createThumbnail($source)
    {
        if (self::TYPE_IMAGE != $this->_helper->getStorageType() || !$this->_filesystem->isFile($source)
            || !$this->_filesystem->isReadable($source)
        ) {
            return false;
        }
        $thumbnailDir = $this->_helper->getThumbnailDirectory($source);
        $thumbnailPath = $thumbnailDir . Magento_Filesystem::DIRECTORY_SEPARATOR . pathinfo($source, PATHINFO_BASENAME);
        try {
            $this->_filesystem->ensureDirectoryExists($thumbnailDir);
            $image = $this->_imageFactory->create();
            $image->open($source);
            $image->keepAspectRatio(true);
            $image->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
            $image->save($thumbnailPath);
        } catch (Magento_Filesystem_Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            return false;
        }

        if ($this->_filesystem->isFile($thumbnailPath)) {
            return $thumbnailPath;
        }
        return false;
    }

    /**
     * Create folder
     *
     * @param string $name
     * @param string $path
     * @return array
     * @throws Mage_Core_Exception
     */
    public function createFolder($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            throw new Mage_Core_Exception(
                $this->_helper->__('Use only standard alphanumeric, dashes and underscores.')
            );
        }
        if (!$this->_filesystem->isWritable($path)) {
            $path = $this->_helper->getStorageRoot();
        }

        $newPath = $path . Magento_Filesystem::DIRECTORY_SEPARATOR . $name;

        if ($this->_filesystem->has($newPath)) {
            throw new Mage_Core_Exception($this->_helper->__('We found a directory with the same name.'));
        }

        $this->_filesystem->ensureDirectoryExists($newPath);

        $result = array(
            'name'       => $name,
            'short_name' => $this->_helper->getShortFilename($name),
            'path'       => str_replace($this->_helper->getStorageRoot(), '', $newPath),
            'id'         => $this->_helper->convertPathToId($newPath)
        );

        return $result;
    }

    /**
     * Delete file
     *
     * @param string $file
     * @return Mage_Theme_Model_Wysiwyg_Storage
     */
    public function deleteFile($file)
    {
        $file = $this->_helper->urlDecode($file);
        $path = $this->_helper->getSession()->getStoragePath();

        $filePath = $this->_filesystem->normalizePath($path . '/' . $file);
        $thumbnailPath = $this->_helper->getThumbnailDirectory($filePath)
            . Magento_Filesystem::DIRECTORY_SEPARATOR
            . $file;

        if ($this->_filesystem->isPathInDirectory($filePath, $path)
            && $this->_filesystem->isPathInDirectory($filePath, $this->_helper->getStorageRoot())
        ) {
            $this->_filesystem->delete($filePath);
            $this->_filesystem->delete($thumbnailPath);
        }
        return $this;
    }

    /**
     * Get directory collection
     *
     * @param string $currentPath
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getDirsCollection($currentPath)
    {
        if (!$this->_filesystem->has($currentPath)) {
            throw new Mage_Core_Exception($this->_helper->__('We cannot find a directory with this name.'));
        }

        $paths = $this->_filesystem->searchKeys($currentPath, '*');
        $directories = array();
        foreach ($paths as $path) {
            if ($this->_filesystem->isDirectory($path)) {
                $directories[] = $path;
            }
        }
        return $directories;
    }

    /**
     * Get files collection
     *
     * @return array
     */
    public function getFilesCollection()
    {
        $paths = $this->_filesystem->searchKeys($this->_helper->getCurrentPath(), '*');
        $files = array();
        $requestParams = $this->_helper->getRequestParams();
        $storageType = $this->_helper->getStorageType();
        foreach ($paths as $path) {
            if (!$this->_filesystem->isFile($path)) {
                continue;
            }
            $fileName = pathinfo($path, PATHINFO_BASENAME);
            $file = array(
                'text' => $fileName,
                'id'   => $this->_helper->urlEncode($fileName)
            );
            if (self::TYPE_IMAGE == $storageType) {
                $requestParams['file'] = $fileName;
                $file['thumbnailParams'] = $requestParams;

                $size = @getimagesize($path);
                if (is_array($size)) {
                    $file['width'] = $size[0];
                    $file['height'] = $size[1];
                }
            }
            $files[] = $file;
        }
        return $files;
    }

    /**
     * Get directories tree array
     *
     * @return array
     */
    public function getTreeArray()
    {
        $directories = $this->getDirsCollection($this->_helper->getCurrentPath());
        $resultArray = array();
        foreach ($directories as $path) {
            $resultArray[] = array(
                'text'  => $this->_helper->getShortFilename(pathinfo($path, PATHINFO_BASENAME), 20),
                'id'    => $this->_helper->convertPathToId($path),
                'cls'   => 'folder'
            );
        }
        return $resultArray;
    }

    /**
     * Delete directory
     *
     * @param string $path
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function deleteDirectory($path)
    {
        $rootCmp = rtrim($this->_helper->getStorageRoot(), Magento_Filesystem::DIRECTORY_SEPARATOR);
        $pathCmp = rtrim($path, Magento_Filesystem::DIRECTORY_SEPARATOR);

        if ($rootCmp == $pathCmp) {
            throw new Mage_Core_Exception($this->_helper->__('We cannot delete root directory %s.', $path));
        }

        return $this->_filesystem->delete($path);
    }
}
