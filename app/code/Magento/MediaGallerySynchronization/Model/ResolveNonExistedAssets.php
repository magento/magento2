<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\MediaGallerySynchronizationApi\Model\FetchBatchesInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete assets which not exist physically
 */
class ResolveNonExistedAssets
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';
    private const MEDIA_GALLERY_ASSET_PATH = 'path';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteAssetsByPaths;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Read
     */
    private $mediaDirectory;

    /**
     * @var FetchBatchesInterface
     */
    private $selectBatches;

    /**
     * @param Filesystem $filesystem
     * @param ResourceConnection $resourceConnection
     * @param DeleteAssetsByPathsInterface $deleteAssetsByPaths
     * @param LoggerInterface $logger
     * @param FetchBatchesInterface $selectBatches
     */
    public function __construct(
        Filesystem $filesystem,
        ResourceConnection $resourceConnection,
        DeleteAssetsByPathsInterface $deleteAssetsByPaths,
        LoggerInterface $logger,
        FetchBatchesInterface $selectBatches
    ) {
        $this->filesystem = $filesystem;
        $this->resourceConnection = $resourceConnection;
        $this->deleteAssetsByPaths = $deleteAssetsByPaths;
        $this->logger = $logger;
        $this->selectBatches = $selectBatches;
    }

    /**
     * Delete assets which not existed
     *
     * @return void
     */
    public function execute(): void
    {
        $columns = [self::MEDIA_GALLERY_ASSET_PATH];
        try {
            foreach ($this->selectBatches->execute(self::TABLE_MEDIA_GALLERY_ASSET, $columns, null) as $batch) {
                foreach ($batch as $item) {
                    if (!$this->getMediaDirectory()->isExist($item[self::MEDIA_GALLERY_ASSET_PATH])) {
                        $this->deleteAssetsByPaths->execute([$item[self::MEDIA_GALLERY_ASSET_PATH]]);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Retrieve media directory instance with read permissions
     *
     * @return Read
     */
    private function getMediaDirectory(): Read
    {
        if (!$this->mediaDirectory) {
            $this->mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }
}
