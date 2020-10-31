<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Theme wysiwyg storage model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Storage
{
    /**
     * Type font
     *
     * Represents the font type
     */
    const TYPE_FONT = 'font';

    /**
     * Type image
     *
     * Represents the image type
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
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;
    /**
     * @var \Magento\Framework\Filesystem\Io\File|null
     */
    private $file;

    /**
     * @var DriverInterface
     */
    private $filesystemDriver;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Theme\Helper\Storage $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Filesystem\Io\File|null $file
     * @param DriverInterface|null $filesystemDriver
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Theme\Helper\Storage $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Filesystem\Io\File $file = null,
        DriverInterface $filesystemDriver = null
    ) {
        $this->mediaWriteDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_imageFactory = $imageFactory;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        $this->file = $file ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Filesystem\Io\File::class
        );
        $this->filesystemDriver = $filesystemDriver ?: ObjectManager::getInstance()
            ->get(DriverInterface::class);
    }

    /**
     * Upload file
     *
     * @param string $targetPath
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadFile($targetPath)
    {
        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
        $uploader = $this->_objectManager->create(
            \Magento\MediaStorage\Model\File\Uploader::class,
            ['fileId' => 'file']
        );
        $uploader->setAllowedExtensions($this->_helper->getAllowedExtensionsByType());
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);
        unset($result['path']);

        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t upload the file right now.'));
        }

        $this->_createThumbnail($targetPath . '/' . $uploader->getUploadedFileName());

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
        $thumbnailPath = sprintf("%s/%s", $thumbnailDir, $this->file->getPathInfo($source)['basename']);
        try {
            $this->mediaWriteDirectory->isExist($thumbnailDir);
            $image = $this->_imageFactory->create();
            $image->open($this->mediaWriteDirectory->getAbsolutePath($source));
            $image->keepAspectRatio(true);
            $image->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
            $image->save($this->mediaWriteDirectory->getAbsolutePath($thumbnailPath));
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createFolder($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Use only standard alphanumeric, dashes and underscores.')
            );
        }
        if (!$this->mediaWriteDirectory->isWritable($path)) {
            $path = $this->_helper->getStorageRoot();
        }

        $newPath = $path . '/' . $name;

        if ($this->mediaWriteDirectory->isExist($newPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We found a directory with the same name.'));
        }

        $this->mediaWriteDirectory->create($newPath);

        $result = [
            'name' => $name,
            'short_name' => $this->_helper->getShortFilename($name),
            'path' => str_replace($this->_helper->getStorageRoot(), '', $newPath),
            'id' => $this->_helper->convertPathToId($newPath)
        ];

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
        $file = $this->urlDecoder->decode($file);
        $path = $this->mediaWriteDirectory->getRelativePath($this->_helper->getCurrentPath());

        $filePath = $this->mediaWriteDirectory->getRelativePath($path . '/' . $file);
        $thumbnailPath = $this->_helper->getThumbnailDirectory($filePath) . '/' . $file;

        if (0 === strpos($filePath, (string) $path) &&
            0 === strpos($filePath, (string) $this->_helper->getStorageRoot())
        ) {
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDirsCollection($currentPath)
    {
        if (!$this->mediaWriteDirectory->isExist($currentPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We cannot find a directory with this name.'));
        }
        $paths = $this->mediaWriteDirectory->search('.*', $currentPath);
        $directories = [];
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
        $files = [];
        $requestParams = $this->_helper->getRequestParams();
        $storageType = $this->_helper->getStorageType();
        foreach ($paths as $path) {
            if (!$this->mediaWriteDirectory->isFile($path)) {
                continue;
            }
            $fileName = $this->file->getPathInfo($path)['basename'];
            $file = ['text' => $fileName, 'id' => $this->urlEncoder->encode($fileName)];
            if (self::TYPE_IMAGE == $storageType) {
                $requestParams['file'] = $fileName;
                $file['thumbnailParams'] = $requestParams;
                //phpcs:ignore Generic.PHP.NoSilencedErrors
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
        $resultArray = [];
        foreach ($directories as $path) {
            $resultArray[] = [
                'text' => $this->_helper->getShortFilename(
                    $this->file->getPathInfo($path)['basename'],
                    20
                ),
                'id' => $this->_helper->convertPathToId($path),
                'cls' => 'folder'
            ];
        }
        return $resultArray;
    }

    /**
     * Delete directory
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteDirectory($path)
    {
        $rootCmp = rtrim($this->_helper->getStorageRoot(), '/');
        $pathCmp = rtrim($path, '/');
        $absolutePath = rtrim(
            $this->filesystemDriver->getRealPathSafety($this->mediaWriteDirectory->getAbsolutePath($path)),
            '/'
        );

        if ($rootCmp == $pathCmp || $rootCmp === $absolutePath) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t delete root directory %1 right now.', $path)
            );
        }

        return $this->mediaWriteDirectory->delete($path);
    }
}
