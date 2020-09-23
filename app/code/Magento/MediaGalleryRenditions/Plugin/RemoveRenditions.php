<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\MediaGalleryRenditionsApi\Api\GetRenditionPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove renditions when assets are removed
 */
class RemoveRenditions
{
    /**
     * @var GetRenditionPathInterface
     */
    private $getRenditionPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param GetRenditionPathInterface $getRenditionPath
     * @param Filesystem $filesystem
     * @param LoggerInterface $log
     */
    public function __construct(
        GetRenditionPathInterface $getRenditionPath,
        Filesystem $filesystem,
        LoggerInterface $log
    ) {
        $this->getRenditionPath = $getRenditionPath;
        $this->filesystem = $filesystem;
        $this->log = $log;
    }

    /**
     * Remove renditions when assets are removed
     *
     * @param DeleteAssetsByPathsInterface $deleteAssetsByPaths
     * @param void $result
     * @param array $paths
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        DeleteAssetsByPathsInterface $deleteAssetsByPaths,
        $result,
        array $paths
    ): void {
        $this->removeRenditions($paths);
    }

    /**
     * Remove rendition files
     *
     * @param array $paths
     */
    private function removeRenditions(array $paths): void
    {
        foreach ($paths as $path) {
            try {
                $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->delete(
                    $this->getRenditionPath->execute($path)
                );
            } catch (\Exception $exception) {
                $this->log->error($exception);
            }
        }
    }
}
