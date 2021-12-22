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

/**
 * Uploads an image to storage
 */
class UploadImage
{
    /**
     * @var Storage
     */
    private $imagesStorage;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Storage $imagesStorage
     * @param Filesystem $filesystem
     */
    public function __construct(
        Storage $imagesStorage,
        Filesystem $filesystem
    ) {
        $this->imagesStorage = $imagesStorage;
        $this->filesystem = $filesystem;
    }

    /**
     * Uploads the image and returns file object
     *
     * @param string $targetFolder
     * @param string $type
     * @throws LocalizedException
     */
    public function execute(string $targetFolder, string $type): void
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if (!$mediaDirectory->isDirectory($targetFolder)) {
            throw new LocalizedException(__('Directory %1 does not exist in media directory.', $targetFolder));
        }

        $this->imagesStorage->uploadFile($mediaDirectory->getAbsolutePath($targetFolder), $type);
    }
}
