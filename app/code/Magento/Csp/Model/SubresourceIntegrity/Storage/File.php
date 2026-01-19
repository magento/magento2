<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\Storage;

use Magento\Framework\Filesystem\Directory\ReadInterface;
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
    private const MERGED_FILE_PATH = '_cache' . DIRECTORY_SEPARATOR . self::MERGED_FILENAME;

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
            $this->logger->critical($exception);

            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function save(string $data, ?string $context): bool
    {
        try {
            $staticDir = $this->filesystem->getDirectoryWrite(
                DirectoryList::STATIC_VIEW
            );

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

            if (!empty($mergedFiles)) {
                $this->saveHashesToFile(
                    $staticDir,
                    $mergedFiles,
                    self::MERGED_FILE_PATH
                );
            }

            if (!empty($individualFiles)) {
                $this->saveHashesToFile(
                    $staticDir,
                    $individualFiles,
                    $this->resolveFilePath($context)
                );
            }

            return true;
        } catch (FileSystemException $exception) {
            $this->logger->critical($exception);

            return false;
        } catch (\InvalidArgumentException $exception) {
            $this->logger->critical($exception);

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
            $this->logger->critical($exception);

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
        return ($context ? $context . DIRECTORY_SEPARATOR : '') . self::FILENAME;
    }

    /**
     * Check if the path is a merged file path.
     *
     * @param string $path
     * @return bool
     */
    private function isMergedFilePath(string $path): bool
    {
        return str_contains($path, '_cache/merged/') || str_contains($path, '/merged/');
    }

    /**
     * Load and deserialize hashes from a file.
     *
     * @param string $filePath
     * @param ReadInterface $staticDir
     * @return array
     * @throws FileSystemException
     */
    private function loadHashesFromFile(string $filePath, $staticDir): array
    {
        if (!$staticDir->isFile($filePath)) {
            return [];
        }

        $content = $staticDir->readFile($filePath);
        if (!$content) {
            return [];
        }

        try {
            $decoded = $this->serializer->unserialize($content);
            return is_array($decoded) ? $decoded : [];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Invalid JSON in ' . $filePath . ': ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Save hashes to a file, merging with existing hashes.
     *
     * @param WriteInterface $staticDir
     * @param array $newHashes
     * @param string $filePath
     * @return void
     * @throws FileSystemException
     */
    private function saveHashesToFile(
        WriteInterface $staticDir,
        array $newHashes,
        string $filePath
    ): void {
        $existingHashes = $this->loadHashesFromFile($filePath, $staticDir);
        $allHashes = array_merge($existingHashes, $newHashes);

        if ($allHashes !== $existingHashes) {
            $staticDir->writeFile($filePath, $this->serializer->serialize($allHashes), 'w');
        }
    }
}
