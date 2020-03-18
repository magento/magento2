<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Plugin\Wysiwyg\Images;

use Magento\MediaGalleryApi\Model\Asset\Command\GetByPathInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Magento\Cms\Model\Wysiwyg\Images\Storage as StorageSubject;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\ValidatorException;
use Psr\Log\LoggerInterface;

/**
 * Ensures that metadata is removed from the database when a file is deleted and it is an image
 */
class Storage
{
    /**
     * @var GetByPathInterface
     */
    private $getMediaAssetByPath;

    /**
     * @var DeleteByPathInterface
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
     * @param GetByPathInterface $getMediaAssetByPath
     * @param DeleteByPathInterface $deleteMediaAssetByPath
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetByPathInterface $getMediaAssetByPath,
        DeleteByPathInterface $deleteMediaAssetByPath,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->getMediaAssetByPath = $getMediaAssetByPath;
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
     * @throws ValidatorException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteFile(StorageSubject $subject, StorageSubject $result, $target): StorageSubject
    {
        if (!is_string($target)) {
            return $result;
        }

        $relativePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getRelativePath($target);
        if (!$relativePath) {
            return $result;
        }

        try {
            $this->deleteMediaAssetByPath->execute($relativePath);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }
}
