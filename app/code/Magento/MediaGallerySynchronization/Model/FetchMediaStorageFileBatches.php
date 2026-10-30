<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaGallery\Model\Directory\IsExcluded;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetch files from media storage in batches
 */
class FetchMediaStorageFileBatches
{
    private const IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * Existing folder configuration used to retain ancestors during pruning.
     *
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $coreConfig;

    /**
     * @var GetAssetsIterator
     */
    private $getAssetsIterator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var File
     */
    private $driver;

    /**
     * @var array
     */
    private $fileExtensions;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param LoggerInterface $log
     * @param IsPathExcludedInterface $isPathExcluded
     * @param Filesystem $filesystem
     * @param GetAssetsIterator $assetsIterator
     * @param File $driver
     * @param int $batchSize
     * @param array $fileExtensions
     * @param ScopeConfigInterface|null $coreConfig
     */
    public function __construct(
        LoggerInterface $log,
        IsPathExcludedInterface $isPathExcluded,
        Filesystem $filesystem,
        GetAssetsIterator $assetsIterator,
        File $driver,
        int $batchSize,
        array $fileExtensions,
        ?ScopeConfigInterface $coreConfig = null
    ) {
        $this->log = $log;
        $this->isPathExcluded = $isPathExcluded;
        $this->getAssetsIterator = $assetsIterator;
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->batchSize = $batchSize;
        $this->fileExtensions = $fileExtensions;
        $this->coreConfig = $coreConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Return files from files system by provided size of batch
     */
    public function execute(): \Traversable
    {
        $i = 0;
        $batch = [];
        /** @var ReadInterface $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $mediaDriver = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver();
        // Non-local directory probes can issue a metadata request for every listed entry.
        $files = get_class($mediaDriver) === File::class
            ? $this->readPaths($mediaDirectory, $mediaDirectory->getAbsolutePath(), '', $this->getAllowedAncestors())
            : $mediaDirectory->readRecursively($mediaDirectory->getAbsolutePath());

        foreach ($files as $file) {
            if (!$this->isApplicable($file)) {
                continue;
            }

            $batch[] = $file;

            if (++$i == $this->batchSize) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            yield $batch;
        }
    }

    /**
     * Expand directories at their sorted prefix position to preserve recursive scan ordering.
     *
     * Directories are addressed by absolute path: relative paths starting with a dash are rejected by path validation.
     *
     * @param ReadInterface $directory
     * @param string $root
     * @param string $path
     * @param array<string,bool>|null $allowedAncestors
     * @return \Traversable<int,string>
     */
    private function readPaths(
        ReadInterface $directory,
        string $root,
        string $path,
        ?array $allowedAncestors
    ): \Traversable {
        $paths = [];
        foreach ($directory->read($path === '' ? '' : $root . $path) as $entry) {
            $paths[] = $entry;
            if ($directory->isDirectory($root . $entry) && $this->canDescend($entry, $allowedAncestors)) {
                // Sort the expansion separately: a.png precedes a/image.png, but a itself precedes both.
                $paths[] = $entry . '/';
            }
        }
        sort($paths);

        foreach ($paths as $entry) {
            if (str_ends_with($entry, '/')) {
                yield from $this->readPaths($directory, $root, rtrim($entry, '/'), $allowedAncestors);
            } else {
                yield $entry;
            }
        }
    }

    /**
     * Retain configured folder ancestors only when descendant exclusion can be inferred safely.
     *
     * @return array<string,bool>|null
     */
    private function getAllowedAncestors(): ?array
    {
        // A custom exclusion service may exclude a directory while allowing its descendants.
        if (get_class($this->isPathExcluded) !== IsExcluded::class) {
            return null;
        }

        $folders = $this->coreConfig->getValue(self::IMAGE_FOLDERS_CONFIG_PATH, 'default');
        if (!is_iterable($folders)) {
            return null;
        }

        $ancestors = [];
        foreach ($folders as $folder) {
            // IsExcluded embeds folder values in a regex; only literal paths are safe to prune.
            if (!is_string($folder) || preg_match('#^[a-zA-Z0-9_/-]+$#D', $folder) !== 1) {
                return null;
            }
            $parts = preg_split('#/+#', trim($folder, '/')) ?: [];
            while ($parts) {
                $ancestors[implode('/', $parts)] = true;
                array_pop($parts);
            }
        }
        return $ancestors;
    }

    /**
     * Keep allowed ancestors and traverse conservatively if exclusion cannot be evaluated.
     *
     * @param string $path
     * @param array<string,bool>|null $allowedAncestors
     */
    private function canDescend(string $path, ?array $allowedAncestors): bool
    {
        if ($allowedAncestors === null) {
            return true;
        }

        try {
            return isset($allowedAncestors[$this->driver->getRealPathSafety($path)])
                || !$this->isPathExcluded->execute($path);
        } catch (\Exception $exception) {
            $this->log->critical($exception);
            return true;
        }
    }

    /**
     * Can synchronization be applied to asset with provided path
     *
     * @param string $path
     * @return bool
     */
    private function isApplicable(string $path): bool
    {
        try {
            return $path
                && !$this->isPathExcluded->execute($path)
                && preg_match('#\.(' . implode("|", $this->fileExtensions) . ')$# i', $path);
        } catch (\Exception $exception) {
            $this->log->critical($exception);
            return false;
        }
    }
}
