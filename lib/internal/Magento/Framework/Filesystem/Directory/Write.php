<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\File\WriteFactoryInterface;

/**
 * Write Interface implementation
 */
class Write extends Read implements WriteInterface
{
    /**
     * Permissions for new sub-directories
     *
     * @var int
     */
    protected $permissions = 0777;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\File\WriteFactory $fileFactory
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     * @param string $path
     * @param int $createPermissions
     * @param PathValidatorInterface|null $pathValidator
     */
    public function __construct(
        \Magento\Framework\Filesystem\File\WriteFactory $fileFactory,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        $path,
        $createPermissions = null,
        PathValidatorInterface $pathValidator = null
    ) {
        parent::__construct($fileFactory, $driver, $path, $pathValidator);
        if (null !== $createPermissions) {
            $this->permissions = $createPermissions;
        }
    }

    /**
     * Check if directory or file is writable
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function assertWritable($path)
    {
        if ($this->isWritable($path) === false) {
            $path = (!$this->driver->isFile($path))
                ? $this->getAbsolutePath($this->path, $path)
                : $this->getAbsolutePath($path);
            throw new FileSystemException(new \Magento\Framework\Phrase('The path "%1" is not writable', [$path]));
        }
    }

    /**
     * Check if given path is exists and is file
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function assertIsFile($path)
    {
        clearstatcache();
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        if (!$this->driver->isFile($absolutePath)) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase('The file "%1" doesn\'t exist or not a file', [$absolutePath])
            );
        }
    }

    /**
     * Create directory if it does not exist
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function create($path = null)
    {
        $this->validatePath($path);
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        if ($this->driver->isDirectory($absolutePath)) {
            return true;
        }

        return $this->driver->createDirectory($absolutePath, $this->permissions);
    }

    /**
     * Rename a file
     *
     * @param string $path
     * @param string $newPath
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function renameFile($path, $newPath, WriteInterface $targetDirectory = null)
    {
        $this->validatePath($path);
        $this->assertIsFile($path);
        $targetDirectory = $targetDirectory ?: $this;
        if (!$targetDirectory->isExist($this->driver->getParentDirectory($newPath))) {
            $targetDirectory->create($this->driver->getParentDirectory($newPath));
        }
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        $absoluteNewPath = $targetDirectory->getAbsolutePath($newPath);

        return $this->driver->rename($absolutePath, $absoluteNewPath, $targetDirectory->driver);
    }

    /**
     * Copy a file
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function copyFile($path, $destination, WriteInterface $targetDirectory = null)
    {
        $this->validatePath($path);
        $this->assertIsFile($path);

        $targetDirectory = $targetDirectory ?: $this;
        if (!$targetDirectory->isExist($this->driver->getParentDirectory($destination))) {
            $targetDirectory->create($this->driver->getParentDirectory($destination));
        }
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        $absoluteDestination = $targetDirectory->getAbsolutePath($destination);

        return $this->driver->copy($absolutePath, $absoluteDestination, $targetDirectory->driver);
    }

    /**
     * Creates symlink on a file and places it to destination
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function createSymlink($path, $destination, WriteInterface $targetDirectory = null)
    {
        $this->validatePath($path);
        $targetDirectory = $targetDirectory ?: $this;
        $parentDirectory = $this->driver->getParentDirectory($destination);
        if (!$targetDirectory->isExist($parentDirectory)) {
            $targetDirectory->create($parentDirectory);
        }
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        $absoluteDestination = $targetDirectory->getAbsolutePath($destination);

        return $this->driver->symlink($absolutePath, $absoluteDestination, $targetDirectory->driver);
    }

    /**
     * Delete given path
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function delete($path = null)
    {
        $exceptionMessages = [];
        $this->validatePath($path);
        if (!$this->isExist($path)) {
            return true;
        }
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        if ($this->driver->isFile($absolutePath)) {
            $this->driver->deleteFile($absolutePath);
        } else {
            try {
                $this->deleteFilesRecursively($absolutePath);
            } catch (FileSystemException $e) {
                $exceptionMessages[] = $e->getMessage();
            }
            try {
                $this->driver->deleteDirectory($absolutePath);
            } catch (FileSystemException $e) {
                $exceptionMessages[] = $e->getMessage();
            }

            if (!empty($exceptionMessages)) {
                throw new FileSystemException(
                    new \Magento\Framework\Phrase(
                        \implode(' ', $exceptionMessages)
                    )
                );
            }
        }

        return true;
    }

    /**
     * Delete files recursively
     *
     * Implemented in order to delete as much files as possible and collect all exceptions
     *
     * @param string $path
     * @return void
     * @throws FileSystemException
     */
    private function deleteFilesRecursively(string $path)
    {
        $exceptionMessages = [];
        $entitiesList = $this->driver->readDirectoryRecursively($path);
        foreach ($entitiesList as $entityPath) {
            if ($this->driver->isFile($entityPath)) {
                try {
                    $this->driver->deleteFile($entityPath);
                } catch (FileSystemException $e) {
                    $exceptionMessages[] = $e->getMessage();
                }
            }
        }
        if (!empty($exceptionMessages)) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase(
                    \implode(' ', $exceptionMessages)
                )
            );
        }
    }

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function changePermissions($path, $permissions)
    {
        $this->validatePath($path);
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);

        return $this->driver->changePermissions($absolutePath, $permissions);
    }

    /**
     * Recursively change permissions of given path
     *
     * @param string $path
     * @param int $dirPermissions
     * @param int $filePermissions
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function changePermissionsRecursively($path, $dirPermissions, $filePermissions)
    {
        $this->validatePath($path);
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);

        return $this->driver->changePermissionsRecursively($absolutePath, $dirPermissions, $filePermissions);
    }

    /**
     * Sets modification time of file, if file does not exist - creates file
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function touch($path, $modificationTime = null)
    {
        $this->validatePath($path);

        $folder = $this->driver->getParentDirectory($path);
        $this->create($folder);
        $this->assertWritable($folder);
        return $this->driver->touch($this->driver->getAbsolutePath($this->path, $path), $modificationTime);
    }

    /**
     * Check if given path is writable
     *
     * @param string|null $path
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function isWritable($path = null)
    {
        $this->validatePath($path);

        return $this->driver->isWritable($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function openFile($path, $mode = 'w')
    {
        $this->validatePath($path);
        $folder = dirname($path);
        $this->create($folder);
        $this->assertWritable($this->isExist($path) ? $path : $folder);
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);

        return $this->fileFactory->create($absolutePath, $this->driver, $mode);
    }

    /**
     * Write contents to file in given mode
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @return int The number of bytes that were written.
     * @throws FileSystemException
     */
    public function writeFile($path, $content, $mode = 'w+')
    {
        return $this->openFile($path, $mode)->write($content);
    }

    /**
     * Get driver
     *
     * @return \Magento\Framework\Filesystem\DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
