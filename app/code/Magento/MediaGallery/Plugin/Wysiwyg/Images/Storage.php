<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Plugin\Wysiwyg\Images;

use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\Cms\Model\Wysiwyg\Images\Storage as StorageSubject;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * Ensures that metadata is removed from the database when a file is deleted and it is an image
 */
class Storage
{
    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteMediaAssetByPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Storage constructor.
     *
     * @param DeleteAssetsByPathsInterface $deleteMediaAssetByPath
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteAssetsByPathsInterface $deleteMediaAssetByPath,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->deleteMediaAssetByPath = $deleteMediaAssetByPath;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * Delete media data after the image delete action from Wysiwyg
     *
     * @param StorageSubject $subject
     * @param StorageSubject $result
     * @param string $target
     *
     * @return StorageSubject
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteFile(StorageSubject $subject, StorageSubject $result, $target): StorageSubject
    {
        if (!is_string($target)) {
            return $result;
        }

        $relativePath = $this->getMediaDirectoryRelativePath($target);
        if (!$relativePath) {
            return $result;
        }

        try {
            $this->deleteMediaAssetByPath->execute([$relativePath]);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    /**
     * Delete media data after the folder delete action from Wysiwyg
     *
     * @param StorageSubject $subject
     * @param mixed $result
     * @param string $path
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteDirectory(StorageSubject $subject, $result, $path)
    {
        if (!is_string($path)) {
            return $result;
        }

        try {
            $this->deleteMediaAssetByPath->execute(
                [
                    $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getRelativePath($path)
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    /**
     * Get path relative to media directory
     *
     * @param string $path
     * @return string
     */
    private function getMediaDirectoryRelativePath(string $path): string
    {
        $relativePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getRelativePath($path);

        return ($relativePath && false === strpos($relativePath, '/')) ? '/' . $relativePath : $relativePath;
    }
}
