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
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Api\ExtractMetadataInterface;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;
use Magento\MediaGallerySynchronization\Model\GetContentHash;

/**
 * Create media asset object based on the file information
 */
class CreateAssetFromFile
{
    /**
     * Date format
     */
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $driver;

    /**
     * @var TimezoneInterface;
     */
    private $date;

    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var GetContentHash
     */
    private $getContentHash;

    /**
     * @var ExtractMetadataInterface
     */
    private $extractMetadata;

    /**
     * @var GetFileInfo
     */
    private $getFileInfo;

    /**
     * @param Filesystem $filesystem
     * @param File $driver
     * @param TimezoneInterface $date
     * @param AssetInterfaceFactory $assetFactory
     * @param GetContentHash $getContentHash
     * @param ExtractMetadataInterface $extractMetadata
     * @param GetFileInfo $getFileInfo
     */
    public function __construct(
        Filesystem $filesystem,
        File $driver,
        TimezoneInterface $date,
        AssetInterfaceFactory $assetFactory,
        GetContentHash $getContentHash,
        ExtractMetadataInterface $extractMetadata,
        GetFileInfo $getFileInfo
    ) {
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->date = $date;
        $this->assetFactory = $assetFactory;
        $this->getContentHash = $getContentHash;
        $this->extractMetadata = $extractMetadata;
        $this->getFileInfo = $getFileInfo;
    }

    /**
     * Create and format media asset object
     *
     * @param string $path
     * @return AssetInterface
     * @throws FileSystemException
     */
    public function execute(string $path): AssetInterface
    {
        $absolutePath = $this->getMediaDirectory()->getAbsolutePath($path);
        $file = $this->getFileInfo->execute($absolutePath);
        [$width, $height] = getimagesize($absolutePath);

        $metadata = $this->extractMetadata->execute($absolutePath);

        return $this->assetFactory->create(
            [
                'id' => null,
                'path' => $path,
                'title' => $metadata->getTitle() ?: $file->getBasename(),
                'description' => $metadata->getDescription(),
                'createdAt' => $this->date->date($file->getCTime())->format(self::DATE_FORMAT),
                'updatedAt' => $this->date->date($file->getMTime())->format(self::DATE_FORMAT),
                'width' => $width,
                'height' => $height,
                'hash' => $this->getHash($path),
                'size' => $file->getSize(),
                'contentType' => 'image/' . $file->getExtension(),
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
     * Retrieve media directory instance with read access
     *
     * @return ReadInterface
     */
    private function getMediaDirectory(): ReadInterface
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }
}
