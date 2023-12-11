<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model;

use Generator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\Glob;
use Magento\RemoteStorage\Driver\DriverPool as RemoteDriverPool;
use Magento\RemoteStorage\Filesystem;

/**
 * Synchronize files from local filesystem.
 */
class Synchronizer
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * File upload.
     *
     * @return Generator
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function execute(): Generator
    {
        foreach ($this->filesystem->getDirectoryCodes() as $directoryCode) {
            if ($this->isSynchronizationAllowed($directoryCode)) {
                $directory = $this->filesystem->getDirectoryWrite($directoryCode, DriverPool::FILE);
                $remoteDirectory = $this->filesystem->getDirectoryWrite($directoryCode, RemoteDriverPool::REMOTE);

                yield from $this->copyRecursive($directory, $remoteDirectory, $directory->getAbsolutePath());
            }
        }
    }

    /**
     * Recursive file upload.
     *
     * @param WriteInterface $directory
     * @param WriteInterface $remoteDirectory
     * @param string $path
     * @param string $pattern
     * @param int $flags
     * @return Generator
     * @throws FileSystemException
     */
    private function copyRecursive(
        WriteInterface $directory,
        WriteInterface $remoteDirectory,
        string $path,
        string $pattern = '*.*',
        int $flags = Glob::GLOB_NOSORT
    ): Generator {
        $path = rtrim($path, '/');
        $localDriver = $directory->getDriver();
        $remoteDriver = $remoteDirectory->getDriver();

        foreach (Glob::glob($path . '/' . $pattern, $flags) as $file) {
            /**
             * Extracting relative path in local system to apply it for remote system.
             */
            $relativeFile = $directory->getRelativePath($file);
            $destination = $remoteDirectory->getAbsolutePath($relativeFile);

            if (!$remoteDirectory->isExist($destination)) {
                $localDriver->copy($file, $destination, $remoteDriver);

                yield $relativeFile;
            }
        }

        foreach (Glob::glob($path . '/{,.}[!.,!..]*',
            $flags | Glob::GLOB_ONLYDIR | Glob::GLOB_BRACE) as $childDirectory) {
            $relativeDirectory = $directory->getRelativePath($childDirectory);
            $destinationDirectory = $remoteDirectory->getAbsolutePath($relativeDirectory);

            if (!$remoteDirectory->isDirectory($destinationDirectory)) {
                $remoteDriver->createDirectory($destinationDirectory);

                yield $relativeDirectory;
            }

            yield from $this->copyRecursive($directory, $remoteDirectory, $childDirectory, $pattern, $flags);
        }
    }

    /**
     * Check if synchronization is allowed.
     *
     * @param string $directoryCode
     * @return bool
     * @deprecated This method should be removed when MC-39280 is fixed
     * and import export functionality is allocated inside var/import_export directory
     */
    private function isSynchronizationAllowed(string $directoryCode): bool
    {
        // skip synchronization for import export
        return $directoryCode !== DirectoryList::VAR_IMPORT_EXPORT;
    }
}
