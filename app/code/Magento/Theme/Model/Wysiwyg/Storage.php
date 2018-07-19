<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme wysiwyg storage model
 */
namespace Magento\Theme\Model\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;

class Storage
{
    /**
     * Type font.
     */
    const TYPE_FONT = 'font';

    /**
     * Type image.
     */
    const TYPE_IMAGE = 'image';

    /**
     * Directory for image thumbnail.
     */
    const THUMBNAIL_DIRECTORY = '.thumbnail';

    /**
     * Image thumbnail width.
     */
    const THUMBNAIL_WIDTH = 100;

    /**
     * Image thumbnail height.
     */
    const THUMBNAIL_HEIGHT = 100;

    /**
     * \Directory name regular expression.
     */
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';

    /**
     * Storage helper.
     *
     * @var \Magento\Theme\Helper\Storage
     */
    protected $_helper;

    /**
     * Object manager.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Adapter factory.
     *
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * Media write directory.
     *
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $mediaWriteDirectory;

    /**
     * URL encoder.
     *
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * URL decoder.
     *
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Theme\Helper\Storage $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Theme\Helper\Storage $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory = null,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        $this->mediaWriteDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_imageFactory = $imageFactory;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        $this->uploaderFactory = $uploaderFactory ?:
            $objectManager->create(\Magento\MediaStorage\Model\File\UploaderFactory::class);
        $this->logger = $logger ?:
            $objectManager->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * Upload file.
     *
     * @param string $targetPath
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadFile($targetPath)
    {
        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => 'file']);
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
     * Create thumbnail for image and save it to thumbnails directory.
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
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->logger->critical($e);

            return false;
        }

        if ($this->mediaWriteDirectory->isFile($thumbnailPath)) {
            return $thumbnailPath;
        }

        return false;
    }

    /**
     * Create folder.
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
     * Delete file.
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

        if (0 === strpos($filePath, $path) && 0 === strpos($filePath, $this->_helper->getStorageRoot())) {
            $this->mediaWriteDirectory->delete($filePath);
            $this->mediaWriteDirectory->delete($thumbnailPath);
        }

        return $this;
    }

    /**
     * Get directory collection.
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
     * Get files collection.
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
            $fileName = pathinfo($path, PATHINFO_BASENAME);
            $file = ['text' => $fileName, 'id' => $this->urlEncoder->encode($fileName)];
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
     * Get directories tree array.
     *
     * @return array
     */
    public function getTreeArray()
    {
        $directories = $this->getDirsCollection($this->_helper->getCurrentPath());
        $resultArray = [];
        foreach ($directories as $path) {
            $resultArray[] = [
                'text' => $this->_helper->getShortFilename(pathinfo($path, PATHINFO_BASENAME), 20),
                'id' => $this->_helper->convertPathToId($path),
                'cls' => 'folder'
            ];
        }

        return $resultArray;
    }

    /**
     * Delete directory.
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteDirectory($path)
    {
        $rootCmp = rtrim($this->_helper->getStorageRoot(), '/');
        $pathCmp = rtrim($path, '/');

        if ($rootCmp == $pathCmp) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t delete root directory %1 right now.', $path)
            );
        }

        return $this->mediaWriteDirectory->delete($path);
    }
}
