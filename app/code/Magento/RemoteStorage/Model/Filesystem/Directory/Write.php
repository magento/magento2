<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Filesystem\Directory;

use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Remote storage write class
 */
class Write implements WriteInterface
{
    /**
     * @var WriteInterface
     */
    private $remoteDirectoryWrite;

    /**
     * @var WriteInterface
     */
    private $localDirectoryWrite;

    /**
     * Write constructor.
     *
     * @param WriteInterface $remoteDirectoryWrite
     * @param WriteInterface $localDirectoryWrite
     */
    public function __construct(WriteInterface $remoteDirectoryWrite, WriteInterface $localDirectoryWrite)
    {
        $this->remoteDirectoryWrite = $remoteDirectoryWrite;
        $this->localDirectoryWrite = $localDirectoryWrite;
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath($path = null)
    {
        return $this->remoteDirectoryWrite->getAbsolutePath($path);
    }

    /**
     * @inheritDoc
     */
    public function getRelativePath($path = null)
    {
        return $this->remoteDirectoryWrite->getRelativePath($path);
    }

    /**
     * @inheritDoc
     */
    public function read($path = null)
    {
        return $this->remoteDirectoryWrite->read($path);
    }

    /**
     * @inheritDoc
     */
    public function search($pattern, $path = null)
    {
        return $this->remoteDirectoryWrite->search($pattern, $path);
    }

    /**
     * @inheritDoc
     */
    public function isExist($path = null)
    {
        return $this->remoteDirectoryWrite->isExist($path);
    }

    /**
     * @inheritDoc
     */
    public function stat($path)
    {
        return $this->remoteDirectoryWrite->stat($path);
    }

    /**
     * @inheritDoc
     */
    public function isReadable($path = null)
    {
        return $this->remoteDirectoryWrite->isReadable($path);
    }

    /**
     * @inheritDoc
     */
    public function isFile($path)
    {
        return $this->remoteDirectoryWrite->isFile($path);
    }

    /**
     * @inheritDoc
     */
    public function isDirectory($path = null)
    {
        return $this->remoteDirectoryWrite->isDirectory($path);
    }

    /**
     * @inheritDoc
     */
    public function readFile($path, $flag = null, $context = null)
    {
        return $this->remoteDirectoryWrite->readFile($path, $flag, $context);
    }

    /**
     * @inheritDoc
     */
    public function create($path = null)
    {
        return $this->remoteDirectoryWrite->create($path);
    }

    /**
     * @inheritDoc
     */
    public function delete($path = null)
    {
        $deleted = $this->remoteDirectoryWrite->delete($path);
        if ($deleted) {
            $deleted = $this->localDirectoryWrite->delete($path);
        }
        return $deleted;
    }

    /**
     * @inheritDoc
     */
    public function renameFile($path, $newPath, WriteInterface $targetDirectory = null)
    {
        return $this->remoteDirectoryWrite->renameFile($path, $newPath, $targetDirectory);
    }

    /**
     * @inheritDoc
     */
    public function copyFile($path, $destination, WriteInterface $targetDirectory = null)
    {
        return $this->remoteDirectoryWrite->copyFile($path, $destination, $targetDirectory);
    }

    /**
     * @inheritDoc
     */
    public function createSymlink($path, $destination, WriteInterface $targetDirectory = null)
    {
        return $this->remoteDirectoryWrite->createSymlink($path, $destination, $targetDirectory);
    }

    /**
     * @inheritDoc
     */
    public function changePermissions($path, $permissions)
    {
        return $this->remoteDirectoryWrite->changePermissions($path, $permissions);
    }

    /**
     * @inheritDoc
     */
    public function changePermissionsRecursively($path, $dirPermissions, $filePermissions)
    {
        return $this->remoteDirectoryWrite->changePermissionsRecursively($path, $dirPermissions, $filePermissions);
    }

    /**
     * @inheritDoc
     */
    public function touch($path, $modificationTime = null)
    {
        return $this->remoteDirectoryWrite->touch($path, $modificationTime);
    }

    /**
     * @inheritDoc
     */
    public function isWritable($path = null)
    {
        return $this->remoteDirectoryWrite->isWritable($path);
    }

    /**
     * @inheritDoc
     */
    public function openFile($path, $mode = 'w')
    {
        return $this->remoteDirectoryWrite->openFile($path, $mode);
    }

    /**
     * @inheritDoc
     */
    public function writeFile($path, $content, $mode = null)
    {
        return $this->remoteDirectoryWrite->writeFile($path, $content, $mode);
    }

    /**
     * @inheritDoc
     */
    public function getDriver()
    {
        return $this->remoteDirectoryWrite->getDriver();
    }
}
