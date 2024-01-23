<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;
use Magento\MediaGallerySynchronizationApi\Model\CreateAssetFromFileInterface;

/**
 * Create media asset object based on the file information
 */
class CreateAssetFromFile implements CreateAssetFromFileInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $driver;

    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var GetContentHash
     */
    private $getContentHash;

    /**
     * @var GetFileInfo
     */
    private $getFileInfo;

    /**
     * @param Filesystem $filesystem
     * @param File $driver
     * @param AssetInterfaceFactory $assetFactory
     * @param GetContentHash $getContentHash
     * @param GetFileInfo $getFileInfo
     */
    public function __construct(
        Filesystem $filesystem,
        File $driver,
        AssetInterfaceFactory $assetFactory,
        GetContentHash $getContentHash,
        GetFileInfo $getFileInfo
    ) {
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->assetFactory = $assetFactory;
        $this->getContentHash = $getContentHash;
        $this->getFileInfo = $getFileInfo;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $path): AssetInterface
    {
        $absolutePath = $this->getMediaDirectory()->getAbsolutePath($path);
        $driver = $this->getMediaDirectory()->getDriver();

        if ($driver instanceof Filesystem\ExtendedDriverInterface) {
            $meta = $driver->getMetadata($absolutePath);
        } else {
            /**
             * SPL file info is not compatible with remote storages and must not be used.
             */
            $file = $this->getFileInfo->execute($absolutePath);
            [$width, $height] = getimagesizefromstring($driver->fileGetContents($absolutePath));
            $meta = [
                'size' => $file->getSize(),
                'extension' => $file->getExtension(),
                'basename' => $file->getBasename(),
                'extra' => [
                    'image-width' => $width,
                    'image-height' => $height
                ]
            ];
        }

        return $this->assetFactory->create(
            [
                'id' => null,
                'path' => $path,
                'title' => $meta['basename'] ?? '',
                'width' => $meta['extra']['image-width'] ?? 0,
                'height' => $meta['extra']['image-height'] ?? 0,
                'hash' => $this->getHash($path),
                'size' => $meta['size'] ?? 0,
                'contentType' => sprintf('%s/%s', 'image', $meta['extension'] ?? ''),
                'source' => 'Local'
            ]
        );
    }

    /**
     * Get hash image content.
     *
     * @param string $path
     * @return string
     * @throws FileSystemException
     */
    private function getHash(string $path): string
    {
        return $this->getContentHash->execute($this->getMediaDirectory()->readFile($path));
    }

    /**
     * Retrieve media directory instance with write access
     *
     * @return Filesystem\Directory\WriteInterface
     */
    private function getMediaDirectory(): Filesystem\Directory\WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
