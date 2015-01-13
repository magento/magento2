<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\FilesystemException;

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
     */
    public function __construct(
        \Magento\Framework\Filesystem\File\WriteFactory $fileFactory,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        $path,
        $createPermissions = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->driver = $driver;
        $this->setPath($path);
        if (null !== $createPermissions) {
            $this->permissions = $createPermissions;
        }
    }

    /**
     * Check if directory is writable
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    protected function assertWritable($path)
    {
        if ($this->isWritable($path) === false) {
            $path = $this->getAbsolutePath($this->path, $path);
            throw new FilesystemException(sprintf('The path "%s" is not writable', $path));
        }
    }

    /**
     * Check if given path is exists and is file
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    protected function assertIsFile($path)
    {
        clearstatcache();
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        if (!$this->driver->isFile($absolutePath)) {
            throw new FilesystemException(sprintf('The "%s" file doesn\'t exist or not a file', $absolutePath));
        }
    }

    /**
     * Create directory if it does not exist
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function create($path = null)
    {
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
     * @throws FilesystemException
     */
    public function renameFile($path, $newPath, WriteInterface $targetDirectory = null)
    {
        $this->assertIsFile($path);
        $targetDirectory = $targetDirectory ?: $this;
        if (!$targetDirectory->isExist($this->driver->getParentDirectory($newPath))) {
            $targetDirectory->create($this->driver->getParentDirectory($newPath));
        }
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        $absoluteNewPath = $targetDirectory->driver->getAbsolutePath($this->path, $newPath);
        return $this->driver->rename($absolutePath, $absoluteNewPath, $targetDirectory->driver);
    }

    /**
     * Copy a file
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FilesystemException
     */
    public function copyFile($path, $destination, WriteInterface $targetDirectory = null)
    {
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
     * Delete given path
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function delete($path = null)
    {
        if (!$this->isExist($path)) {
            return true;
        }
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        if ($this->driver->isFile($absolutePath)) {
            $this->driver->deleteFile($absolutePath);
        } else {
            $this->driver->deleteDirectory($absolutePath);
        }
        return true;
    }

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FilesystemException
     */
    public function changePermissions($path, $permissions)
    {
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        return $this->driver->changePermissions($absolutePath, $permissions);
    }

    /**
     * Sets modification time of file, if file does not exist - creates file
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FilesystemException
     */
    public function touch($path, $modificationTime = null)
    {
        $folder = $this->driver->getParentDirectory($path);
        $this->create($folder);
        $this->assertWritable($folder);
        return $this->driver->touch($this->driver->getAbsolutePath($this->path, $path), $modificationTime);
    }

    /**
     * Check if given path is writable
     *
     * @param null $path
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function isWritable($path = null)
    {
        return $this->driver->isWritable($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @param string|null $protocol
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     */
    public function openFile($path, $mode = 'w', $protocol = null)
    {
        $folder = dirname($path);
        $this->create($folder);
        $this->assertWritable($folder);
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        return $this->fileFactory->create($absolutePath, $protocol, $this->driver, $mode);
    }

    /**
     * Write contents to file in given mode
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @param string|null $protocol
     * @return int The number of bytes that were written.
     * @throws FilesystemException
     */
    public function writeFile($path, $content, $mode = 'w+', $protocol = null)
    {
        return $this->openFile($path, $mode, $protocol)->write($content);
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
