<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

interface WriteInterface extends ReadInterface
{
    /**
     * Create directory if it does not exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function create($path = null);

    /**
     * Delete given path
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function delete($path = null);

    /**
     * Rename a file
     *
     * @param string $path
     * @param string $newPath
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function renameFile($path, $newPath, WriteInterface $targetDirectory = null);

    /**
     * Copy a file
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function copyFile($path, $destination, WriteInterface $targetDirectory = null);

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function changePermissions($path, $permissions);

    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int $modificationTime [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function touch($path, $modificationTime = null);

    /**
     * Check if given path is writable
     *
     * @param string $path [optional]
     * @return bool
     */
    public function isWritable($path = null);

    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @param string|null $protocol
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     */
    public function openFile($path, $mode = 'w', $protocol = null);

    /**
     * Open file in given path
     *
     * @param string $path
     * @param string $content
     * @param string $mode [optional]
     * @return int The number of bytes that were written.
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function writeFile($path, $content, $mode = null);

    /**
     * Get driver
     *
     * @return \Magento\Framework\Filesystem\DriverInterface
     */
    public function getDriver();
}
