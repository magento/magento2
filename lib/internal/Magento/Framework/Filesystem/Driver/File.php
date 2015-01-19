<?php
/**
 * Origin filesystem driver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\FilesystemException;

class File implements DriverInterface
{
    /**
     * @var string
     */
    protected $scheme = '';

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
     * @throws FilesystemException
     */
    public function isExists($path)
    {
        clearstatcache();
        $result = @file_exists($this->getScheme() . $path);
        if ($result === null) {
            throw new FilesystemException(sprintf('Error occurred during execution %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws FilesystemException
     */
    public function stat($path)
    {
        clearstatcache();
        $result = @stat($this->getScheme() . $path);
        if (!$result) {
            throw new FilesystemException(sprintf('Cannot gather stats! %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function isReadable($path)
    {
        clearstatcache();
        $result = @is_readable($this->getScheme() . $path);
        if ($result === null) {
            throw new FilesystemException(sprintf('Error occurred during execution %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Tells whether the filename is a regular file
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function isFile($path)
    {
        clearstatcache();
        $result = @is_file($this->getScheme() . $path);
        if ($result === null) {
            throw new FilesystemException(sprintf('Error occurred during execution %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Tells whether the filename is a regular directory
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function isDirectory($path)
    {
        clearstatcache();
        $result = @is_dir($this->getScheme() . $path);
        if ($result === null) {
            throw new FilesystemException(sprintf('Error occurred during execution %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FilesystemException
     */
    public function fileGetContents($path, $flag = null, $context = null)
    {
        clearstatcache();
        $result = @file_get_contents($this->getScheme() . $path, $flag, $context);
        if (false === $result) {
            throw new FilesystemException(
                sprintf('Cannot read contents from file "%s" %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Check if given path is writable
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function isWritable($path)
    {
        clearstatcache();
        $result = @is_writable($this->getScheme() . $path);
        if ($result === null) {
            throw new FilesystemException(sprintf('Error occurred during execution %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Returns parent directory's path
     *
     * @param string $path
     * @return string
     */
    public function getParentDirectory($path)
    {
        return dirname($this->getScheme() . $path);
    }

    /**
     * Create directory
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FilesystemException
     */
    public function createDirectory($path, $permissions)
    {
        $result = @mkdir($this->getScheme() . $path, $permissions, true);
        if (!$result) {
            throw new FilesystemException(
                sprintf('Directory "%s" cannot be created %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Read directory
     *
     * @param string $path
     * @return string[]
     * @throws FilesystemException
     */
    public function readDirectory($path)
    {
        try {
            $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
            $iterator = new \FilesystemIterator($path, $flags);
            $result = [];
            /** @var \FilesystemIterator $file */
            foreach ($iterator as $file) {
                $result[] = $file->getPathname();
            }
            sort($result);
            return $result;
        } catch (\Exception $e) {
            throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Search paths by given regex
     *
     * @param string $pattern
     * @param string $path
     * @return string[]
     * @throws FilesystemException
     */
    public function search($pattern, $path)
    {
        clearstatcache();
        $globPattern = rtrim($path, '/') . '/' . ltrim($pattern, '/');
        $result = @glob($globPattern, GLOB_BRACE);
        return is_array($result) ? $result : [];
    }

    /**
     * Renames a file or directory
     *
     * @param string $oldPath
     * @param string $newPath
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FilesystemException
     */
    public function rename($oldPath, $newPath, DriverInterface $targetDriver = null)
    {
        $result = false;
        $targetDriver = $targetDriver ?: $this;
        if (get_class($targetDriver) == get_class($this)) {
            $result = @rename($this->getScheme() . $oldPath, $newPath);
        } else {
            $content = $this->fileGetContents($oldPath);
            if (false !== $targetDriver->filePutContents($newPath, $content)) {
                $result = $this->deleteFile($newPath);
            }
        }
        if (!$result) {
            throw new FilesystemException(
                sprintf('The "%s" path cannot be renamed into "%s" %s', $oldPath, $newPath, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Copy source into destination
     *
     * @param string $source
     * @param string $destination
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FilesystemException
     */
    public function copy($source, $destination, DriverInterface $targetDriver = null)
    {
        $targetDriver = $targetDriver ?: $this;
        if (get_class($targetDriver) == get_class($this)) {
            $result = @copy($this->getScheme() . $source, $destination);
        } else {
            $content = $this->fileGetContents($source);
            $result = $targetDriver->filePutContents($destination, $content);
        }
        if (!$result) {
            throw new FilesystemException(
                sprintf(
                    'The file or directory "%s" cannot be copied to "%s" %s',
                    $source,
                    $destination,
                    $this->getWarningMessage()
                )
            );
        }
        return $result;
    }

    /**
     * Delete file
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function deleteFile($path)
    {
        $result = @unlink($this->getScheme() . $path);
        if (!$result) {
            throw new FilesystemException(
                sprintf('The file "%s" cannot be deleted %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Recursive delete directory
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function deleteDirectory($path)
    {
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($path, $flags);
        /** @var \FilesystemIterator $entity */
        foreach ($iterator as $entity) {
            if ($entity->isDir()) {
                $this->deleteDirectory($entity->getPathname());
            } else {
                $this->deleteFile($entity->getPathname());
            }
        }
        $result = @rmdir($this->getScheme() . $path);
        if (!$result) {
            throw new FilesystemException(
                sprintf('The directory "%s" cannot be deleted %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
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
        $result = @chmod($this->getScheme() . $path, $permissions);
        if (!$result) {
            throw new FilesystemException(
                sprintf('Cannot change permissions for path "%s" %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FilesystemException
     */
    public function touch($path, $modificationTime = null)
    {
        if (!$modificationTime) {
            $result = @touch($this->getScheme() . $path);
        } else {
            $result = @touch($this->getScheme() . $path, $modificationTime);
        }
        if (!$result) {
            throw new FilesystemException(
                sprintf('The file or directory "%s" cannot be touched %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Write contents to file in given path
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @return int The number of bytes that were written.
     * @throws FilesystemException
     */
    public function filePutContents($path, $content, $mode = null)
    {
        $result = @file_put_contents($this->getScheme() . $path, $content, $mode);
        if (!$result) {
            throw new FilesystemException(
                sprintf('The specified "%s" file could not be written %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Open file
     *
     * @param string $path
     * @param string $mode
     * @return resource file
     * @throws FilesystemException
     */
    public function fileOpen($path, $mode)
    {
        $result = @fopen($this->getScheme() . $path, $mode);
        if (!$result) {
            throw new FilesystemException(sprintf('File "%s" cannot be opened %s', $path, $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Reads the line content from file pointer (with specified number of bytes from the current position).
     *
     * @param resource $resource
     * @param int $length
     * @param string $ending [optional]
     * @return string
     * @throws FilesystemException
     */
    public function fileReadLine($resource, $length, $ending = null)
    {
        $result = @stream_get_line($resource, $length, $ending);
        if (false === $result) {
            throw new FilesystemException(sprintf('File cannot be read %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param resource $resource
     * @param int $length
     * @return string
     * @throws FilesystemException
     */
    public function fileRead($resource, $length)
    {
        $result = @fread($resource, $length);
        if ($result === false) {
            throw new FilesystemException(sprintf('File cannot be read %s', $this->getWarningMessage()));
        }
        return $result;
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
     * @throws FilesystemException
     */
    public function fileGetCsv($resource, $length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $result = @fgetcsv($resource, $length, $delimiter, $enclosure, $escape);
        if ($result === null) {
            throw new FilesystemException(sprintf('Wrong CSV handle %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Returns position of read/write pointer
     *
     * @param resource $resource
     * @return int
     * @throws FilesystemException
     */
    public function fileTell($resource)
    {
        $result = @ftell($resource);
        if ($result === null) {
            throw new FilesystemException(sprintf('Error occurred during execution %s', $this->getWarningMessage()));
        }
        return $result;
    }

    /**
     * Seeks to the specified offset
     *
     * @param resource $resource
     * @param int $offset
     * @param int $whence
     * @return int
     * @throws FilesystemException
     */
    public function fileSeek($resource, $offset, $whence = SEEK_SET)
    {
        $result = @fseek($resource, $offset, $whence);
        if ($result === -1) {
            throw new FilesystemException(
                sprintf('Error occurred during execution of fileSeek %s', $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Returns true if pointer at the end of file or in case of exception
     *
     * @param resource $resource
     * @return boolean
     */
    public function endOfFile($resource)
    {
        return feof($resource);
    }

    /**
     * Close file
     *
     * @param resource $resource
     * @return boolean
     * @throws FilesystemException
     */
    public function fileClose($resource)
    {
        $result = @fclose($resource);
        if (!$result) {
            throw new FilesystemException(
                sprintf('Error occurred during execution of fileClose %s', $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Writes data to file
     *
     * @param resource $resource
     * @param string $data
     * @return int
     * @throws FilesystemException
     */
    public function fileWrite($resource, $data)
    {
        $result = @fwrite($resource, $data);
        if (false === $result) {
            throw new FilesystemException(
                sprintf('Error occurred during execution of fileWrite %s', $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Writes one CSV row to the file.
     *
     * @param resource $resource
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws FilesystemException
     */
    public function filePutCsv($resource, array $data, $delimiter = ',', $enclosure = '"')
    {
        $result = @fputcsv($resource, $data, $delimiter, $enclosure);
        if (!$result) {
            throw new FilesystemException(
                sprintf('Error occurred during execution of filePutCsv %s', $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Flushes the output
     *
     * @param resource $resource
     * @return bool
     * @throws FilesystemException
     */
    public function fileFlush($resource)
    {
        $result = @fflush($resource);
        if (!$result) {
            throw new FilesystemException(
                sprintf('Error occurred during execution of fileFlush %s', $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Lock file in selected mode
     *
     * @param resource $resource
     * @param int $lockMode
     * @return bool
     * @throws FilesystemException
     */
    public function fileLock($resource, $lockMode = LOCK_EX)
    {
        $result = @flock($resource, $lockMode);
        if (!$result) {
            throw new FilesystemException(
                sprintf('Error occurred during execution of fileLock %s', $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Unlock file
     *
     * @param resource $resource
     * @return bool
     * @throws FilesystemException
     */
    public function fileUnlock($resource)
    {
        $result = @flock($resource, LOCK_UN);
        if (!$result) {
            throw new FilesystemException(
                sprintf('Error occurred during execution of fileUnlock %s', $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return string
     */
    public function getAbsolutePath($basePath, $path, $scheme = null)
    {
        return $this->getScheme($scheme) . $basePath . ltrim($this->fixSeparator($path), '/');
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
        $path = $this->fixSeparator($path);
        if (strpos($path, $basePath) === 0 || $basePath == $path . '/') {
            $result = substr($path, strlen($basePath));
        } else {
            $result = $path;
        }
        return $result;
    }

    /**
     * Fixes path separator
     * Utility method.
     *
     * @param string $path
     * @return string
     */
    protected function fixSeparator($path)
    {
        return str_replace('\\', '/', $path);
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
     * @throws FilesystemException
     */
    public function readDirectoryRecursively($path = null)
    {
        $result = [];
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, $flags),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \FilesystemIterator $file */
            foreach ($iterator as $file) {
                $result[] = $file->getPathname();
            }
        } catch (\Exception $e) {
            throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
        }
        return $result;
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
        return realpath($path);
    }
}
