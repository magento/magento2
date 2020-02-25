<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

/**
 * Wysiwyg Images model.
 *
 * Tightly connected with controllers responsible for managing files so it uses session and is (sort of) a part
 * of the presentation layer.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 *
 * @api
 * @since 100.0.2
 */
class Storage extends \Magento\Framework\DataObject
{
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';

    const THUMBS_DIRECTORY_NAME = '.thumbs';

    const THUMB_PLACEHOLDER_PATH_SUFFIX = 'Magento_Cms::images/placeholder_thumbnail.jpg';

    /**
     * Config object
     *
     * @var \Magento\Framework\App\Config\Element
     */
    protected $_config;

    /**
     * Config object as array
     *
     * @var array
     */
    protected $_configAsArray;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
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
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Directory database factory
     *
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory
     */
    protected $_directoryDatabaseFactory;

    /**
     * Storage database factory
     *
     * @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory
     */
    protected $_storageDatabaseFactory;

    /**
     * Storage file factory
     *
     * @var \Magento\MediaStorage\Model\File\Storage\FileFactory
     */
    protected $_storageFileFactory;

    /**
     * Storage collection factory
     *
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory
     */
    protected $_storageCollectionFactory;

    /**
     * Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem\Io\File|null
     */
    private $ioFile;

