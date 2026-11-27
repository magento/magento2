<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGallerySynchronizationApi\Model\ImportFilesInterface;
use Psr\Log\LoggerInterface;

/**
 * Import image file to the media gallery asset table
 */
class ImportMediaAsset implements ImportFilesInterface
{
    /**
     * @var SaveAssetsInterface
     */
    private $saveAssets;

    /**
     * @var GetAssetFromPath
     */
    private $getAssetFromPath;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param SaveAssetsInterface $saveAssets
     * @param GetAssetFromPath $getAssetFromPath
     * @param LoggerInterface|null $log
     */
    public function __construct(
        SaveAssetsInterface $saveAssets,
        GetAssetFromPath $getAssetFromPath,
        ?LoggerInterface $log = null
    ) {
        $this->saveAssets = $saveAssets;
        $this->getAssetFromPath = $getAssetFromPath;
        $this->log = $log ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $assets = [];

        foreach ($paths as $path) {
            try {
                $assets[] = $this->getAssetFromPath->execute($path);
            } catch (FileSystemException | \RuntimeException $exception) {
                $this->log->warning(
                    'Media gallery synchronization skipped a file that cannot be read.',
                    ['path' => $path, 'exception' => $exception]
                );
            }
        }

        $this->saveAssets->execute($assets);
    }
}
