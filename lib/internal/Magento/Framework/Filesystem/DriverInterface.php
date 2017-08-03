<?php
/**
 * Interface of Magento filesystem driver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem;

use Magento\Framework\Exception\FileSystemException;

/**
 * Class Driver
 *
 * @api
 * @since 2.0.0
 */
interface DriverInterface
{
    /**
     * Permissions to give read/write/execute access to owner and owning group, but not to all users
     *
     * @deprecated
     */
    const WRITEABLE_DIRECTORY_MODE = 0770;

    /**
     * Permissions to give read/write access to owner and owning group, but not to all users
     *
     * @deprecated
     */
    const WRITEABLE_FILE_MODE = 0660;

    /**
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function isExists($path);

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function stat($path);

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function isReadable($path);

    /**
     * Tells whether the filename is a regular file
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function isFile($path);

    /**
     * Tells whether the filename is a regular directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function isDirectory($path);

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileGetContents($path, $flag = null, $context = null);

    /**
     * Check if given path is writable
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function isWritable($path);

    /**
     * Returns parent directory's path
     *
     * @param string $path
     * @return string
     * @since 2.0.0
     */
    public function getParentDirectory($path);

    /**
     * Create directory
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function createDirectory($path, $permissions = 0777);

    /**
     * Read directory
     *
     * @param string $path
     * @return array
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function readDirectory($path);

    /**
     * Read directory recursively
     *
     * @param string|null $path
     * @return array
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function readDirectoryRecursively($path = null);

    /**
     * Search paths by given regex
     *
     * @param string $pattern
     * @param string $path
     * @return array
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function search($pattern, $path);

    /**
     * Renames a file or directory
     *
     * @param string $oldPath
     * @param string $newPath
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function rename($oldPath, $newPath, DriverInterface $targetDriver = null);

    /**
     * Copy source into destination
     *
     * @param string $source
     * @param string $destination
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function copy($source, $destination, DriverInterface $targetDriver = null);

    /**
     * Create symlink on source and place it into destination
     *
     * @param string $source
     * @param string $destination
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function symlink($source, $destination, DriverInterface $targetDriver = null);

    /**
     * Delete file
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function deleteFile($path);

    /**
     * Delete directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function deleteDirectory($path);

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function changePermissions($path, $permissions);

    /**
     * Recursively hange permissions of given path
     *
     * @param string $path
     * @param int $dirPermissions
     * @param int $filePermissions
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function changePermissionsRecursively($path, $dirPermissions, $filePermissions);

    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function touch($path, $modificationTime = null);

    /**
     * Put contents into given file
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @return int The number of bytes that were written.
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function filePutContents($path, $content, $mode = null);

    /**
     * Open file
     *
     * @param string $path
     * @param string $mode
     * @return resource
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileOpen($path, $mode);

    /**
     * Reads the line content from file pointer (with specified number of bytes from the current position).
     *
     * @param resource $resource
     * @param int $length
     * @param string $ending [optional]
     * @return string
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileReadLine($resource, $length, $ending = null);

    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param resource $resource
     * @param int $length
     * @return string
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileRead($resource, $length);

    /**
     * Reads one CSV row from the file
     *
     * @param resource $resource
     * @param int $length [optional]
     * @param string $delimiter [optional]
     * @param string $enclosure [optional]
     * @param string $escape [optional]
     * @return array|bool|null
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileGetCsv($resource, $length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\');

    /**
     * Returns position of read/write pointer
     *
     * @param resource $resource
     * @return int
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileTell($resource);

    /**
     * Seeks to the specified offset
     *
     * @param resource $resource
     * @param int $offset
     * @param int $whence
     * @return int
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileSeek($resource, $offset, $whence = SEEK_SET);

    /**
     * Returns true if pointer at the end of file or in case of exception
     *
     * @param resource $resource
     * @return boolean
     * @since 2.0.0
     */
    public function endOfFile($resource);

    /**
     * Close file
     *
     * @param resource $resource
     * @return boolean
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileClose($resource);

    /**
     * Writes data to file
     *
     * @param resource $resource
     * @param string $data
     * @return int
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileWrite($resource, $data);

    /**
     * Writes one CSV row to the file.
     *
     * @param resource $resource
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function filePutCsv($resource, array $data, $delimiter = ',', $enclosure = '"');

    /**
     * Flushes the output
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileFlush($resource);

    /**
     * Lock file in selected mode
     *
     * @param resource $resource
     * @param int $lockMode
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileLock($resource, $lockMode = LOCK_EX);

    /**
     * Unlock file
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileUnlock($resource);

    /**
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return mixed
     * @since 2.0.0
     */
    public function getAbsolutePath($basePath, $path, $scheme = null);

    /**
     * @param string $path
     * @return mixed
     * @since 2.0.0
     */
    public function getRealPath($path);

    /**
     * Return correct path for link
     *
     * @param string $path
     * @return mixed
     * @since 2.0.0
     */
    public function getRealPathSafety($path);

    /**
     * @param string $basePath
     * @param null $path
     * @return mixed
     * @since 2.0.0
     */
    public function getRelativePath($basePath, $path = null);
}
