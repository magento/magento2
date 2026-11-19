<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Resolve content hashes of stored product media gallery images.
 *
 * Encapsulates filesystem access so the media gallery processor can detect duplicate
 * uploads by comparing image content, keeping its own object coupling low.
 */
class ImageContentHashProvider
{
    /**
     * @var Config
     */
    private $mediaConfig;

    /**
     * @var ReadInterface|null
     */
    private $mediaDirectory;

    /**
     * @param Config $mediaConfig
     * @param Filesystem $filesystem
     */
    public function __construct(Config $mediaConfig, Filesystem $filesystem)
    {
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * Build a map of existing image content hashes to their value_ids.
     *
     * The stored file name cannot be used for matching because Magento disambiguates
     * colliding names (image.png, image_1.png, ...), so the content hash is used instead.
     *
     * Each hash maps to a queue of value_ids so that several existing images sharing
     * identical content (e.g. the same picture reused for different roles) are matched
     * one-to-one and never collapsed onto a single entry.
     *
     * @param array $existingMediaGallery
     * @return array
     */
    public function getExistingValueIdsByContentHash(array $existingMediaGallery): array
    {
        $map = [];
        foreach ($existingMediaGallery as $existingEntry) {
            if (!isset($existingEntry['file'], $existingEntry['value_id'])) {
                continue;
            }
            $hash = $this->getStoredImageHash((string)$existingEntry['file']);
            if ($hash !== null) {
                $map[$hash][] = (int)$existingEntry['value_id'];
            }
        }
        return $map;
    }

    /**
     * Calculate the content hash of an existing media gallery file.
     *
     * @param string $file
     * @return string|null
     */
    private function getStoredImageHash(string $file): ?string
    {
        if ($this->mediaDirectory === null) {
            return null;
        }
        try {
            $path = $this->mediaConfig->getMediaPath($file);
            if (!$this->mediaDirectory->isExist($path)) {
                return null;
            }

            return sha1((string)$this->mediaDirectory->readFile($path));
        } catch (\Exception) {
            return null;
        }
    }
}
