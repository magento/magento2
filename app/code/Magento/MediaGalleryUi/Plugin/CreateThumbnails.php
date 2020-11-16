<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Plugin;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaGallerySynchronizationApi\Model\ImportFilesComposite;

/**
 * Create resizes files that were synced
 */
class CreateThumbnails
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     * @param Storage $storage
     */
    public function __construct(Filesystem $filesystem, Storage $storage)
    {
        $this->storage = $storage;
        $this->filesystem = $filesystem;
    }

    /**
     * Create thumbnails for synced files.
     *
     * @param ImportFilesComposite $subject
     * @param string[] $paths
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ImportFilesComposite $subject, array $paths): array
    {
        foreach ($paths as $path) {
            $this->storage->resizeFile(
                $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($path)
            );
        }

        return [$paths];
    }
}
