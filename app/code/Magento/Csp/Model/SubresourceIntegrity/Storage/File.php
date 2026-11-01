<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\Storage;

use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Csp\Model\SubresourceIntegrity\StorageInterface;

/**
 * Filesystem based SRI hashed storage.
 */
class File implements StorageInterface
{
    /**
     * Name of a storage file.
     *
     * @var string
     */
    private const FILENAME = 'sri-hashes.json';

    /**
     * Name of a storage file for runtime-generated merged files.
     *
     * @var string
     */
    private const MERGED_FILENAME = 'merged/sri-hashes.json';

    /**
     * Full path to merged file hashes storage.
     *
     * @var string
     */
    private const MERGED_FILE_PATH = '_cache' . '/' . self::MERGED_FILENAME;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function load(?string $context): ?string
    {
        try {
            $staticDir = $this->filesystem->getDirectoryRead(
                DirectoryList::STATIC_VIEW
            );

            $individualData = $this->loadHashesFromFile(
                $this->resolveFilePath($context),
                $staticDir
            );

            $mergedData = $this->loadHashesFromFile(
                self::MERGED_FILE_PATH,
                $staticDir
            );

            $combinedData = array_merge($individualData, $mergedData);

            return empty($combinedData) ? null : $this->serializer->serialize($combinedData);
        } catch (FileSystemException $exception) {
            $this->logger->warning('SRI: Could not load hashes: ' . $exception->getMessage());

            return null;
        }
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save(string $data, ?string $context): bool
    {
        try {
            $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

            $dataArray = $this->serializer->unserialize($data);
            if (!is_array($dataArray)) {
                return false;
            }

            $mergedFiles = [];
            $individualFiles = [];

            foreach ($dataArray as $path => $hash) {
                if ($this->isMergedFilePath($path)) {
                    $mergedFiles[$path] = $hash;
                } else {
                    $individualFiles[$path] = $hash;
                }
            }

            $mergedSuccess = true;
            $individualSuccess = true;

            if (!empty($mergedFiles)) {
                $mergedSuccess = $this->saveHashesToFile($staticDir, $mergedFiles, self::MERGED_FILE_PATH);
            }

            if (!empty($individualFiles)) {
                $individualSuccess = $this->saveHashesToFile(
                    $staticDir,
                    $individualFiles,
                    $this->resolveFilePath($context)
                );
            }

            return $mergedSuccess && $individualSuccess;
        } catch (\InvalidArgumentException $exception) {
            $this->logger->warning('SRI: Invalid data passed to save: ' . $exception->getMessage());

            return false;
        } catch (\Exception $exception) {
            $this->logger->warning('SRI: Could not save hashes: ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function remove(?string $context): bool
    {
        try {
            $staticDir = $this->filesystem->getDirectoryWrite(
                DirectoryList::STATIC_VIEW
            );

            return $staticDir->delete($this->resolveFilePath($context));
        } catch (FileSystemException $exception) {
            $this->logger->warning('SRI: Could not remove hashes: ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * Resolves a storage file path for individual file hashes.
     *
     * @param string|null $context
     *
     * @return string
     */
    private function resolveFilePath(?string $context): string
    {
        return ($context ? $context . '/' : '') . self::FILENAME;
    }

    /**
     * Check if the path is a merged file path.
     *
     * @param string $path
     * @return bool
     */
    private function isMergedFilePath(string $path): bool
    {
        return str_contains($path, '_cache/merged/');
    }

    /**
     * Load and deserialize hashes from a file.
     *
     * @param string $filePath
     * @param ReadInterface $staticDir
     * @return array
     */
    private function loadHashesFromFile(string $filePath, ReadInterface $staticDir): array
    {
        try {
            if (!$staticDir->isFile($filePath)) {
                return [];
            }

            return $this->deserializeHashes($staticDir->readFile($filePath), $filePath);
        } catch (FileSystemException $e) {
            $this->logger->warning('SRI: Could not read file ' . $filePath . ': ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Read and deserialize hashes from an open file handle.
     *
     * @param resource $resource
     * @param DriverInterface $driver
     * @param string $absolutePath
     * @param string $filePath
     * @return array
     */
    private function readHashesFromHandle(
        $resource,
        DriverInterface $driver,
        string $absolutePath,
        string $filePath
    ): array {
        try {
            $stat = $driver->stat($absolutePath);
            $content = ($stat && $stat['size'] > 0) ? $driver->fileRead($resource, $stat['size']) : '';
            return $this->deserializeHashes($content, $filePath);
        } catch (FileSystemException $e) {
            $this->logger->warning('SRI: Could not read hashes file ' . $filePath . ': ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Deserialize a JSON hashes string into an array.
     *
     * @param string $content
     * @param string $filePath
     * @return array
     */
    private function deserializeHashes(string $content, string $filePath): array
    {
        if (!$content) {
            return [];
        }

        try {
            $decoded = $this->serializer->unserialize($content);
            return is_array($decoded) ? $decoded : [];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('SRI: Invalid JSON in hashes file ' . $filePath . ': ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Save hashes to a file, merging with existing hashes.
     *
     * Uses an exclusive lock held across the entire read-modify-write sequence
     * to prevent TOCTOU data loss under parallel SCD deployments (--jobs > 1).
     *
     * @param WriteInterface $staticDir
     * @param array $newHashes
     * @param string $filePath
     * @return bool
     */
    private function saveHashesToFile(
        WriteInterface $staticDir,
        array $newHashes,
        string $filePath
    ): bool {
        $resource = null;
        $driver = $staticDir->getDriver();

        try {
            $absolutePath = $staticDir->getAbsolutePath($filePath);
            $staticDir->create($driver->getParentDirectory($filePath));

            $resource = $driver->fileOpen($absolutePath, 'c+');
            $driver->fileLock($resource, LOCK_EX);
            $driver->fileSeek($resource, 0);

            $existingHashes = $this->readHashesFromHandle($resource, $driver, $absolutePath, $filePath);
            $allHashes = array_merge($existingHashes, $newHashes);

            if ($allHashes !== $existingHashes) {
                if (!ftruncate($resource, 0)) {
                    $this->logger->warning('SRI: Failed to truncate hashes file: ' . $filePath);
                    return false;
                }
                $driver->fileSeek($resource, 0);
                $driver->fileWrite($resource, $this->serializer->serialize($allHashes));
            }

            return true;
        } catch (FileSystemException $e) {
            $this->logger->warning('SRI: Could not write hashes file ' . $filePath . ': ' . $e->getMessage());
            return false;
        } finally {
            if ($resource) {
                try {
                    $driver->fileUnlock($resource);
                    $driver->fileClose($resource);
                } catch (\Exception $e) {
                    $this->logger->warning('SRI: Could not close hashes file ' . $filePath . ': ' . $e->getMessage());
                }
            }
        }
    }
}
