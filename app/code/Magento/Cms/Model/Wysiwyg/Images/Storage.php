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
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Cms\Model\Wysiwyg\Images;

/**
 * Wysiwyg Images model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Storage extends \Magento\Object
{
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';
    const THUMBS_DIRECTORY_NAME = '.thumbs';
    const THUMB_PLACEHOLDER_PATH_SUFFIX = 'Magento_Cms::images/placeholder_thumbnail.jpg';

    /**
     * Config object
     *
     * @var \Magento\Core\Model\Config\Element
     */
    protected $_config;

    /**
     * Config object as array
     *
     * @var array
     */
    protected $_configAsArray;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Core\Model\View\Url
     */
    protected $_viewUrl;

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * Cms wysiwyg images
     *
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    protected $_cmsWysiwygImages = null;

    /**
     * @var array
     */
    protected $_resizeParameters;

    /**
     * @var array
     */
    protected $_extensions;

    /**
     * @var array
     */
    protected $_dirs;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Directory database factory
     *
     * @var \Magento\Core\Model\File\Storage\Directory\DatabaseFactory
     */
    protected $_directoryDatabaseFactory;

    /**
     * Storage database factory
     *
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $_storageDatabaseFactory;

    /**
     * Storage file factory
     *
     * @var \Magento\Core\Model\File\Storage\FileFactory
     */
    protected $_storageFileFactory;

    /**
     * Storage collection factory
     *
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory
     */
    protected $_storageCollectionFactory;

    /**
     * Dir
     *
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Url $backendUrl
     * @param \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\Image\AdapterFactory $imageFactory
     * @param \Magento\Core\Model\View\Url $viewUrl
     * @param \Magento\App\Dir $dir
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory
     * @param \Magento\Core\Model\File\Storage\FileFactory $storageFileFactory
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory
     * @param \Magento\Core\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory
     * @param \Magento\Core\Model\File\UploaderFactory $uploaderFactory
     * @param array $resizeParameters
     * @param array $extensions
     * @param array $dirs
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\Image\AdapterFactory $imageFactory,
        \Magento\Core\Model\View\Url $viewUrl,
        \Magento\App\Dir $dir,
        \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory,
        \Magento\Core\Model\File\Storage\FileFactory $storageFileFactory,
        \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\Core\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory,
        \Magento\Core\Model\File\UploaderFactory $uploaderFactory,
        array $resizeParameters = array(),
        array $extensions = array(),
        array $dirs = array(),
        array $data = array()
    ) {
        $this->_session = $session;
        $this->_backendUrl = $backendUrl;
        $this->_cmsWysiwygImages = $cmsWysiwygImages;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_filesystem = $filesystem;
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->setWorkingDirectory($cmsWysiwygImages->getStorageRoot());
        $this->_imageFactory = $imageFactory;
        $this->_viewUrl = $viewUrl;
        $this->_dir = $dir;
        $this->_storageCollectionFactory = $storageCollectionFactory;
        $this->_storageFileFactory = $storageFileFactory;
        $this->_storageDatabaseFactory = $storageDatabaseFactory;
        $this->_directoryDatabaseFactory = $directoryDatabaseFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_resizeParameters = $resizeParameters;
        $this->_extensions = $extensions;
        $this->_dirs = $dirs;
        parent::__construct($data);
    }

    /**
     * Return one-level child directories for specified path
     *
     * @param string $path Parent directory path
     * @return \Magento\Data\Collection\Filesystem
     */
    public function getDirsCollection($path)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            /** @var \Magento\Core\Model\File\Storage\Directory\Database $subDirectories */
            $subDirectories = $this->_directoryDatabaseFactory->create();
            $subDirectories->getSubdirectories($path);
            foreach ($subDirectories as $directory) {
                $fullPath = rtrim($path, DS) . DS . $directory['name'];
                $this->_filesystem->ensureDirectoryExists($fullPath, 0777, $path);
            }
        }

        $conditions = array('reg_exp' => array(), 'plain' => array());

        if ($this->_dirs['exclude']) {
            foreach ($this->_dirs['exclude'] as $dir) {
                $conditions[$dir->getAttribute('regexp') ? 'reg_exp' : 'plain'][$dir] = true;
            }
        }

        // "include" section takes precedence and can revoke directory exclusion
        if ($this->_dirs['include']) {
            foreach ($this->_dirs['include'] as $dir) {
                unset($conditions['regexp'][(string) $dir], $conditions['plain'][$dir]);
            }
        }

        $regExp = $conditions['reg_exp'] ? ('~' . implode('|', array_keys($conditions['reg_exp'])) . '~i') : null;
        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setCollectFiles(false)
            ->setCollectRecursively(false);
        $storageRootLength = strlen($this->_cmsWysiwygImages->getStorageRoot());

        foreach ($collection as $key => $value) {
            $rootChildParts = explode(DIRECTORY_SEPARATOR, substr($value->getFilename(), $storageRootLength));

            if (array_key_exists($rootChildParts[0], $conditions['plain'])
                || ($regExp && preg_match($regExp, $value->getFilename()))) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

    /**
     * Return files
     *
     * @param string $path Parent directory path
     * @param string $type Type of storage, e.g. image, media etc.
     * @return \Magento\Data\Collection\Filesystem
     */
    public function getFilesCollection($path, $type = null)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            $files = $this->_storageDatabaseFactory->create()->getDirectoryFiles($path);

            /** @var \Magento\Core\Model\File\Storage\File $fileStorageModel */
            $fileStorageModel = $this->_storageFileFactory->create();
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }

        $collection = $this->getCollection($path)
            ->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(false)
            ->setOrder('mtime', \Magento\Data\Collection::SORT_ORDER_ASC);

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed). ')$/i');
        }

        // prepare items
        foreach ($collection as $item) {
            $item->setId($this->_cmsWysiwygImages->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($this->_cmsWysiwygImages->getShortFilename($item->getBasename()));
            $item->setUrl($this->_cmsWysiwygImages->getCurrentUrl() . $item->getBasename());

            if ($this->isImage($item->getBasename())) {
                $thumbUrl = $this->getThumbnailUrl($item->getFilename(), true);
                // generate thumbnail "on the fly" if it does not exists
                if (!$thumbUrl) {
                    $thumbUrl = $this->_backendUrl->getUrl('*/*/thumbnail', array('file' => $item->getId()));
                }

                $size = @getimagesize($item->getFilename());

                if (is_array($size)) {
                    $item->setWidth($size[0]);
                    $item->setHeight($size[1]);
                }
            } else {
                $thumbUrl = $this->_viewUrl->getViewFileUrl(self::THUMB_PLACEHOLDER_PATH_SUFFIX);
            }

            $item->setThumbUrl($thumbUrl);
        }

        return $collection;
    }

    /**
     * Storage collection
     *
     * @param string $path Path to the directory
     * @return \Magento\Cms\Model\Wysiwyg\Images\Storage\Collection
     */
    public function getCollection($path = null)
    {
        /** @var \Magento\Cms\Model\Wysiwyg\Images\Storage\Collection $collection */
        $collection = $this->_storageCollectionFactory->create();
        if ($path !== null) {
            $collection->addTargetDir($path);
        }
        return $collection;
    }

    /**
     * Create new directory in storage
     *
     * @param string $name New directory name
     * @param string $path Parent directory path
     * @return array New directory info
     * @throws \Magento\Core\Exception
     */
    public function createDirectory($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            throw new \Magento\Core\Exception(
                __('Please correct the folder name. Use only letters, numbers, underscores and dashes.'));
        }
        if (!$this->_filesystem->isDirectory($path) || !$this->_filesystem->isWritable($path)) {
            $path = $this->_cmsWysiwygImages->getStorageRoot();
        }

        $newPath = $path . DS . $name;

        if ($this->_filesystem->isDirectory($newPath, $path)) {
            throw new \Magento\Core\Exception(
                __('We found a directory with the same name. Please try another folder name.'));
        }

        $this->_filesystem->createDirectory($newPath);
        try {
            if ($this->_coreFileStorageDb->checkDbUsage()) {
                $relativePath = $this->_coreFileStorageDb->getMediaRelativePath($newPath);
                $this->_directoryDatabaseFactory->create()->createRecursive($relativePath);
            }

            $result = array(
                'name'          => $name,
                'short_name'    => $this->_cmsWysiwygImages->getShortFilename($name),
                'path'          => $newPath,
                'id'            => $this->_cmsWysiwygImages->convertPathToId($newPath)
            );
            return $result;
        } Catch (\Magento\Filesystem\FilesystemException $e) {
            throw new \Magento\Core\Exception(__('We cannot create a new directory.'));
        }
    }

    /**
     * Recursively delete directory from storage
     *
     * @param string $path Target dir
     * @return void
     * @throws \Magento\Core\Exception
     */
    public function deleteDirectory($path)
    {
        // prevent accidental root directory deleting
        $rootCmp = rtrim($this->_cmsWysiwygImages->getStorageRoot(), DS);
        $pathCmp = rtrim($path, DS);

        if ($rootCmp == $pathCmp) {
            throw new \Magento\Core\Exception(
                __('We cannot delete root directory %1.', $path)
            );
        }


        if ($this->_coreFileStorageDb->checkDbUsage()) {
            $this->_directoryDatabaseFactory->create()->deleteDirectory($path);
        }
        try {
            $this->_filesystem->delete($path);
        } catch (\Magento\Filesystem\FilesystemException $e) {
            throw new \Magento\Core\Exception(__('We cannot delete directory %1.', $path));
        }

        if (strpos($pathCmp, $rootCmp) === 0) {
            $this->_filesystem->delete(
                $this->getThumbnailRoot() . DS . ltrim(substr($pathCmp, strlen($rootCmp)), '\\/')
            );
        }
    }

    /**
     * Delete file (and its thumbnail if exists) from storage
     *
     * @param string $target File path to be deleted
     * @return \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    public function deleteFile($target)
    {
        if ($this->_filesystem->isFile($target)) {
            $this->_filesystem->delete($target);
        }
        $this->_coreFileStorageDb->deleteFile($target);

        $thumb = $this->getThumbnailPath($target, true);
        if ($thumb) {
            if ($this->_filesystem->isFile($thumb)) {
                $this->_filesystem->delete($thumb);
            }
            $this->_coreFileStorageDb->deleteFile($thumb);
        }
        return $this;
    }


    /**
     * Upload and resize new file
     *
     * @param string $targetPath Target directory
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array File info Array
     * @throws \Magento\Core\Exception
     */
    public function uploadFile($targetPath, $type = null)
    {
        /** @var \Magento\Core\Model\File\Uploader $uploader */
        $uploader = $this->_uploaderFactory->create(array('fileId' => 'image'));
        $allowed = $this->getAllowedExtensions($type);
        if ($allowed) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);

        if (!$result) {
            throw new \Magento\Core\Exception(__('We cannot upload the file.') );
        }

        // create thumbnail
        $this->resizeFile($targetPath . DS . $uploader->getUploadedFileName(), true);

        $result['cookie'] = array(
            'name'     => session_name(),
            'value'    => $this->getSession()->getSessionId(),
            'lifetime' => $this->getSession()->getCookieLifetime(),
            'path'     => $this->getSession()->getCookiePath(),
            'domain'   => $this->getSession()->getCookieDomain()
        );

        return $result;
    }

    /**
     * Thumbnail path getter
     *
     * @param  string $filePath original file path
     * @param  boolean $checkFile OPTIONAL is it necessary to check file availability
     * @return string | false
     */
    public function getThumbnailPath($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->_cmsWysiwygImages->getStorageRoot();

        if (strpos($filePath, $mediaRootDir) === 0) {
            $thumbPath = $this->getThumbnailRoot() . DS . substr($filePath, strlen($mediaRootDir));

            if (!$checkFile || $this->_filesystem->isReadable($thumbPath)) {
                return $thumbPath;
            }
        }

        return false;
    }

    /**
     * Thumbnail URL getter
     *
     * @param  string $filePath original file path
     * @param  boolean $checkFile OPTIONAL is it necessary to check file availability
     * @return string | false
     */
    public function getThumbnailUrl($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->_cmsWysiwygImages->getStorageRoot();

        if (strpos($filePath, $mediaRootDir) === 0) {
            $thumbSuffix = self::THUMBS_DIRECTORY_NAME . DS . substr($filePath, strlen($mediaRootDir));

            if (! $checkFile || $this->_filesystem->isReadable($mediaRootDir . $thumbSuffix)) {
                $randomIndex = '?rand=' . time();
                return str_replace('\\', '/', $this->_cmsWysiwygImages->getBaseUrl() . $thumbSuffix) . $randomIndex;
            }
        }

        return false;
    }

    /**
     * Create thumbnail for image and save it to thumbnails directory
     *
     * @param string $source Image path to be resized
     * @param bool $keepRation Keep aspect ratio or not
     * @return bool|string Resized filepath or false if errors were occurred
     */
    public function resizeFile($source, $keepRation = true)
    {
        if (!$this->_filesystem->isFile($source)
            || !$this->_filesystem->isReadable($source)
        ) {
            return false;
        }

        $targetDir = $this->getThumbsPath($source);
        if (!$this->_filesystem->isWritable($targetDir)) {
            $this->_filesystem->createDirectory($targetDir);
        }
        if (!$this->_filesystem->isWritable($targetDir)) {
            return false;
        }
        $image = $this->_imageFactory->create();
        $image->open($source);
        $image->keepAspectRatio($keepRation);
        $image->resize($this->_resizeParameters['width'], $this->_resizeParameters['height']);
        $dest = $targetDir . DS . pathinfo($source, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_filesystem->isFile($dest)) {
            return $dest;
        }
        return false;
    }

    /**
     * Resize images on the fly in controller action
     *
     * @param string File basename
     * @return bool|string Thumbnail path or false for errors
     */
    public function resizeOnTheFly($filename)
    {
        $path = $this->getSession()->getCurrentPath();
        if (!$path) {
            $path = $this->_cmsWysiwygImages->getCurrentPath();
        }
        return $this->resizeFile($path . DS . $filename);
    }

    /**
     * Return thumbnails directory path for file/current directory
     *
     * @param bool|string $filePath Path to the file
     * @return string
     */
    public function getThumbsPath($filePath = false)
    {
        $mediaRootDir = $this->_dir->getDir(\Magento\App\Dir::MEDIA);
        $thumbnailDir = $this->getThumbnailRoot();

        if ($filePath && strpos($filePath, $mediaRootDir) === 0) {
            $thumbnailDir .= DS . dirname(substr($filePath, strlen($mediaRootDir)));
        }

        return $thumbnailDir;
    }

    /**
     * Storage session
     *
     * @return \Magento\Adminhtml\Model\Session
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Prepare allowed_extensions config settings
     *
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    public function getAllowedExtensions($type = null)
    {
        if (is_string($type) && array_key_exists("{$type}_allowed", $this->_extensions)) {
            $allowed = $this->_extensions["{$type}_allowed"];
        } else {
            $allowed = $this->_extensions['allowed'];
        }

        return array_keys(array_filter($allowed));
    }

    /**
     * Thumbnail root directory getter
     *
     * @return string
     */
    public function getThumbnailRoot()
    {
        return $this->_cmsWysiwygImages->getStorageRoot() . self::THUMBS_DIRECTORY_NAME;
    }

    /**
     * Simple way to check whether file is image or not based on extension
     *
     * @param string $filename
     * @return bool
     */
    public function isImage($filename)
    {
        if (!$this->hasData('_image_extensions')) {
            $this->setData('_image_extensions', $this->getAllowedExtensions('image'));
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $this->_getData('_image_extensions'));
    }

    /**
     * Get resize width
     *
     * @return int
     */
    public function getResizeWidth()
    {
        return $this->_resizeParameters['width'];
    }

    /**
     * Get resize height
     *
     * @return int
     */
    public function getResizeHeight()
    {
        return $this->_resizeParameters['height'];
    }
}