    /**
     * Construct
     *
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory
     * @param \Magento\MediaStorage\Model\File\Storage\FileFactory $storageFileFactory
     * @param \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory
     * @param \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param array $resizeParameters
     * @param array $extensions
     * @param array $dirs
     * @param array $data
     * @param \Magento\Framework\Filesystem\DriverInterface $file
     * @param \Magento\Framework\Filesystem\Io\File|null $ioFile
     * @param \Psr\Log\LoggerInterface|null $logger
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory,
        \Magento\MediaStorage\Model\File\Storage\FileFactory $storageFileFactory,
        \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        array $resizeParameters = [],
        array $extensions = [],
        array $dirs = [],
        array $data = [],
        \Magento\Framework\Filesystem\DriverInterface $file = null,
        \Magento\Framework\Filesystem\Io\File $ioFile = null,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        $this->_session = $session;
        $this->_backendUrl = $backendUrl;
        $this->_cmsWysiwygImages = $cmsWysiwygImages;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory;
        $this->_assetRepo = $assetRepo;
        $this->_storageCollectionFactory = $storageCollectionFactory;
        $this->_storageFileFactory = $storageFileFactory;
        $this->_storageDatabaseFactory = $storageDatabaseFactory;
        $this->_directoryDatabaseFactory = $directoryDatabaseFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $this->_resizeParameters = $resizeParameters;
        $this->_extensions = $extensions;
        $this->_dirs = $dirs;
        $this->file = $file ?: ObjectManager::getInstance()->get(\Magento\Framework\Filesystem\Driver\File::class);
        $this->ioFile = $ioFile ?: ObjectManager::getInstance()->get(\Magento\Framework\Filesystem\Io\File::class);
        parent::__construct($data);
    }

    /**
     * Create sub directories if DB storage is used
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function createSubDirectories($path)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            /** @var \Magento\MediaStorage\Model\File\Storage\Directory\Database $subDirectories */
            $subDirectories = $this->_directoryDatabaseFactory->create();
            $directories = $subDirectories->getSubdirectories($path);
            foreach ($directories as $directory) {
                $fullPath = rtrim($path, '/') . '/' . $directory['name'];
                $this->_directory->create($fullPath);
            }
        }
    }

    /**
     * Prepare and get conditions for exclude directories
     *
     * @return array
     */
    protected function getConditionsForExcludeDirs()
    {
        $conditions = ['reg_exp' => [], 'plain' => []];

        if ($this->_dirs['exclude']) {
            foreach ($this->_dirs['exclude'] as $dir) {
                $conditions[!empty($dir['regexp']) ? 'reg_exp' : 'plain'][$dir['name']] = true;
            }
        }

        // "include" section takes precedence and can revoke directory exclusion
        if ($this->_dirs['include']) {
            foreach ($this->_dirs['include'] as $dir) {
                unset($conditions['reg_exp'][$dir['name']], $conditions['plain'][$dir['name']]);
            }
        }

        return $conditions;
    }

    /**
     * Remove excluded directories from collection
     *
     * @param \Magento\Framework\Data\Collection\Filesystem $collection
     * @param array $conditions
     * @return \Magento\Framework\Data\Collection\Filesystem
     */
    protected function removeItemFromCollection($collection, $conditions)
    {
        $regExp = $conditions['reg_exp'] ? '~' . implode('|', array_keys($conditions['reg_exp'])) . '~i' : null;
        $storageRoot = $this->_cmsWysiwygImages->getStorageRoot();
        $storageRootLength = strlen($storageRoot);

        foreach ($collection as $key => $value) {
            $mediaSubPathname = substr($value->getFilename(), $storageRootLength);
            $rootChildParts = explode('/', '/' . ltrim($mediaSubPathname, '/'));

            if (array_key_exists($rootChildParts[1], $conditions['plain'])
                || ($regExp && preg_match($regExp, $value->getFilename()))) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

    /**
     * Return one-level child directories for specified path
     *
     * @param string $path Parent directory path
     * @return \Magento\Framework\Data\Collection\Filesystem
     * @throws \Exception
     */
    public function getDirsCollection($path)
    {
        $this->createSubDirectories($path);

        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setCollectFiles(false)
            ->setCollectRecursively(false)
            ->setOrder('basename', \Magento\Framework\Data\Collection\Filesystem::SORT_ORDER_ASC);

        $conditions = $this->getConditionsForExcludeDirs();

        return $this->removeItemFromCollection($collection, $conditions);
    }

    /**
     * Return files
     *
     * @param   string $path Parent directory path
     * @param   string $type Type of storage, e.g. image, media etc.
     * @return  \Magento\Framework\Data\Collection\Filesystem
     *
     * @throws  \Magento\Framework\Exception\FileSystemException
     * @throws  \Magento\Framework\Exception\LocalizedException
     */
    public function getFilesCollection($path, $type = null)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            $files = $this->_storageDatabaseFactory->create()->getDirectoryFiles($path);

            /** @var \Magento\MediaStorage\Model\File\Storage\File $fileStorageModel */
            $fileStorageModel = $this->_storageFileFactory->create();
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }

        $collection = $this->getCollection(
            $path
        )->setCollectDirs(
            false
        )->setCollectFiles(
            true
        )->setCollectRecursively(
            false
        )->setOrder(
            'mtime',
            \Magento\Framework\Data\Collection::SORT_ORDER_ASC
        );

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed) . ')$/i');
        }

        // prepare items
        foreach ($collection as $item) {
            $item->setId($this->_cmsWysiwygImages->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($this->_cmsWysiwygImages->getShortFilename($item->getBasename()));
            $item->setUrl($this->_cmsWysiwygImages->getCurrentUrl() . $item->getBasename());
            $itemStats = $this->file->stat($item->getFilename());
            $item->setSize($itemStats['size']);
            $item->setMimeType(\mime_content_type($item->getFilename()));

            if ($this->isImage($item->getBasename())) {
                $thumbUrl = $this->getThumbnailUrl($item->getFilename(), true);
                // generate thumbnail "on the fly" if it does not exists
                if (!$thumbUrl) {
                    $thumbUrl = $this->_backendUrl->getUrl('cms/*/thumbnail', ['file' => $item->getId()]);
                }

                try {
                    $size = getimagesize($item->getFilename());

                    if (is_array($size)) {
                        $item->setWidth($size[0]);
                        $item->setHeight($size[1]);
                    }
                } catch (\Error $e) {
                    $this->logger->notice(sprintf("GetImageSize caused error: %s", $e->getMessage()));
                }
            } else {
                $thumbUrl = $this->_assetRepo->getUrl(self::THUMB_PLACEHOLDER_PATH_SUFFIX);
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
     * @throws \Exception
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createDirectory($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please rename the folder using only Latin letters, numbers, underscores and dashes.')
            );
        }

        $relativePath = $this->_directory->getRelativePath($path);
        if (!$this->_directory->isDirectory($relativePath) || !$this->_directory->isWritable($relativePath)) {
            $path = $this->_cmsWysiwygImages->getStorageRoot();
        }

        $newPath = $path . '/' . $name;
        $relativeNewPath = $this->_directory->getRelativePath($newPath);
        if ($this->_directory->isDirectory($relativeNewPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found a directory with the same name. Please try another folder name.')
            );
        }

        $this->_directory->create($relativeNewPath);
        try {
            if ($this->_coreFileStorageDb->checkDbUsage()) {
                $relativePath = $this->_coreFileStorageDb->getMediaRelativePath($newPath);
                $this->_directoryDatabaseFactory->create()->createRecursive($relativePath);
            }

            $result = [
                'name' => $name,
                'short_name' => $this->_cmsWysiwygImages->getShortFilename($name),
                'path' => $newPath,
                'id' => $this->_cmsWysiwygImages->convertPathToId($newPath),
            ];
            return $result;
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We cannot create a new directory.'));
        }
    }

    /**
     * Recursively delete directory from storage
     *
     * @param string $path Absolute path to target directory
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteDirectory($path)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            $this->_directoryDatabaseFactory->create()->deleteDirectory($path);
        }
        if (!$this->isPathAllowed($path, $this->getConditionsForExcludeDirs())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot delete directory %1.', $this->_getRelativePathToRoot($path))
            );
        }
        try {
            $this->_deleteByPath($path);
            $path = $this->getThumbnailRoot() . $this->_getRelativePathToRoot($path);
            $this->_deleteByPath($path);
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot delete directory %1.', $this->_getRelativePathToRoot($path))
            );
        }
    }

    /**
     * Delete by path
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _deleteByPath($path)
    {
        $path = $this->_sanitizePath($path);
        if (!empty($path)) {
            $this->_validatePath($path);
            $this->_directory->delete($this->_directory->getRelativePath($path));
        }
    }

    /**
     * Delete file (and its thumbnail if exists) from storage
     *
     * @param string $target File path to be deleted
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function deleteFile($target)
    {
        $relativePath = $this->_directory->getRelativePath($target);
        if ($this->_directory->isFile($relativePath)) {
            $this->_directory->delete($relativePath);
        }
        $this->_coreFileStorageDb->deleteFile($target);

        $thumb = $this->getThumbnailPath($target, true);
        $relativePathThumb = $this->_directory->getRelativePath($thumb);
        if ($thumb) {
            if ($this->_directory->isFile($relativePathThumb)) {
                $this->_directory->delete($relativePathThumb);
            }
            $this->_coreFileStorageDb->deleteFile($thumb);
        }
        return $this;
    }

    /**
     * Upload and resize new file
     *
     * @param string $targetPath Absolute path to target directory
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array File info Array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadFile($targetPath, $type = null)
    {
        if (!$this->isPathAllowed($targetPath, $this->getConditionsForExcludeDirs())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t upload the file to current folder right now. Please try another folder.')
            );
        }
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->_uploaderFactory->create(['fileId' => 'image']);
        $allowed = $this->getAllowedExtensions($type);
        if ($allowed) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        if (!$uploader->checkMimeType($this->getAllowedMimeTypes($type))) {
            throw new \Magento\Framework\Exception\LocalizedException(__('File validation failed.'));
        }
        $result = $uploader->save($targetPath);

        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t upload the file right now.'));
        }

        // create thumbnail
        $this->resizeFile($targetPath . '/' . $uploader->getUploadedFileName(), true);

        return $result;
    }

    /**
     * Thumbnail path getter
     *
     * @param string $filePath original file path
     * @param bool $checkFile OPTIONAL is it necessary to check file availability
     * @return string|false
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getThumbnailPath($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->_cmsWysiwygImages->getStorageRoot();

        if (strpos($filePath, (string) $mediaRootDir) === 0) {
            $relativeFilePath = substr($filePath, strlen($mediaRootDir));
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $thumbPath = $relativeFilePath === basename($filePath)
                ? $this->getThumbnailRoot() . DIRECTORY_SEPARATOR . $relativeFilePath
                : $this->getThumbnailRoot() . $relativeFilePath;

            if (!$checkFile || $this->_directory->isExist($this->_directory->getRelativePath($thumbPath))) {
                return $thumbPath;
            }
        }

        return false;
    }

    /**
     * Thumbnail URL getter
     *
     * @param string $filePath original file path
     * @param bool $checkFile OPTIONAL is it necessary to check file availability
     * @return string|false
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getThumbnailUrl($filePath, $checkFile = false)
    {
        $thumbPath = $this->getThumbnailPath($filePath, $checkFile);
        if ($thumbPath) {
            $thumbRelativePath = ltrim($this->_directory->getRelativePath($thumbPath), '/\\');
            $baseUrl = rtrim($this->_cmsWysiwygImages->getBaseUrl(), '/');
            $randomIndex = '?rand=' . time();
            return str_replace('\\', '/', $baseUrl . '/' . $thumbRelativePath) . $randomIndex;
        }

        return false;
    }

    /**
     * Create thumbnail for image and save it to thumbnails directory
     *
     * @param string $source Image path to be resized
     * @param bool $keepRatio Keep aspect ratio or not
     * @return bool|string Resized filepath or false if errors were occurred
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function resizeFile($source, $keepRatio = true)
    {
        $realPath = $this->_directory->getRelativePath($source);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }

        $targetDir = $this->getThumbsPath($source);
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }
        $image = $this->_imageFactory->create();
        $image->open($source);
        $image->keepAspectRatio($keepRatio);
        $image->resize($this->_resizeParameters['width'], $this->_resizeParameters['height']);
        $dest = $targetDir . '/' . $this->ioFile->getPathInfo($source)['basename'];
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {
            return $dest;
        }
        return false;
    }

    /**
     * Resize images on the fly in controller action
     *
     * @param string $filename File basename
     * @return bool|string Thumbnail path or false for errors
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function resizeOnTheFly($filename)
    {
        $path = $this->getSession()->getCurrentPath();
        if (!$path) {
            $path = $this->_cmsWysiwygImages->getCurrentPath();
        }
        return $this->resizeFile($path . '/' . $filename);
    }

    /**
     * Return thumbnails directory path for file/current directory
     *
     * @param bool|string $filePath Path to the file
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getThumbsPath($filePath = false)
    {
        $thumbnailDir = $this->getThumbnailRoot();

        if ($filePath) {
            $thumbPath = $this->getThumbnailPath($filePath, false);
            if ($thumbPath) {
                $thumbnailDir = $this->file->getParentDirectory($thumbPath);
            }
        }

        return $thumbnailDir;
    }

    /**
     * Storage session
     *
     * @return \Magento\Backend\Model\Session
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
        $allowed = $this->getExtensionsList($type);

        return array_keys(array_filter($allowed));
    }

    /**
     * Thumbnail root directory getter
     *
     * @return string
     */
    public function getThumbnailRoot()
    {
        return $this->_cmsWysiwygImages->getStorageRoot() . '/' . self::THUMBS_DIRECTORY_NAME;
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

        $ext = "";
        if (array_key_exists('extension', $this->ioFile->getPathInfo($filename))) {
            $ext = strtolower($this->ioFile->getPathInfo($filename)['extension']);
        }
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

    /**
     * Get cms wysiwyg images helper
     *
     * @return Images|null
     */
    public function getCmsWysiwygImages()
    {
        return $this->_cmsWysiwygImages;
    }

    /**
     * Is path under storage root directory
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _validatePath($path)
    {
        $root = $this->_sanitizePath($this->_cmsWysiwygImages->getStorageRoot());
        if ($root == $path) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t delete root directory %1 right now.', $path)
            );
        }
        if (strpos($path, (string) $root) !== 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Directory %1 is not under storage root path.', $path)
            );
        }
    }

    /**
     * Sanitize path
     *
     * @param string $path
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _sanitizePath($path)
    {
        return rtrim(
            preg_replace(
                '~[/\\\]+~',
                '/',
                $this->_directory->getDriver()->getRealPathSafety(
                    $this->_directory->getAbsolutePath($path)
                )
            ),
            '/'
        );
    }

    /**
     * Get path in root storage dir
     *
     * @param string $path
     * @return string|bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _getRelativePathToRoot($path)
    {
        return substr(
            $this->_sanitizePath($path),
            strlen($this->_sanitizePath($this->_cmsWysiwygImages->getStorageRoot()))
        );
    }

    /**
     * Prepare mime types config settings.
     *
     * @param string|null $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    private function getAllowedMimeTypes($type = null): array
    {
        $allowed = $this->getExtensionsList($type);

        return array_values(array_filter($allowed));
    }

    /**
     * Get list of allowed file extensions with mime type in values.
     *
     * @param string|null $type
     * @return array
     */
    private function getExtensionsList($type = null): array
    {
        if (is_string($type) && array_key_exists("{$type}_allowed", $this->_extensions)) {
            $allowed = $this->_extensions["{$type}_allowed"];
        } else {
            $allowed = $this->_extensions['allowed'];
        }

        return $allowed;
    }

    /**
     * Check if path is not in excluded dirs.
     *
     * @param string $path Absolute path
     * @param array $conditions Exclude conditions
     * @return bool
     */
    private function isPathAllowed($path, array $conditions): bool
    {
        $isAllowed = true;
        $regExp = $conditions['reg_exp'] ? '~' . implode('|', array_keys($conditions['reg_exp'])) . '~i' : null;
        $storageRoot = $this->_cmsWysiwygImages->getStorageRoot();
        $storageRootLength = strlen($storageRoot);

        $mediaSubPathname = substr($path, $storageRootLength);
        $rootChildParts = explode('/', '/' . ltrim($mediaSubPathname, '/'));

        if (array_key_exists($rootChildParts[1], $conditions['plain'])
            || ($regExp && preg_match($regExp, $path))) {
            $isAllowed = false;
        }

        return $isAllowed;
    }
}
