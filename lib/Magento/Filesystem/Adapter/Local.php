<?php
/**
 * Adapter for local filesystem
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Adapter;

class Local implements
    \Magento\Filesystem\AdapterInterface,
    \Magento\Filesystem\Stream\FactoryInterface
{
    /**
     * Checks the file existence.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return file_exists($key);
    }

    /**
     * Reads content of the file.
     *
     * @param string $key
     * @return string
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function read($key)
    {
        $result = @file_get_contents($key);
        if (false === $result) {
            throw new \Magento\Filesystem\FilesystemException("Failed to read contents of '{$key}'");
        }
        return $result;
    }

    /**
     * Writes content into the file.
     *
     * @param string $key
     * @param string $content
     * @return bool true if write was success
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function write($key, $content)
    {
        $result = @file_put_contents($key, $content);
        if (false === $result) {
            throw new \Magento\Filesystem\FilesystemException("Failed to write contents to '{$key}'");
        }
        return true;
    }

    /**
     * Renames the file.
     *
     * @param string $source
     * @param string $target
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function rename($source, $target)
    {
        $result = @rename($source, $target);
        if (!$result) {
            throw new \Magento\Filesystem\FilesystemException("Failed to rename '{$source}' to '{$target}'");
        }
        return true;
    }

    /**
     * Copy the file.
     *
     * @param string $source
     * @param string $target
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function copy($source, $target)
    {
        $result = @copy($source, $target);
        if (!$result) {
            throw new \Magento\Filesystem\FilesystemException("Failed to copy '{$source}' to '{$target}'");
        }
        return true;
    }

    /**
     * Calculates the MD5 hash of the file specified
     *
     * @param $key
     * @return string
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getFileMd5($key)
    {
        $hash = @md5_file($key);
        if (false === $hash) {
            throw new \Magento\Filesystem\FilesystemException("Failed to get hash of '{$key}'");
        }
        return $hash;
    }

    /**
     * Deletes the file or directory recursively.
     *
     * @param string $key
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function delete($key)
    {
        if (!file_exists($key) && !is_link($key)) {
            return;
        }

        if (is_file($key) || is_link($key)) {
            if (true !== @unlink($key)) {
                throw new \Magento\Filesystem\FilesystemException("Failed to remove file '{$key}'");
            }
            return;
        }

        $this->_deleteNestedKeys($key);

        if (true !== @rmdir($key)) {
            throw new \Magento\Filesystem\FilesystemException("Failed to remove directory '{$key}'");
        }
    }

    /**
     * Deletes all nested keys
     *
     * @param string $key
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function _deleteNestedKeys($key)
    {
        foreach ($this->getNestedKeys($key) as $nestedKey) {
            if (is_dir($nestedKey) && !is_link($nestedKey)) {
                if (true !== @rmdir($nestedKey)) {
                    throw new \Magento\Filesystem\FilesystemException("Failed to remove directory '{$nestedKey}'");
                }
            } else {
                // https://bugs.php.net/bug.php?id=52176
                if (defined('PHP_WINDOWS_VERSION_MAJOR') && is_dir($nestedKey)) {
                    if (true !== @rmdir($nestedKey)) {
                        throw new \Magento\Filesystem\FilesystemException("Failed to remove file '{$nestedKey}'");
                    }
                } else {
                    if (true !== @unlink($nestedKey)) {
                        throw new \Magento\Filesystem\FilesystemException("Failed to remove file '{$nestedKey}'");
                    }
                }
            }
        }
    }

    /**
     * Changes permissions of filesystem key
     *
     * @param string $key
     * @param int $permissions
     * @param bool $recursively
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function changePermissions($key, $permissions, $recursively)
    {
        if (!@chmod($key, $permissions)) {
            throw new \Magento\Filesystem\FilesystemException("Failed to change mode of '{$key}'");
        }

        if (is_dir($key) && $recursively) {
            foreach ($this->getNestedKeys($key) as $nestedKey) {
                if (!@chmod($nestedKey, $permissions)) {
                    throw new \Magento\Filesystem\FilesystemException("Failed to change mode of '{$nestedKey}'");
                }
            }
        }
    }

    /**
     * Gets list of all nested keys
     *
     * @param string $key
     * @return array
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getNestedKeys($key)
    {
        $result = array();

        if (!is_dir($key)) {
            throw new \Magento\Filesystem\FilesystemException("The directory '{$key}' does not exist.");
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($key, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
        } catch (\Exception $e) {
            $iterator = new \EmptyIterator;
        }


        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $result[] = $file->getPathname();
        }

        return $result;
    }

    /**
     * Gets list of all matched keys
     *
     * @param string $pattern
     * @return array
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function searchKeys($pattern)
    {
        $result = @glob($pattern);
        if (false === $result) {
            throw new \Magento\Filesystem\FilesystemException("Failed to resolve the file pattern '{$pattern}'");
        }
        return $result;
    }

    /**
     * Check if key is a directory.
     *
     * @param string $key
     * @return bool
     */
    public function isDirectory($key)
    {
        return is_dir($key);
    }

    /**
     * Check if key is a file.
     *
     * @param string $key
     * @return bool
     */
    public function isFile($key)
    {
        return is_file($key);
    }

    /**
     * Check if key exists and is writable
     *
     * @param string $key
     * @return bool
     */
    public function isWritable($key)
    {
        return is_writable($key);
    }

    /**
     * Check if key exists and is readable
     *
     * @param string $key
     * @return bool
     */
    public function isReadable($key)
    {
        return is_readable($key);
    }

    /**
     * Creates new directory
     *
     * @param string $key
     * @param int $mode
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function createDirectory($key, $mode)
    {
        if (!@mkdir($key, $mode, true)) {
            throw new \Magento\Filesystem\FilesystemException("Failed to create '{$key}'");
        }
    }

    /**
     * Touches a file
     *
     * @param string $key
     * @param int|null $fileModificationTime
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function touch($key, $fileModificationTime = null)
    {
        if ($fileModificationTime === null) {
            $success = @touch($key);
        } else {
            $success = @touch($key, $fileModificationTime);
        }
        if (!$success) {
            throw new \Magento\Filesystem\FilesystemException("Failed to touch '{$key}'");
        }
    }

    /**
     * Get file modification time.
     *
     * @param string $key
     * @return int
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getMTime($key)
    {
        $mtime = @filemtime($key);
        if (false === $mtime) {
            throw new \Magento\Filesystem\FilesystemException("Failed to get modification time of '{$key}'");
        }
        return $mtime;
    }

    /**
     * Get file size.
     *
     * @param string $key
     * @return int
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getFileSize($key)
    {
        $size = @filesize($key);
        if (false === $size) {
            throw new \Magento\Filesystem\FilesystemException("Failed to get file size of '{$key}'");
        }
        return $size;
    }

    /**
     * Create stream object
     *
     * @param string $path
     * @return \Magento\Filesystem\Stream\Local
     */
    public function createStream($path)
    {
        return new \Magento\Filesystem\Stream\Local($path);
    }
}
