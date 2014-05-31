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
 * Theme wysiwyg storage model
 */
namespace Magento\Theme\Model\Wysiwyg;

class Storage
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
     * \Directory for image thumbnail
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
     * \Directory name regular expression
     */
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';

    /**
     * Storage helper
     *
     * @var \Magento\Theme\Helper\Storage
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $mediaWriteDirectory;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Theme\Helper\Storage $helper
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Theme\Helper\Storage $helper,
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\Image\AdapterFactory $imageFactory
    ) {
        $this->mediaWriteDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_imageFactory = $imageFactory;
    }

    /**
     * Upload file
     *
     * @param string $targetPath
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadFile($targetPath)
    {
        /** @var $uploader \Magento\Core\Model\File\Uploader */
        $uploader = $this->_objectManager->create('Magento\Core\Model\File\Uploader', array('fileId' => 'file'));
        $uploader->setAllowedExtensions($this->_helper->getAllowedExtensionsByType());
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);

        if (!$result) {
            throw new \Magento\Framework\Model\Exception(__('We cannot upload the file.'));
        }

        $this->_createThumbnail($targetPath . '/' . $uploader->getUploadedFileName());

        $result['cookie'] = array(
            'name' => $this->_helper->getSession()->getName(),
            'value' => $this->_helper->getSession()->getSessionId(),
            'lifetime' => $this->_helper->getSession()->getCookieLifetime(),
            'path' => $this->_helper->getSession()->getCookiePath(),
            'domain' => $this->_helper->getSession()->getCookieDomain()
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
        if (self::TYPE_IMAGE != $this->_helper->getStorageType() || !$this->mediaWriteDirectory->isFile(
            $source
        ) || !$this->mediaWriteDirectory->isReadable(
            $source
        )
        ) {
            return false;
        }
        $thumbnailDir = $this->_helper->getThumbnailDirectory($source);
        $thumbnailPath = $thumbnailDir . '/' . pathinfo($source, PATHINFO_BASENAME);
        try {
            $this->mediaWriteDirectory->isExist($thumbnailDir);
            $image = $this->_imageFactory->create();
            $image->open($this->mediaWriteDirectory->getAbsolutePath($source));
            $image->keepAspectRatio(true);
            $image->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
            $image->save($this->mediaWriteDirectory->getAbsolutePath($thumbnailPath));
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            return false;
        }

        if ($this->mediaWriteDirectory->isFile($thumbnailPath)) {
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function createFolder($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            throw new \Magento\Framework\Model\Exception(__('Use only standard alphanumeric, dashes and underscores.'));
        }
        if (!$this->mediaWriteDirectory->isWritable($path)) {
            $path = $this->_helper->getStorageRoot();
        }

        $newPath = $path . '/' . $name;

        if ($this->mediaWriteDirectory->isExist($newPath)) {
            throw new \Magento\Framework\Model\Exception(__('We found a directory with the same name.'));
        }

        $this->mediaWriteDirectory->create($newPath);

        $result = array(
            'name' => $name,
            'short_name' => $this->_helper->getShortFilename($name),
            'path' => str_replace($this->_helper->getStorageRoot(), '', $newPath),
            'id' => $this->_helper->convertPathToId($newPath)
        );

        return $result;
    }

    /**
     * Delete file
     *
     * @param string $file
     * @return \Magento\Theme\Model\Wysiwyg\Storage
     */
    public function deleteFile($file)
    {
        $file = $this->_helper->urlDecode($file);
        $path = $this->mediaWriteDirectory->getRelativePath($this->_helper->getCurrentPath());

        $filePath = $this->mediaWriteDirectory->getRelativePath($path . '/' . $file);
        $thumbnailPath = $this->_helper->getThumbnailDirectory($filePath) . '/' . $file;

        if (0 === strpos($filePath, $path) && 0 === strpos($filePath, $this->_helper->getStorageRoot())) {
            $this->mediaWriteDirectory->delete($filePath);
            $this->mediaWriteDirectory->delete($thumbnailPath);
        }
        return $this;
    }

    /**
     * Get directory collection
     *
     * @param string $currentPath
     * @return array
     * @throws \Magento\Framework\Model\Exception
     */
    public function getDirsCollection($currentPath)
    {
        if (!$this->mediaWriteDirectory->isExist($currentPath)) {
            throw new \Magento\Framework\Model\Exception(__('We cannot find a directory with this name.'));
        }
        $paths = $this->mediaWriteDirectory->search('.*', $currentPath);
        $directories = array();
        foreach ($paths as $path) {
            if ($this->mediaWriteDirectory->isDirectory($path)) {
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
        $paths = $this->mediaWriteDirectory->search('.*', $this->_helper->getCurrentPath());
        $files = array();
        $requestParams = $this->_helper->getRequestParams();
        $storageType = $this->_helper->getStorageType();
        foreach ($paths as $path) {
            if (!$this->mediaWriteDirectory->isFile($path)) {
                continue;
            }
            $fileName = pathinfo($path, PATHINFO_BASENAME);
            $file = array('text' => $fileName, 'id' => $this->_helper->urlEncode($fileName));
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
                'text' => $this->_helper->getShortFilename(pathinfo($path, PATHINFO_BASENAME), 20),
                'id' => $this->_helper->convertPathToId($path),
                'cls' => 'folder'
            );
        }
        return $resultArray;
    }

    /**
     * Delete directory
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     */
    public function deleteDirectory($path)
    {
        $rootCmp = rtrim($this->_helper->getStorageRoot(), '/');
        $pathCmp = rtrim($path, '/');

        if ($rootCmp == $pathCmp) {
            throw new \Magento\Framework\Model\Exception(__('We cannot delete root directory %1.', $path));
        }

        return $this->mediaWriteDirectory->delete($path);
    }
}
