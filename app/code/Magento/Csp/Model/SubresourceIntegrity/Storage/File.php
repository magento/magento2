<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\Storage;

use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
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
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
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

            $path = $this->resolveFilePath($context);

            if (!$staticDir->isFile($path)) {
                return null;
            }

            return $staticDir->readFile($path);
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

            return (bool) $staticDir->writeFile(
                $this->resolveFilePath($context),
                $data,
                'w'
            );
        } catch (FileSystemException $exception) {
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
     * Resolves a storage file path for a given context.
     *
     * @param string|null $context
     *
     * @return string
     */
    private function resolveFilePath(?string $context): string
    {
        return ($context ? $context . DIRECTORY_SEPARATOR : '') . self::FILENAME;
    }
}
