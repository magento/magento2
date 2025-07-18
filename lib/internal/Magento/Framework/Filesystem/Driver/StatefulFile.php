<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Origin filesystem driver. Filesystem driver that uses the local filesystem.
 *
 * Assumed that stat cache is cleanup by data modification methods
 *
 * @deprecated moved most of the functionality back to File
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class StatefulFile implements DriverInterface
{
    /**
     * @var string
     */
    protected $scheme = '';

    /**
     * @var File
     */
    private $driverFile;

    /**
     * StatefulFile constructor.
     * @param File $driverFile
     */
    public function __construct(?File $driverFile = null)
    {
        $this->driverFile = $driverFile ?? ObjectManager::getInstance()->create(
            File::class,
            ['stateful' => true]
        );
    }
    /**
     * Returns last warning message string
     *
     * @return string
     */
    protected function getWarningMessage()
    {
        $warning = error_get_last();
        if ($warning && $warning['type'] == E_WARNING) {
            return 'Warning!' . $warning['message'];
        }
        return null;
    }

    /**
     * Is file or directory exist in file system
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function isExists($path)
    {
        return $this->driverFile->isExists($path);
    }

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws FileSystemException
     */
    public function stat($path)
    {
        return $this->driverFile->stat($path);
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function isReadable($path)
    {
        return $this->driverFile->isReadable($path);
    }

    /**
     * Tells whether the filename is a regular file
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function isFile($path)
    {
        return $this->driverFile->isFile($path);
    }

    /**
     * Tells whether the filename is a regular directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function isDirectory($path)
    {
        return $this->driverFile->isDirectory($path);
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     */
    public function fileGetContents($path, $flag = null, $context = null)
    {
        return $this->driverFile->fileGetContents($path, $flag, $context);
    }

    /**
     * Check if given path is writable
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function isWritable($path)
    {
        return $this->driverFile->isWritable($path);
    }

    /**
     * Returns parent directory's path
     *
     * @param string $path
     * @return string
     */
    public function getParentDirectory($path)
    {
        return $this->driverFile->getParentDirectory($path);
    }

    /**
     * Create directory
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     */
    public function createDirectory($path, $permissions = 0777)
    {
        return $this->driverFile->createDirectory($path, $permissions);
    }

    /**
     * Read directory
     *
     * @param string $path
     * @return string[]
     * @throws FileSystemException
     */
    public function readDirectory($path)
    {
        return $this->driverFile->readDirectory($path);
    }

    /**
     * Search paths by given regex
     *
     * @param string $pattern
     * @param string $path
     * @return string[]
     * @throws FileSystemException
     */
    public function search($pattern, $path)
    {
        return $this->driverFile->search($pattern, $path);
    }

    /**
     * Renames a file or directory
     *
     * @param string $oldPath
     * @param string $newPath
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     */
    public function rename($oldPath, $newPath, ?DriverInterface $targetDriver = null)
    {
        return $this->driverFile->rename($oldPath, $newPath, $targetDriver);
    }

    /**
     * Copy source into destination
     *
     * @param string $source
     * @param string $destination
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     */
    public function copy($source, $destination, ?DriverInterface $targetDriver = null)
    {
        return $this->driverFile->copy($source, $destination, $targetDriver);
    }

    /**
     * Create symlink on source and place it into destination
     *
     * @param string $source
     * @param string $destination
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     */
    public function symlink($source, $destination, ?DriverInterface $targetDriver = null)
    {
        return $this->driverFile->symlink($source, $destination, $targetDriver);
    }

    /**
     * Delete file
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function deleteFile($path)
    {
        return $this->driverFile->deleteFile($path);
    }

    /**
     * Recursive delete directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function deleteDirectory($path)
    {
        return $this->driverFile->deleteDirectory($path);
    }

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     */
    public function changePermissions($path, $permissions)
    {
        return $this->driverFile->changePermissions($path, $permissions);
    }

    /**
     * Recursively change permissions of given path
     *
     * @param string $path
     * @param int $dirPermissions
     * @param int $filePermissions
     * @return bool
     * @throws FileSystemException
     */
    public function changePermissionsRecursively($path, $dirPermissions, $filePermissions)
    {
        return $this->driverFile->changePermissionsRecursively($path, $dirPermissions, $filePermissions);
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FileSystemException
     */
    public function touch($path, $modificationTime = null)
    {
        return $this->driverFile->touch($path, $modificationTime);
    }

    /**
     * Write contents to file in given path
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @return int The number of bytes that were written.
     * @throws FileSystemException
     */
    public function filePutContents($path, $content, $mode = null)
    {
        return $this->driverFile->filePutContents($path, $content, $mode);
    }

    /**
     * Open file
     *
     * @param string $path
     * @param string $mode
     * @return resource file
     * @throws FileSystemException
     */
    public function fileOpen($path, $mode)
    {
        return $this->driverFile->fileOpen($path, $mode);
    }

    /**
     * Reads the line content from file pointer (with specified number of bytes from the current position).
     *
     * @param resource $resource
     * @param int $length
     * @param string $ending [optional]
     * @return string
     * @throws FileSystemException
     */
    public function fileReadLine($resource, $length, $ending = null)
    {
        return $this->driverFile->fileReadLine($resource, $length, $ending);
    }

    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param resource $resource
     * @param int $length
     * @return string
     * @throws FileSystemException
     */
    public function fileRead($resource, $length)
    {
        return $this->driverFile->fileRead($resource, $length);
    }

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
     */
    public function fileGetCsv($resource, $length = 0, $delimiter = ',', $enclosure = '"', $escape = "\0")
    {
        return $this->driverFile->fileGetCsv($resource, $length, $delimiter, $enclosure, $escape);
    }

    /**
     * Returns position of read/write pointer
     *
     * @param resource $resource
     * @return int
     * @throws FileSystemException
     */
    public function fileTell($resource)
    {
        return $this->driverFile->fileTell($resource);
    }

    /**
     * Seeks to the specified offset
     *
     * @param resource $resource
     * @param int $offset
     * @param int $whence
     * @return int
     * @throws FileSystemException
     */
    public function fileSeek($resource, $offset, $whence = SEEK_SET)
    {
        return $this->driverFile->fileSeek($resource, $offset, $whence);
    }

    /**
     * Returns true if pointer at the end of file or in case of exception
     *
     * @param resource $resource
     * @return bool
     */
    public function endOfFile($resource)
    {
        return $this->driverFile->endOfFile($resource);
    }

    /**
     * Close file
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     */
    public function fileClose($resource)
    {
        return $this->driverFile->fileClose($resource);
    }

    /**
     * Writes data to file
     *
     * @param resource $resource
     * @param string $data
     * @return int
     * @throws FileSystemException
     */
    public function fileWrite($resource, $data)
    {
        return $this->driverFile->fileWrite($resource, $data);
    }

    /**
     * Writes one CSV row to the file.
     *
     * @param resource $resource
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws FileSystemException
     */
    public function filePutCsv($resource, array $data, $delimiter = ',', $enclosure = '"')
    {
        return $this->driverFile->filePutCsv($resource, $data, $delimiter, $enclosure);
    }

    /**
     * Flushes the output
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     */
    public function fileFlush($resource)
    {
        return $this->driverFile->fileFlush($resource);
    }

    /**
     * Lock file in selected mode
     *
     * @param resource $resource
     * @param int $lockMode
     * @return bool
     * @throws FileSystemException
     */
    public function fileLock($resource, $lockMode = LOCK_EX)
    {
        return $this->driverFile->fileLock($resource, $lockMode);
    }

    /**
     * Unlock file
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     */
    public function fileUnlock($resource)
    {
        return $this->driverFile->fileUnlock($resource);
    }

    /**
     * Returns an absolute path for the given one.
     *
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return string
     */
    public function getAbsolutePath($basePath, $path, $scheme = null)
    {
        return $this->driverFile->getAbsolutePath($basePath, $path, $scheme);
    }

    /**
     * Retrieves relative path
     *
     * @param string $basePath
     * @param string $path
     * @return string
     */
    public function getRelativePath($basePath, $path = null)
    {
        return $this->driverFile->getRelativePath($basePath, $path);
    }

    /**
     * Fixes path separator.
     *
     * Utility method.
     *
     * @param string $path
     * @return string
     */
    protected function fixSeparator($path)
    {
        return $path !== null ? str_replace('\\', '/', $path) : '';
    }

    /**
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     */
    protected function getScheme($scheme = null)
    {
        return $scheme ? $scheme . '://' : '';
    }

    /**
     * Read directory recursively
     *
     * @param string $path
     * @return string[]
     * @throws FileSystemException
     */
    public function readDirectoryRecursively($path = null)
    {
        return $this->driverFile->readDirectoryRecursively($path);
    }

    /**
     * Get real path
     *
     * @param string $path
     *
     * @return string|bool
     */
    public function getRealPath($path)
    {
        return $this->driverFile->getRealPath($path);
    }

    /**
     * Return correct path for link
     *
     * @param string $path
     * @return mixed
     */
    public function getRealPathSafety($path)
    {
        return $this->driverFile->getRealPathSafety($path);
    }
}
