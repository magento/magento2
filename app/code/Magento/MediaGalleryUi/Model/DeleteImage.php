<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;

/**
 * Delete image from a storage
 */
class DeleteImage
{
    /**
     * @var Storage
     */
    private $imagesStorage;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * DeleteImage constructor.
     *
     * @param Storage $imagesStorage
     * @param Filesystem $filesystem
     * @param IsPathExcludedInterface $isPathExcluded
     */
    public function __construct(
        Storage $imagesStorage,
        Filesystem $filesystem,
        IsPathExcludedInterface $isPathExcluded
    ) {
        $this->imagesStorage = $imagesStorage;
        $this->filesystem = $filesystem;
        $this->isPathExcluded = $isPathExcluded;
    }

    /**
     * Delete asset image physically from file storage and from data storage.
     *
     * @param AssetInterface[] $assets
     * @throws LocalizedException
     */
    public function execute(array $assets): void
    {
        $failedAssets = [];
        foreach ($assets as $asset) {
            if ($this->isPathExcluded->execute($asset->getPath())) {
                $failedAssets[] = $asset->getPath();
            }

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $absolutePath = $mediaDirectory->getAbsolutePath($asset->getPath());
            $this->imagesStorage->deleteFile($absolutePath);
        }
        if (!empty($failedAssets)) {
            throw new LocalizedException(
                __(
                    'Could not delete "%image": destination directory is restricted.',
                    ['image' => implode(",", $failedAssets)]
                )
            );
        }
    }
}
